<?php
/**
* Edit Category Form Template
*
* @package     WP_Equipment
* @subpackage  Views/Templates/Category/Forms
* @version     1.0.0
* @author      arisciwek
*
* Path: /wp-equipment/src/Views/templates/category/forms/edit-category-form.php
*/

defined('ABSPATH') || exit;
?>
<div class="modal-overlay" style="display: none;">
    <div class="modal-container">
        <div class="modal-header">
            <h3>Add New Category</h3>
            <button type="button" class="modal-close">&times;</button>
        </div>
            <form id="category-form">
                <div class="modal-content">

               <form id="edit-category-form">
                   <input type="hidden" id="edit-category-id" name="id">
                   
                   <div class="form-group">
                       <label for="edit-category-code"><?php _e('Code', 'wp-equipment'); ?> <span class="required">*</span></label>
                       <input type="text" id="edit-category-code" name="code" required>
                   </div>

                   <div class="form-group">
                       <label for="edit-category-name"><?php _e('Name', 'wp-equipment'); ?> <span class="required">*</span></label>
                       <input type="text" id="edit-category-name" name="name" required>
                   </div>

                   <div class="form-group">
                       <label for="edit-category-description"><?php _e('Description', 'wp-equipment'); ?></label>
                       <textarea id="edit-category-description" name="description" rows="3"></textarea>
                   </div>

                   <div class="form-row">
                       <div class="form-group col-md-6">
                           <label for="edit-category-level"><?php _e('Level', 'wp-equipment'); ?> <span class="required">*</span></label>
                           <select id="edit-category-level" name="level" required>
                               <option value="1"><?php _e('Level 1 - Main Category', 'wp-equipment'); ?></option>
                               <option value="2"><?php _e('Level 2 - Sub Category', 'wp-equipment'); ?></option>
                               <option value="3"><?php _e('Level 3 - Service Type', 'wp-equipment'); ?></option>
                           </select>
                       </div>

                       <div class="form-group col-md-6">
                           <label for="edit-category-parent"><?php _e('Parent Category', 'wp-equipment'); ?></label>
                           <select id="edit-category-parent" name="parent_id">
                               <option value=""><?php _e('None', 'wp-equipment'); ?></option>
                           </select>
                       </div>
                   </div>

                   <div class="form-row">
                       <div class="form-group col-md-6">
                           <label for="edit-category-unit"><?php _e('Unit', 'wp-equipment'); ?></label>
                           <input type="text" id="edit-category-unit" name="unit">
                       </div>

                       <div class="form-group col-md-6">
                           <label for="edit-category-price"><?php _e('Price', 'wp-equipment'); ?></label>
                           <input type="number" id="edit-category-price" name="price" step="0.01" min="0">
                       </div>
                   </div>

                   <div class="form-group">
                       <label for="edit-category-sort-order"><?php _e('Sort Order', 'wp-equipment'); ?></label>
                       <input type="number" id="edit-category-sort-order" name="sort_order" min="0">
                   </div>
               </form>
            </div>
            <div class="modal-footer">
                <button type="submit" class="button button-primary">Save</button>
                <button type="button" class="button cancel-button">Cancel</button>
            </div>
        </form>
    </div>
</div>
