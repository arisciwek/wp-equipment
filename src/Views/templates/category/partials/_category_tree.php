<?php
/**
* Category Tree Partial Template
*
* @package     WP_Equipment
* @subpackage  Views/Templates/Category/Partials
* @version     1.0.0
* @author      arisciwek
*
* Path: /wp-equipment/src/Views/templates/category/partials/_category_tree.php
*/

defined('ABSPATH') || exit;
?>

<div id="category-tree" class="tab-content">
   <div class="tree-container">
       <div class="tree-controls">
           <button type="button" class="button expand-all">
               <?php _e('Expand All', 'wp-equipment'); ?>
           </button>
           <button type="button" class="button collapse-all">
               <?php _e('Collapse All', 'wp-equipment'); ?>
           </button>
       </div>
       
       <div id="category-tree-view" class="tree-view">
           <!-- Tree will be populated by JavaScript -->
       </div>
   </div>

   <div class="tree-legend">
       <h4><?php _e('Legend', 'wp-equipment'); ?></h4>
       <ul>
           <li class="level-1"><?php _e('Level 1 - Main Category', 'wp-equipment'); ?></li>
           <li class="level-2"><?php _e('Level 2 - Sub Category', 'wp-equipment'); ?></li>
           <li class="level-3"><?php _e('Level 3 - Service Type', 'wp-equipment'); ?></li>
       </ul>
   </div>
</div>
