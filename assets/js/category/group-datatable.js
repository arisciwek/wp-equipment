/**
 * Group DataTable Handler
 *
 * @package     WP_Equipment
 * @subpackage  Assets/JS/Category
 * @version     1.0.0
 * @author      arisciwek
 *
 * Path: /wp-equipment/assets/js/category/group-datatable.js
 *
 * Description: Komponen untuk mengelola DataTables grup.
 *              Includes state management, server-side processing,
 *              dan error handling.
 *              Terintegrasi dengan form handlers dan toast.
 *              Mendukung filter berdasarkan service.
 *
 * Dependencies:
 * - jQuery
 * - DataTables library
 * - EquipmentToast for notifications
 * - WIModal for confirmations
 *
 * Related Files:
 * - group-form.js: Handler untuk form operations
 * - GroupController.php: Backend handler
 * - _category_group_lists.php: Template for group lists
 *
 * Changelog:
 * 1.0.0 - 2024-02-24
 * - Initial implementation
 * - Added state management
 * - Added service filtering
 * - Added document handling
 * - Enhanced error handling
 */
(function($) {
    'use strict';

    const GroupDataTable = {
        table: null,
        initialized: false,
        currentServiceId: null,

        init() {
            // Debug: cek elemen table
            if ($('#groups-table').length === 0) {
                console.error('Table element tidak ditemukan');
                return;
            }
            
            // Debug: cek inisialisasi
            if ($('#groups-table').length && !this.initialized) {
                console.log('Inisialisasi GroupDataTable...');
                this.initializeDataTable();
                this.bindEvents();
                this.initialized = true;
            }
        },

        initializeDataTable() {
            // Debug: cek wpEquipmentData
            if (!wpEquipmentData || !wpEquipmentData.ajaxUrl || !wpEquipmentData.nonce) {
                console.error('wpEquipmentData tidak tersedia', {
                    exists: !!wpEquipmentData,
                    ajaxUrl: wpEquipmentData?.ajaxUrl,
                    nonce: wpEquipmentData?.nonce
                });
                return;
            }

            // Debug: destroy existing table jika ada
            if ($.fn.DataTable.isDataTable('#groups-table')) {
                $('#groups-table').DataTable().destroy();
            }

            const config = {
                processing: true,
                serverSide: true,
                ajax: {
                    url: wpEquipmentData.ajaxUrl,
                    type: 'POST',
                    data: (d) => {
                        const requestData = {
                            ...d,
                            action: 'handle_group_datatable',
                            nonce: wpEquipmentData.nonce,
                            draw: d.draw || 1,
                            service_id: this.currentServiceId
                        };
                        console.log('DataTable request data:', requestData);
                        return requestData;
                    },
                    error: (xhr, error, thrown) => {
                        console.error('DataTable request failed:', {
                            status: xhr.status,
                            statusText: xhr.statusText,
                            responseText: xhr.responseText,
                            error: error,
                            thrown: thrown
                        });
                        EquipmentToast.error('Gagal memuat data grup');
                    }
                },
                columns: [
                    { data: 'nama' },
                    { data: 'service_nama' },
                    { 
                        data: 'dokumen_type',
                        render: function(data, type, row) {
                            return data || '-';
                        }
                    },
                    { 
                        data: 'status',
                        render: function(data) {
                            return `<span class="status-badge ${data}">${data}</span>`;
                        }
                    },
                    { 
                        data: null,
                        render: function(data) {
                            let actions = '';
                            
                            // View button
                            actions += `<button type="button" class="button view-group" data-id="${data.id}" title="Lihat">
                                <i class="dashicons dashicons-visibility"></i>
                            </button> `;
                            
                            // Edit button
                            actions += `<button type="button" class="button edit-group" data-id="${data.id}" title="Edit">
                                <i class="dashicons dashicons-edit"></i>
                            </button> `;
                            
                            // Delete button
                            actions += `<button type="button" class="button delete-group" data-id="${data.id}" title="Hapus">
                                <i class="dashicons dashicons-trash"></i>
                            </button>`;
                            
                            return actions;
                        },
                        orderable: false,
                        searchable: false
                    }
                ],
                order: [[0, 'asc']],
                language: {
                    processing: 'Memproses...',
                    search: 'Pencarian:',
                    lengthMenu: 'Tampilkan _MENU_ data',
                    info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
                    infoEmpty: 'Tidak ada data yang ditampilkan',
                    infoFiltered: '(filter dari _MAX_ total data)',
                    loadingRecords: 'Mengambil data...',
                    zeroRecords: 'Tidak ditemukan data yang sesuai',
                    emptyTable: 'Tidak ada data',
                    paginate: {
                        first: 'Pertama',
                        previous: 'Sebelumnya',
                        next: 'Selanjutnya',
                        last: 'Terakhir'
                    }
                }
            };

            try {
                this.table = $('#groups-table').DataTable(config);
                console.log('DataTable initialized successfully');
            } catch (error) {
                console.error('Error initializing DataTable:', error);
            }
        },

        setServiceId(serviceId) {
            this.currentServiceId = serviceId;
            if (this.table) {
                this.table.ajax.reload();
            }
        },

        refresh() {
            console.log('Refreshing GroupDataTable...');
            if (this.table) {
                this.table.ajax.reload(null, false);
            } else {
                console.warn('Table not initialized, reinitializing...');
                this.init();
            }
        },

        bindEvents() {
            console.log('Binding GroupDataTable events...');
            $(document)
                .on('group:created group:updated group:deleted', () => this.refresh())
                .on('click', '.delete-group', (e) => {
                    e.preventDefault();
                    const id = $(e.currentTarget).data('id');
                    this.handleDelete(id);
                })
                .on('click', '.view-group', (e) => {
                    e.preventDefault();
                    const id = $(e.currentTarget).data('id');
                    if (id) {
                        window.location.hash = id;
                        
                        $('.nav-tab').removeClass('nav-tab-active');
                        $('.nav-tab[data-tab="group-details"]').addClass('nav-tab-active');
                        $('.tab-content').removeClass('active').hide();
                        $('#group-details').addClass('active').show();
                        
                        $('.wp-group-container').addClass('with-right-panel');
                        $('.wp-group-right-panel').addClass('visible');
                    }
                });
        },

        handleDelete(id) {
            if (!id) return;
        
            WIModal.show({
                title: 'Konfirmasi Hapus',
                message: 'Yakin ingin menghapus grup ini? Aksi ini tidak dapat dibatalkan.',
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
                                action: 'delete_group',
                                id: id,
                                nonce: wpEquipmentData.nonce
                            }
                        });
        
                        if (response.success) {
                            EquipmentToast.success('Grup berhasil dihapus');
                            this.refresh();
                            $(document).trigger('group:deleted');
                        } else {
                            EquipmentToast.error(response.data?.message || 'Gagal menghapus grup');
                        }
                    } catch (error) {
                        console.error('Delete group error:', error);
                        EquipmentToast.error('Gagal menghubungi server');
                    }
                }
            });
        },

        highlightRow(id) {
            if (!this.table) return;
            
            this.table.rows().nodes().each((row) => {
                $(row).removeClass('highlighted');
            });
            
            this.table.rows((idx, data) => data.id === id)
                .nodes()
                .each((row) => {
                    $(row).addClass('highlighted');
                });
        }
    };

    // Export ke window object
    window.GroupDataTable = GroupDataTable;

})(jQuery);
