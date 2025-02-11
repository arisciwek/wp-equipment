/**
* Category DataTable Handler
*
* @package     WP_Equipment
* @subpackage  Assets/JS
* @version     1.0.0
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
            if (typeof EquipmentToast === 'undefined') {
                console.error('Required dependency not found: EquipmentToast');
                return;
            }

           this.initDataTable();
           this.bindEvents();
       },

       initDataTable() {
           this.table = $('#categories-table').DataTable({
               processing: true,
               serverSide: true,
               ajax: {
                   url: wpEquipmentData.ajaxUrl,
                   type: 'POST',
                   data: (d) => {
                       d.action = 'handle_category_datatable';
                       d.nonce = wpEquipmentData.nonce;
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
               drawCallback: () => {
                   if (this.currentRow) {
                       this.highlightRow(this.currentRow);
                   }
               }
           });
       },

       bindEvents() {
           $('#categories-table').on('click', '.view-category', (e) => {
               e.preventDefault();
               const id = $(e.currentTarget).data('id');
               window.location.hash = id;
           });

           $('#categories-table').on('click', '.edit-category', (e) => {
               e.preventDefault();
               const id = $(e.currentTarget).data('id');
               this.loadEditForm(id);
           });

           $('#categories-table').on('click', '.delete-category', (e) => {
               e.preventDefault();
               const id = $(e.currentTarget).data('id');
               this.confirmDelete(id);
           });
       },

       highlightRow(id) {
           this.currentRow = id;
           $('#categories-table tr').removeClass('highlight');
           $(`#categories-table tr[data-id="${id}"]`).addClass('highlight');
       },

       refresh() {
           this.table.ajax.reload(null, false);
       },

       loadEditForm(id) {
           $.ajax({
               url: wpEquipmentData.ajaxUrl,
               type: 'POST',
               data: {
                   action: 'get_category',
                   id: id,
                   nonce: wpEquipmentData.nonce
               },
               success: (response) => {
                   if (response.success) {
                       CategoryForm.populateEditForm(response.data.category);
                   } else {
                       CategoryToast.error(response.data.message);
                   }
               },
               error: () => {
                   CategoryToast.error('Failed to load category data');
               }
           });
       },

       confirmDelete(id) {
           if (confirm('Are you sure you want to delete this category?')) {
               $.ajax({
                   url: wpEquipmentData.ajaxUrl,
                   type: 'POST',
                   data: {
                       action: 'delete_category',
                       id: id,
                       nonce: wpEquipmentData.nonce
                   },
                   success: (response) => {
                       if (response.success) {
                           CategoryToast.success('Category deleted successfully');
                           $(document).trigger('category:deleted');
                       } else {
                           CategoryToast.error(response.data.message);
                       }
                   },
                   error: () => {
                       CategoryToast.error('Failed to delete category');
                   }
               });
           }
       }
   };

   $(document).ready(() => {
       window.CategoryDataTable = CategoryDataTable;
       CategoryDataTable.init();
   });

})(jQuery);
