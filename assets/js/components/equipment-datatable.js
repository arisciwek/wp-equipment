
/**
 * Equipment DataTable Handler
 *
 * @package     WP_Equipment
 * @subpackage  Assets/JS/Components
 * @version     1.0.2
 * @author      arisciwek
 *
 * Path: /wp-equipment/assets/js/components/equipment-datatable.js
 *
 * Description: Komponen untuk mengelola DataTables equipment.
 *              Menangani server-side processing, panel kanan,
 *              dan integrasi dengan komponen form terpisah.
 *
 * Form Integration:
 * - Create form handling sudah dipindahkan ke create-equipment-form.js
 * - Component ini hanya menyediakan method refresh() untuk update table
 * - Event 'equipment:created' digunakan sebagai trigger untuk refresh
 *
 * Dependencies:
 * - jQuery
 * - DataTables library
 * - EquipmentToast for notifications
 * - CreateEquipmentForm for handling create operations
 * - EditEquipmentForm for handling edit operations
 *
 * Related Files:
 * - create-equipment-form.js: Handles create form submission
 * - edit-equipment-form.js: Handles edit form submission
 */
 /**
  * Equipment DataTable Handler
  *
  * @package     WP_Equipment
  * @subpackage  Assets/JS/Components
  * @version     1.1.0
  * @author      arisciwek
  */
 (function($) {
     'use strict';

     const EquipmentDataTable = {
         table: null,
         initialized: false,
         currentHighlight: null,

         init() {
             if (this.initialized) {
                 return;
             }

             // Wait for dependencies
             if (!window.Equipment || !window.EquipmentToast) {
                 setTimeout(() => this.init(), 100);
                 return;
             }

             this.initialized = true;
             this.initDataTable();
             this.bindEvents();
             this.handleInitialHash();
         },

        initDataTable() {
            if ($.fn.DataTable.isDataTable('#equipments-table')) {
                $('#equipments-table').DataTable().destroy();
            }

            // Initialize clean table structure
            $('#equipments-table').empty().html(`
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama Equipment</th>
                        <th>Admin</th>
                        <th>Surat Keterangan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            `);

            this.table = $('#equipments-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: wpEquipmentData.ajaxUrl,
                    type: 'POST',
                    data: (d) => {
                        return {
                            ...d,
                            action: 'handle_equipment_datatable',
                            nonce: wpEquipmentData.nonce
                        };
                    },
                    error: (xhr, error, thrown) => {
                        console.error('DataTables Error:', error);
                        EquipmentToast.error('Gagal memuat data equipment');
                    }
                },
                // Di bagian columns, tambahkan setelah kolom code
                columns: [
                    {
                        data: 'code',
                        title: 'Kode',
                        width: '20px'
                    },
                    {
                        data: 'name',
                        title: 'Nama Equipment'
                    },
                    {
                        data: 'owner_name', // Kolom baru
                        title: 'Admin',
                        defaultContent: '-'
                    },
                    {
                        data: 'licence_count',
                        title: 'Surat Keterangan',
                        className: 'text-center',
                        searchable: false
                    },
                    {
                        data: 'actions',
                        title: 'Aksi',
                        orderable: false,
                        searchable: false,
                        className: 'text-center nowrap'
                    }
                ],
                order: [[0, 'asc']], // Default sort by code
                pageLength: wpEquipmentData.perPage || 10,
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
             $(window).off('hashchange.equipmentTable')
                     .on('hashchange.equipmentTable', () => this.handleHashChange());

             // CRUD event listeners
             $(document).off('equipment:created.datatable equipment:updated.datatable equipment:deleted.datatable')
                       .on('equipment:created.datatable equipment:updated.datatable equipment:deleted.datatable',
                           () => this.refresh());
         },

         bindActionButtons() {
             const $table = $('#equipments-table');
             $table.off('click', '.view-equipment, .edit-equipment, .delete-equipment');

             // View action
             $table.on('click', '.view-equipment', (e) => {
                 const id = $(e.currentTarget).data('id');
                 if (id) window.location.hash = id;

                 // Reset tab ke details
                 $('.tab-content').removeClass('active');
                 $('#equipment-details').addClass('active');
                 $('.nav-tab').removeClass('nav-tab-active');
                 $('.nav-tab[data-tab="equipment-details"]').addClass('nav-tab-active');

             });

             // Edit action
             $table.on('click', '.edit-equipment', (e) => {
                 e.preventDefault();
                 const id = $(e.currentTarget).data('id');
                 this.loadEquipmentForEdit(id);
             });

             // Delete action
             $table.on('click', '.delete-equipment', (e) => {
                 const id = $(e.currentTarget).data('id');
                 this.handleDelete(id);
             });
         },

         async loadEquipmentForEdit(id) {
             if (!id) return;

             try {
                 const response = await $.ajax({
                     url: wpEquipmentData.ajaxUrl,
                     type: 'POST',
                     data: {
                         action: 'get_equipment',
                         id: id,
                         nonce: wpEquipmentData.nonce
                     }
                 });

                 if (response.success) {
                     if (window.EditEquipmentForm) {
                         window.EditEquipmentForm.showEditForm(response.data);
                     } else {
                         EquipmentToast.error('Komponen form edit tidak tersedia');
                     }
                 } else {
                     EquipmentToast.error(response.data?.message || 'Gagal memuat data equipment');
                 }
             } catch (error) {
                 console.error('Load equipment error:', error);
                 EquipmentToast.error('Gagal menghubungi server');
             }
         },

         async handleDelete(id) {
             if (!id) return;

             // Tampilkan modal konfirmasi dengan WIModal
             WIModal.show({
                 title: 'Konfirmasi Hapus',
                 message: 'Yakin ingin menghapus equipment ini? Aksi ini tidak dapat dibatalkan.',
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
                                 action: 'delete_equipment',
                                 id: id,
                                 nonce: wpEquipmentData.nonce
                             }
                         });

                         if (response.success) {
                             EquipmentToast.success(response.data.message);

                             // Clear hash if deleted equipment is currently viewed
                             if (window.location.hash === `#${id}`) {
                                 window.location.hash = '';
                             }

                             this.refresh();
                             $(document).trigger('equipment:deleted');
                         } else {
                             EquipmentToast.error(response.data?.message || 'Gagal menghapus equipment');
                         }
                     } catch (error) {
                         console.error('Delete equipment error:', error);
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
         window.EquipmentDataTable = EquipmentDataTable;
         EquipmentDataTable.init();
     });

 })(jQuery);
