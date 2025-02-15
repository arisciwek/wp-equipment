<?php
/**
* Category Left Panel Template
*
* @package     WP_Equipment
* @subpackage  Views/Templates/Category
* @version     1.0.0
* @author      arisciwek
*
* Path: /wp-equipment/src/Views/templates/category/category-left-panel.php
*/

defined('ABSPATH') || exit;
?>

<div id="wp-category-left-panel" class="wp-category-left-panel">
   <div class="wp-category-header">
       <h2><?php _e('Category List', 'wp-equipment'); ?></h2>
       <div class="header-actions">
           <button type="button" class="button button-primary" id="add-category-btn">
               <?php _e('Add Category', 'wp-equipment'); ?>
           </button>
           <?php if (current_user_can('manage_options')): ?>
               <button type="button" class="button" id="generate-demo-categories-btn">
                   <?php _e('Generate Demo Data', 'wp-equipment'); ?>
               </button>
           <?php endif; ?>
       </div>
   </div>
   
   <div class="wp-category-content">
       <table id="categories-table" class="display" style="width:100%">
           <thead>
               <tr>
                   <th><?php _e('Code', 'wp-equipment'); ?></th>
                   <th><?php _e('Name', 'wp-equipment'); ?></th>
                   <th><?php _e('Level', 'wp-equipment'); ?></th>
                   <th><?php _e('Parent', 'wp-equipment'); ?></th>
                   <th><?php _e('Unit', 'wp-equipment'); ?></th>
                   <th><?php _e('PNBP', 'wp-equipment'); ?></th>
                   <th><?php _e('Actions', 'wp-equipment'); ?></th>
               </tr>
           </thead>
           <tbody>
           </tbody>
       </table>
   </div>
   <div class="wp-category-footer">
       
   </div>

</div>
