<?php
/**
 * Category Service Lists Template
 *
 * @package     WP_Equipment
 * @subpackage  Views/Templates/Category/Partials
 * @version     1.0.0
 * @author      arisciwek
 *
 * Path: /wp-equipment/src/Views/templates/category/partials/_category_service_lists.php
 *
 * Description: Template untuk menampilkan daftar layanan dalam kategori.
 *              Includes DataTable, loading states, empty states,
 *              dan action buttons dengan permission checks.
 *              Terintegrasi dengan WP_Equipment core.
 *
 * Changelog:
 * 1.0.0 - 2024-02-21
 * - Initial release
 * - Added loading states
 * - Added empty state messages
 * - Added proper DataTable structure
 */

defined('ABSPATH') || exit;
?>

<div id="service" class="tab-content">
    <div class="wp-equipment-service-header">
        <div class="service-header-title">
            <h3><?php _e('Daftar Layanan', 'wp-equipment'); ?></h3>
        </div>
        <div class="service-header-actions">
            <div id="tombol-tambah-service"></div>
        </div>
    </div>
    
    <div class="services-table-wrapper">
        <table id="services-table" class="display" style="width:100%">
            <thead>
                <tr>
                    <th><?php _e('Singkatan', 'wp-equipment'); ?></th>
                    <th><?php _e('Nama', 'wp-equipment'); ?></th>
                    <th><?php _e('Status', 'wp-equipment'); ?></th>
                    <th><?php _e('Actions', 'wp-equipment'); ?></th>
                </tr>
            </thead>
        </table>
    </div>
</div>
<?php
// Include modal form (combined create/edit form)
require_once WP_EQUIPMENT_PATH . 'src/Views/templates/category/forms/service-form.php';
require_once WP_EQUIPMENT_PATH . 'src/Views/components/confirmation-modal.php';

// Render confirmation modal
wp_equipment_render_confirmation_modal();
?>
