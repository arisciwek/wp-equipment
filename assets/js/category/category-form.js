/**
* Category Form Handler
*
* @package     WP_Equipment
* @subpackage  Assets/JS
* @version     1.0.0
* @author      arisciwek
*
* Path: /wp-equipment/assets/js/category/category-form.js
*/

(function($) {
   'use strict';

   const CategoryForm = {
       init() {
           this.bindEvents();
           this.initParentSelect();
       },

       bindEvents() {
           $('#add-category-btn').on('click', () => this.showCreateForm());
           $('#submit-create-category').on('click', () => this.submitCreate());
           $('#submit-edit-category').on('click', () => this.submitEdit());
           $('#category-level, #edit-category-level').on('change', (e) => this.handleLevelChange(e));
           $('.wi-modal-close, #cancel-create-category, #cancel-edit-category').on('click', () => this.closeModals());
       },

       initParentSelect() {
           $.ajax({
               url: wpEquipmentData.ajaxUrl,
               type: 'POST',
               data: {
                   action: 'get_category_tree',
                   nonce: wpEquipmentData.nonce
               },
               success: (response) => {
                   if (response.success) {
                       this.populateParentSelects(response.data.tree);
                   }
               }
           });
       },

       populateParentSelects(categories, level = 1) {
           const selects = ['#category-parent', '#edit-category-parent'];
           selects.forEach(selectId => {
               const $select = $(selectId);
               $select.find('option:not(:first)').remove();
               
               const addOptions = (items, prefix = '') => {
                   items.forEach(cat => {
                       if (cat.level < level) {
                           $select.append(
                               $('<option>', {
                                   value: cat.id,
                                   text: prefix + cat.code + ' - ' + cat.name
                               })
                           );
                           if (cat.children) {
                               addOptions(cat.children, prefix + '-- ');
                           }
                       }
                   });
               };
               addOptions(categories);
           });
       },

       handleLevelChange(e) {
           const level = parseInt($(e.target).val());
           const formId = $(e.target).closest('form').attr('id');
           const parentSelect = $(`#${formId} [name="parent_id"]`);

           parentSelect.val('');
           if (level === 1) {
               parentSelect.prop('disabled', true);
           } else {
               parentSelect.prop('disabled', false);
               this.updateParentOptions(parentSelect, level);
           }
       },

       updateParentOptions(parentSelect, childLevel) {
           parentSelect.find('option:not(:first)').each(function() {
               const $option = $(this);
               const categoryLevel = parseInt($option.data('level'));
               $option.prop('disabled', categoryLevel >= childLevel);
           });
       },

       showCreateForm() {
           $('#create-category-form')[0].reset();
           $('#create-category-modal').show();
       },

       populateEditForm(category) {
           const form = $('#edit-category-form');
           form.find('#edit-category-id').val(category.id);
           form.find('#edit-category-code').val(category.code);
           form.find('#edit-category-name').val(category.name);
           form.find('#edit-category-description').val(category.description);
           form.find('#edit-category-level').val(category.level);
           form.find('#edit-category-parent').val(category.parent_id || '');
           form.find('#edit-category-unit').val(category.unit);
           form.find('#edit-category-price').val(category.price);
           form.find('#edit-category-sort-order').val(category.sort_order);

           this.handleLevelChange({ target: '#edit-category-level' });
           $('#edit-category-modal').show();
       },

       submitCreate() {
           const formData = new FormData($('#create-category-form')[0]);
           formData.append('action', 'create_category');
           formData.append('nonce', wpEquipmentData.nonce);

           $.ajax({
               url: wpEquipmentData.ajaxUrl,
               type: 'POST',
               data: formData,
               processData: false,
               contentType: false,
               success: (response) => {
                   if (response.success) {
                       CategoryToast.success('Category created successfully');
                       this.closeModals();
                       $(document).trigger('category:created', response.data);
                   } else {
                       CategoryToast.error(response.data.message);
                   }
               },
               error: () => CategoryToast.error('Failed to create category')
           });
       },

       submitEdit() {
           const formData = new FormData($('#edit-category-form')[0]);
           formData.append('action', 'update_category');
           formData.append('nonce', wpEquipmentData.nonce);

           $.ajax({
               url: wpEquipmentData.ajaxUrl,
               type: 'POST',
               data: formData,
               processData: false,
               contentType: false,
               success: (response) => {
                   if (response.success) {
                       CategoryToast.success('Category updated successfully');
                       this.closeModals();
                       $(document).trigger('category:updated', response.data);
                   } else {
                       CategoryToast.error(response.data.message);
                   }
               },
               error: () => CategoryToast.error('Failed to update category')
           });
       },

       closeModals() {
           $('.wi-modal').hide();
       }
   };

   $(document).ready(() => {
       window.CategoryForm = CategoryForm;
       CategoryForm.init();
   });

})(jQuery);
