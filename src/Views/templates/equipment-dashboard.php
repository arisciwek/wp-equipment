<?php
/**
 * Equipment Dashboard Template
 *
 * @package     WP_Equipment
 * @subpackage  Views/Templates
 * @version     1.0.0
 * @author      arisciwek
 *
 * Path: /wp-equipment/src/Views/templates/equipment-dashboard.php
 *
 * Description: Main dashboard template untuk manajemen equipment.
 *              Includes statistics overview, DataTable listing,
 *              right panel details, dan modal forms.
 *              Mengatur layout dan component integration.
 *
 * Changelog:
 * 1.0.1 - 2024-12-05
 * - Added edit form modal integration
 * - Updated form templates loading
 * - Improved modal management
 *
 * 1.0.0 - 2024-12-03
 * - Initial dashboard implementation
 * - Added statistics display
 * - Added equipment listing
 * - Added panel navigation
 */
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    <!-- Dashboard Section -->
    <div class="wp-equipment-dashboard">
        <div class="postbox">
            <div class="inside">
                <div class="main">
                    <h2>Statistik WP</h2>
                    <div class="wi-stats-container">
                        <div class="wi-stat-box equipment-stats">
                            <h3>Total Equipment</h3>
                            <p class="wi-stat-number"><span id="total-equipments">0</span></p>
                        </div>
                        <div class="wi-stat-box">
                            <h3>Total Surat Keterangan</h3>
                            <p class="wi-stat-number" id="total-licencees">0</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="wp-equipment-content-area">
        <div id="wp-equipment-main-container" class="wp-equipment-container">
            <!-- Left Panel -->
            <?php require_once WP_EQUIPMENT_PATH . 'src/Views/templates/equipment-right-panel.php'; ?>

            <!-- Right Panel -->
            <div id="wp-equipment-right-panel" class="wp-equipment-right-panel hidden">
                <?php require_once WP_EQUIPMENT_PATH . 'src/Views/templates/equipment-right-panel.php'; ?>
            </div>
        </div>
    </div>

    <!-- Modal Forms -->
    <?php
    require_once WP_EQUIPMENT_PATH . 'src/Views/templates/forms/create-equipment-form.php';
    require_once WP_EQUIPMENT_PATH . 'src/Views/templates/forms/edit-equipment-form.php';
    ?>
    <!-- Modal Templates -->
    <?php
    if (function_exists('wp_equipment_render_confirmation_modal')) {
        wp_equipment_render_confirmation_modal();
    }
    ?>
