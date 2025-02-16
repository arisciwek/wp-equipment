<?php
/**
 * Category Dashboard Template
 *
 * @package     WP_Equipment
 * @subpackage  Views/Templates
 * @version     1.0.0
 * @author      arisciwek
 *
 * Path: /wp-equipment/src/Views/templates/category/category-dashboard.php
 */

defined('ABSPATH') || exit;
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <!-- Dashboard Section -->
    <div class="wp-category-dashboard">
        <div class="postbox">
            <div class="inside">
                <div class="main">
                    <h2><?php _e('Category Statistics', 'wp-equipment'); ?></h2>
                    <div class="wi-stats-container">
                        <div class="wi-stat-box category-stats">
                            <h3><?php _e('Total Categories', 'wp-equipment'); ?></h3>
                            <p class="wi-stat-number" id="total-categories">0</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="wp-category-content-area">
        <div id="wp-category-main-container" class="wp-category-container">
            <!-- Left Panel -->
            <?php require_once WP_EQUIPMENT_PATH . 'src/Views/templates/category/category-left-panel.php'; ?>

            <!-- Right Panel -->
            <div id="wp-category-right-panel" class="wp-category-right-panel hidden">
                <?php require_once WP_EQUIPMENT_PATH . 'src/Views/templates/category/category-right-panel.php'; ?>
            </div>
        </div>
    </div>

    <!-- Modal Forms -->
    <?php
    require_once WP_EQUIPMENT_PATH . 'src/Views/components/confirmation-modal.php';

    require_once WP_EQUIPMENT_PATH . 'src/Views/templates/category/forms/create-category-form.php';
    require_once WP_EQUIPMENT_PATH . 'src/Views/templates/category/forms/edit-category-form.php';
    ?>
</div>
<!-- Modal Confirmation Templates -->
<?php
    if (function_exists('wp_equipment_render_confirmation_modal')) {
        wp_equipment_render_confirmation_modal();
    }
?>
