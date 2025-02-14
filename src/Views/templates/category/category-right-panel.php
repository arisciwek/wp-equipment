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
<div class="nav-tab-wrapper">
    <a href="#" class="nav-tab nav-tab-active" data-tab="category-details">Details</a>
    <a href="#" class="nav-tab" data-tab="category-hierarchy">Hierarchy</a>
</div>

<?php 
// Load tab contents from separate files
foreach ([
    'category/partials/_category_details.php',
    'category/partials/_category_hierarchy.php'
] as $template) {
    include_once WP_EQUIPMENT_PATH . 'src/Views/templates/' . $template;
}
?>