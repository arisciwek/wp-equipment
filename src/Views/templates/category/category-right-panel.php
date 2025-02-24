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
<div class="wp-category-header">
    <div class="nav-tab-wrapper">
        <a href="#" class="nav-tab nav-tab-active" data-tab="category-details">Details</a>
        <a href="#" class="nav-tab" data-tab="service">Service</a>
        <a href="#" class="nav-tab" data-tab="group">Group</a>
        <a href="#" class="nav-tab" data-tab="category-hierarchy">Hierarchy</a>
    </div>
</div>
<div class="wp-category-content">
    <?php 
    // Load tab contents from separate files
    foreach ([
        'category/partials/_category_details.php',
        'category/partials/_category_hierarchy.php',
        'category/partials/_category_service_lists.php',
        'category/partials/_category_group_lists.php'
    ] as $template) {
        include_once WP_EQUIPMENT_PATH . 'src/Views/templates/' . $template;
    }
    ?>
</div>

<div class="wp-category-footer">
   
</div>