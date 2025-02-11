/**
 * Licence DataTable Handler
 *
 * @package     WP_Equipment
 * @subpackage  Assets/JS/Branch
 * @version     1.1.0
 * @author      arisciwek
 *
 * Path: /wp-equipment/assets/js/licence/licence-datatable.js
 *
 * Description: Komponen untuk mengelola DataTables surat keterangan.
 *              Includes state management, export functions,
 *              dan error handling yang lebih baik.
 *
 * Dependencies:
 * - jQuery
 * - DataTables library
 * - EquipmentToast for notifications
 *
 * Changelog:
 * 1.1.0 - 2024-12-10
 * - Added state management
 * - Added export functionality
 * - Enhanced error handling
 * - Improved loading states
 */

 /**
  * Licence DataTable Handler - Fixed Implementation
  */
 (function($) {
     'use strict';

     const LicenceDataTable = {
         table: null,
         initialized: false,
         currentHighlight: null,
         equipmentId: null,
         $container: null,
         $tableContainer: null,
         $loadingState: null,
         $emptyState: null,
         $errorState: null,

         init(equipmentId) {
             // Cache DOM elements
             this.$container = $('#licence-list');
             this.$tableContainer = this.$container.find('.wi-table-container');
             this.$loadingState = this.$container.find('.licence-loading-state');
             this.$emptyState = this.$container.find('.empty-state');
             this.$errorState = this.$container.find('.error-state');

             if (this.initialized && this.equipmentId === equipmentId) {
                 this.refresh();
                 return;
             }

             this.equipmentId = equipmentId;
             this.showLoading();
             this.initDataTable();
             this.bindEvents();
         },

         bindEvents() {
             // CRUD event listeners
             $(document)
                 .off('licence:created.datatable licence:updated.datatable licence:deleted.datatable')
                 .on('licence:created.datatable licence:updated.datatable licence:deleted.datatable',
                     () => this.refresh());

             // Reload button handler
             this.$errorState.find('.reload-table').off('click').on('click', () => {
                 this.refresh();
             });

             // Direct event binding for action buttons
             $('#licence-table').off('click', '.delete-licence').on('click', '.delete-licence', (e) => {
                 e.preventDefault();
                 const id = $(e.currentTarget).data('id');
                 if (id) {
                     this.handleDelete(id);
                 }
             });
         },

         async handleDelete(id) {
             if (!id) return;

             if (typeof WIModal === 'undefined') {
                 console.error('WIModal is not defined');
                 LicenceToast.error('Error: Modal component not found');
                 return;
             }

             WIModal.show({
                 title: 'Konfirmasi Hapus',
                 message: 'Yakin ingin menghapus surat keterangan ini? Aksi ini tidak dapat dibatalkan.',
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
                                 action: 'delete_licence',
                                 id: id,
                                 nonce: wpEquipmentData.nonce
                             }
                         });

                         if (response.success) {
                             LicenceToast.success('Pertama/berkala berhasil dihapus');
                             this.refresh();
                             $(document).trigger('licence:deleted', [id]);
                         } else {
                             LicenceToast.error(response.data?.message || 'Gagal menghapus surat keterangan');
                         }
                     } catch (error) {
                         console.error('Delete licence error:', error);
                         LicenceToast.error('Gagal menghubungi server');
                     }
                 }
             });
         },

         initDataTable() {
             if ($.fn.DataTable.isDataTable('#licence-table')) {
                 $('#licence-table').DataTable().destroy();
             }

             // Initialize clean table structure
             $('#licence-table').empty().html(`
                 <thead>
                     <tr>
                         <th>Kode</th>
                         <th>Nama</th>
                         <th>Tipe</th>
                         <th>Aksi</th>
                     </tr>
                 </thead>
                 <tbody></tbody>
             `);

             const self = this;
             this.table = $('#licence-table').DataTable({
                 processing: true,
                 serverSide: true,
                 ajax: {
                     url: wpEquipmentData.ajaxUrl,
                     type: 'POST',
                     data: (d) => {
                         return {
                             ...d,
                             action: 'handle_licence_datatable',
                             equipment_id: this.equipmentId,
                             nonce: wpEquipmentData.nonce
                         };
                     },
                     error: (xhr, error, thrown) => {
                         console.error('DataTables Error:', error);
                         this.showError();
                     },
                     dataSrc: function(response) {
                         if (!response.data || response.data.length === 0) {
                             self.showEmpty();
                         } else {
                             self.showTable();
                         }
                         return response.data;
                     }
                 },
                 columns: [
                     { data: 'code', width: '15%' },
                     { data: 'name', width: '45%' },
                     { data: 'type', width: '20%' },
                     {
                         data: 'actions',
                         width: '20%',
                         orderable: false,
                         className: 'text-center'
                     }
                 ],
                 order: [[0, 'asc']],
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
                 }
             });

             this.initialized = true;
         },

         bindActionButtons() {
             // No need to rebind delete buttons as we're using event delegation above
             // Just handle other action buttons if needed
         },

         showLoading() {
             this.$tableContainer.hide();
             this.$emptyState.hide();
             this.$errorState.hide();
             this.$loadingState.show();
         },

         showEmpty() {
             this.$tableContainer.hide();
             this.$loadingState.hide();
             this.$errorState.hide();
             this.$emptyState.show();
         },

         showError() {
             this.$tableContainer.hide();
             this.$loadingState.hide();
             this.$emptyState.hide();
             this.$errorState.show();
         },

         showTable() {
             this.$loadingState.hide();
             this.$emptyState.hide();
             this.$errorState.hide();
             this.$tableContainer.show();
         },

         refresh() {
             if (this.table) {
                 this.showLoading();
                 this.table.ajax.reload(() => {
                     const info = this.table.page.info();
                     if (info.recordsTotal === 0) {
                         this.showEmpty();
                     } else {
                         this.showTable();
                     }
                 }, false);
             }
         }
     };

     // Initialize when document is ready
     $(document).ready(() => {
         window.LicenceDataTable = LicenceDataTable;
     });

 })(jQuery);
