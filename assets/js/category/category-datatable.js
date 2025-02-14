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
       currentRow: null,

       init() {
            // Check dependencies
            if (typeof EquipmentToast === 'undefined') {
                console.error('Required dependency not found: EquipmentToast');
                return;
            }

            if (typeof CategoryForm === 'undefined') {
                console.error('Required dependency not found: CategoryForm');
                return;
            }

           this.initDataTable();
           this.bindEvents();
       },

       initDataTable() {
           if ($.fn.DataTable.isDataTable('#categories-table')) {
               $('#categories-table').DataTable().destroy();
           }

           this.table = $('#categories-table').DataTable({
               processing: true,
               serverSide: true,
               ajax: {
                   url: wpEquipmentData.ajaxUrl,
                   type: 'POST',
                   data: (d) => {
                       d.action = 'handle_category_datatable';
                       d.nonce = wpEquipmentData.nonce;
                   },
                   error: (xhr, error, thrown) => {
                       console.error('DataTables error:', error);
                       EquipmentToast.error('Gagal memuat data kategori');
                   }
               },
               columns: [
                   { data: 'code' },
                   { data: 'name' },
                   { data: 'level' },
                   { data: 'parent_name' },
                   { data: 'unit' },
                   { data: 'price' },
                   { 
                       data: 'actions',
                       orderable: false,
                       searchable: false
                   }
               ],
               order: [[0, 'asc']],
               pageLength: 25,
               language: {
                   processing: 'Loading...',
                   search: 'Search:',
                   lengthMenu: 'Show _MENU_ entries',
                   info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                   infoEmpty: 'Showing 0 to 0 of 0 entries',
                   infoFiltered: '(filtered from _MAX_ total entries)',
                   emptyTable: 'No categories found',
                   zeroRecords: 'No matching categories found'
               },
               drawCallback: (settings) => {
                   // Highlight current row if any
                   if (this.currentRow) {
                       this.highlightRow(this.currentRow);
                   }

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
           // View action
           $('#categories-table').on('click', '.view-category', (e) => {
            e.preventDefault();
            const id = $(e.currentTarget).data('id');
            if (id) {
                // Update hash
                window.location.hash = id;
                
                // Reset dan aktifkan tab details
                $('.nav-tab').removeClass('nav-tab-active');
                $('.nav-tab[data-tab="category-details"]').addClass('nav-tab-active');
                $('.tab-content').removeClass('active').hide();
                $('#category-details').addClass('active').show();
                
                // Buka panel kanan
                $('.wp-category-container').addClass('with-right-panel');
                $('.wp-category-right-panel').addClass('visible');
            }
        });

           // Edit action
           $('#categories-table').on('click', '.edit-category', (e) => {
               e.preventDefault();
               const id = $(e.currentTarget).data('id');
               this.loadEditForm(id);
           });

           // Delete action dengan konfirmasi
           $('#categories-table').on('click', '.delete-category', (e) => {
               e.preventDefault();
               const id = $(e.currentTarget).data('id');
               this.confirmDelete(id);
           });

           // Refresh table after CRUD operations
           $(document)
               .off('category:created.datatable category:updated.datatable category:deleted.datatable')
               .on('category:created.datatable category:updated.datatable category:deleted.datatable',
                   () => this.refresh());
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

       confirmDelete(id) {
           // Gunakan WIModal jika tersedia, jika tidak gunakan confirm biasa
           if (typeof WIModal !== 'undefined') {
               WIModal.show({
                   title: 'Konfirmasi Hapus',
                   message: 'Yakin ingin menghapus kategori ini? Aksi ini tidak dapat dibatalkan.',
                   icon: 'trash',
                   type: 'danger',
                   confirmText: 'Hapus',
                   confirmClass: 'button-danger',
                   cancelText: 'Batal',
                   onConfirm: () => this.handleDelete(id)
               });
           } else {
               if (confirm('Yakin ingin menghapus kategori ini?')) {
                   this.handleDelete(id);
               }
           }
       },
           
       async handleDelete(id) {
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
                   EquipmentToast.success(response.data.message || 'Kategori berhasil dihapus');

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
       },

       highlightRow(id) {
           this.currentRow = id;
           $('#categories-table tr').removeClass('highlight');
           $(`#categories-table tr[data-id="${id}"]`).addClass('highlight');

           // Scroll into view if needed
           const $row = $(`#categories-table tr[data-id="${id}"]`);
           if ($row.length) {
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

   $(document).ready(() => {
       window.CategoryDataTable = CategoryDataTable;
       CategoryDataTable.init();
   });

})(jQuery);
