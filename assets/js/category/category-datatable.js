/**
* Category DataTable Handler
*
* @package     WP_Equipment
* @subpackage  Assets/JS
* @version     1.1.0
* @author      arisciwek
*
* Path: /wp-equipment/assets/js/category-datatable.js
*/
(function($) {
    'use strict';

    const CategoryDataTable = {
        table: null,
        initialized: false,
        currentHighlight: null,

        init() {
            if (this.initialized) {
                return;
            }

            // Wait for dependencies
            if (!window.Category || !window.EquipmentToast) {
                setTimeout(() => this.init(), 100);
                return;
            }

            this.initialized = true;
            this.initDataTable();
            this.bindEvents();
            this.handleInitialHash();
        },

        initDataTable() {
            if ($.fn.DataTable.isDataTable('#categories-table')) {
                $('#categories-table').DataTable().destroy();
            }

            // Initialize clean table structure
            $('#categories-table').empty().html(`
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama Kategori</th>
                        <th>Level</th>
                        <th>Parent</th>
                        <th>Unit</th>
                        <th>PNBP</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            `);

            this.table = $('#categories-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: wpEquipmentData.ajaxUrl,
                    type: 'POST',
                    data: (d) => {
                        return {
                            ...d,
                            action: 'handle_category_datatable',
                            nonce: wpEquipmentData.nonce
                        };
                    },
                    error: (xhr, error, thrown) => {
                        console.error('DataTables Error:', error);
                        EquipmentToast.error('Gagal memuat data kategori');
                    }
                },
                columns: [
                    {
                        data: 'code',
                        title: 'Kode',
                        width: '100px'
                    },
                    {
                        data: 'name',
                        title: 'Nama Kategori'
                    },
                    {
                        data: 'level',
                        title: 'Level',
                        className: 'text-center',
                        width: '80px'
                    },
                    {
                        data: 'parent_name',
                        title: 'Parent',
                        defaultContent: '-'
                    },
                    {
                        data: 'unit',
                        title: 'Unit',
                        defaultContent: '-',
                        width: '80px'
                    },
                    {
                        data: 'pnbp',
                        title: 'PNBP',
                        defaultContent: '-',
                        className: 'text-right',
                        width: '120px'
                    },
                    {
                        data: 'actions',
                        title: 'Aksi',
                        orderable: false,
                        searchable: false,
                        className: 'text-center nowrap',
                        width: '100px'
                    }
                ],
                order: [[0, 'asc']], // Default sort by code
                pageLength: 25,
                language: {
                    "emptyTable": "Tidak ada data yang tersedia",
                    "info": "Menampilkan _START_ hingga _END_ dari _TOTAL_ entri",
                    "infoEmpty": "Menampilkan 0 hingga 0 dari 0 entri",
                    "infoFiltered": "(disaring dari _MAX_ total entri)",
                    "lengthMenu": "Tampilkan _MENU_ entri",
                    "loadingRecords": "Memuat...",
                    "processing": "Memproses...",
                    "search": "Cari:",
                    "zeroRecords": "Tidak ditemukan data yang sesuai",
                    "paginate": {
                        "first": "Pertama",
                        "last": "Terakhir",
                        "next": "Selanjutnya",
                        "previous": "Sebelumnya"
                    }
                },
                drawCallback: (settings) => {
                    this.bindActionButtons();

                    // Get current hash if any
                    const hash = window.location.hash;
                    if (hash && hash.startsWith('#')) {
                        const id = hash.substring(1);
                        if (id) {
                            this.highlightRow(id);
                        }
                    }
                },
                createdRow: (row, data) => {
                    $(row).attr('data-id', data.id);
                }
            });
        },

        bindEvents() {
            // Hash change event
            $(window).off('hashchange.categoryTable')
                    .on('hashchange.categoryTable', () => this.handleHashChange());

            // CRUD event listeners
            $(document).off('category:created.datatable category:updated.datatable category:deleted.datatable')
                      .on('category:created.datatable category:updated.datatable category:deleted.datatable',
                          () => this.refresh());
        },

        bindActionButtons() {
            const $table = $('#categories-table');
            $table.off('click', '.view-category, .edit-category, .delete-category');

            // View action
            $table.on('click', '.view-category', (e) => {
                e.preventDefault();
                const id = $(e.currentTarget).data('id');
                if (id) {
                    window.location.hash = id;

                    // Reset tab ke details
                    $('.tab-content').removeClass('active');
                    $('#category-details').addClass('active');
                    $('.nav-tab').removeClass('nav-tab-active');
                    $('.nav-tab[data-tab="category-details"]').addClass('nav-tab-active');
                }
            });

            // Edit action
            $table.on('click', '.edit-category', (e) => {
                e.preventDefault();
                const id = $(e.currentTarget).data('id');
                this.loadCategoryForEdit(id);
            });

            // Delete action
            $table.on('click', '.delete-category', (e) => {
                console.log('Delete button clicked');
                const id = $(e.currentTarget).data('id');
                console.log('Category ID:', id);
                this.handleDelete(id);
            });
        },

        async loadEditForm(id) {
            try {
                const response = await $.ajax({
                    url: wpEquipmentData.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'get_category',
                        id: id,
                        nonce: wpEquipmentData.nonce
                    }
                });
 
                if (response.success) {
                    // Gunakan CategoryForm yang baru untuk populate form
                    if (window.CategoryForm) {
                        window.CategoryForm.populateEditForm(response.data);
                    } else {
                        console.error('CategoryForm component not found');
                        EquipmentToast.error('Komponen form edit tidak tersedia');
                    }
                } else {
                    EquipmentToast.error(response.data?.message || 'Gagal memuat data kategori');
                }
            } catch (error) {
                console.error('Load category error:', error);
                EquipmentToast.error('Gagal menghubungi server');
            }
        },
 
        async loadCategoryForEdit(id) {
            if (!id) return;

            try {
                const response = await $.ajax({
                    url: wpEquipmentData.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'get_category',
                        id: id,
                        nonce: wpEquipmentData.nonce
                    }
                });

                if (response.success) {
                    if (window.EditCategoryForm) {
                        window.EditCategoryForm.showEditForm(response.data);
                    } else {
                        EquipmentToast.error('Komponen form edit tidak tersedia');
                    }
                } else {
                    EquipmentToast.error(response.data?.message || 'Gagal memuat data kategori');
                }
            } catch (error) {
                console.error('Load category error:', error);
                EquipmentToast.error('Gagal menghubungi server');
            }
        },

        handleDelete(id) {
            if (!id) return;

            console.log('handleDelete called with ID:', id);
            console.log('WIModal available:', typeof WIModal !== 'undefined');

            // Tampilkan modal konfirmasi dengan WIModal
            WIModal.show({
                title: 'Konfirmasi Hapus',
                message: 'Yakin ingin menghapus kategori ini? Aksi ini tidak dapat dibatalkan.',
                icon: 'trash',
                type: 'danger',
                confirmText: 'Hapus',
                confirmClass: 'button-danger',
                cancelText: 'Batal',
                onConfirm: async () => {
                    try {
                        const response = await $.ajax({
                            url: wpEquipmentData.ajaxUrl,
                            type: 'POST',
                            data: {
                                action: 'delete_category',
                                id: id,
                                nonce: wpEquipmentData.nonce
                            }
                        });

                        if (response.success) {
                            EquipmentToast.success(response.data.message);

                            // Clear hash if deleted category is currently viewed
                            if (window.location.hash === `#${id}`) {
                                window.location.hash = '';
                            }

                            this.refresh();
                            $(document).trigger('category:deleted');
                        } else {
                            EquipmentToast.error(response.data?.message || 'Gagal menghapus kategori');
                        }
                    } catch (error) {
                        console.error('Delete category error:', error);
                        EquipmentToast.error('Gagal menghubungi server');
                    }
                }
            });
        },

        handleHashChange() {
            const hash = window.location.hash;
            if (hash) {
                const id = hash.substring(1);
                if (id) {
                    this.highlightRow(id);
                }
            }
        },

        handleInitialHash() {
            const hash = window.location.hash;
            if (hash && hash.startsWith('#')) {
                this.handleHashChange();
            }
        },

        highlightRow(id) {
            if (this.currentHighlight) {
                $(`tr[data-id="${this.currentHighlight}"]`).removeClass('highlight');
            }

            const $row = $(`tr[data-id="${id}"]`);
            if ($row.length) {
                $row.addClass('highlight');
                this.currentHighlight = id;

                // Scroll into view if needed
                const container = this.table.table().container();
                const rowTop = $row.position().top;
                const containerHeight = $(container).height();
                const scrollTop = $(container).scrollTop();

                if (rowTop < scrollTop || rowTop > scrollTop + containerHeight) {
                    $row[0].scrollIntoView({behavior: 'smooth', block: 'center'});
                }
            }
        },

        refresh() {
            if (this.table) {
                this.table.ajax.reload(null, false);
            }
        }
    };

    // Initialize when document is ready
    $(document).ready(() => {
        window.CategoryDataTable = CategoryDataTable;
        CategoryDataTable.init();
    });

})(jQuery);
