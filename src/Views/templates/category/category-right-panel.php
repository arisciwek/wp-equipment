<?php
/**
* Category Right Panel Template
*
* @package     WP_Equipment
* @subpackage  Views/Templates/Category
* @version     1.0.0
* @author      arisciwek
*
* Path: /wp-equipment/src/Views/templates/category/category-right-panel.php
*/

defined('ABSPATH') || exit;
?>

<!-- category-right-panel.php -->
<div class="wp-category-panel-header">
    <h2><?php _e('Detail Category:', 'wp-equipment'); ?> <span id="category-header-name"></span></h2>
    <button type="button" class="wp-category-close-panel">&times;</button>
</div>

<div class="wp-category-panel-content">
    <div class="nav-tab-wrapper">
        <a href="#" class="nav-tab nav-tab-active" data-tab="category-details">
            <?php _e('Details', 'wp-equipment'); ?>
        </a>
        <a href="#" class="nav-tab" data-tab="category-hierarchy">
            <?php _e('Hierarchy', 'wp-equipment'); ?>
        </a>
    </div>

    <!-- Detail Content -->
    <?php include WP_EQUIPMENT_PATH . 'src/Views/templates/category/partials/_category_details.php'; ?>
    <?php include WP_EQUIPMENT_PATH . 'src/Views/templates/category/partials/_category_hierarchy.php'; ?>
</div>
