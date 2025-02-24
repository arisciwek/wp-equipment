<?php
/**
 * Category Group Lists Template
 *
 * @package     WP_Equipment
 * @subpackage  Views/Templates/Category/Partials
 * @version     1.0.0
 * @author      arisciwek
 *
 * Path: /wp-equipment/src/Views/templates/category/partials/_category_group_lists.php
 *
 * Description: Template untuk menampilkan daftar group dalam kategori.
 *              Includes DataTable, loading states, empty states,
 *              dan action buttons dengan permission checks.
 *              Terintegrasi dengan WP_Equipment core.
 *
 * Changelog:
 * 1.0.0 - 2024-02-24
 * - Initial release
 * - Added loading states
 * - Added empty state messages
 * - Added proper DataTable structure
 */

defined('ABSPATH') || exit;
?>

<div id="group" class="tab-content">
    <div class="wp-equipment-group-header">
        <div class="group-header-title">
            <h3><?php _e('Daftar Group', 'wp-equipment'); ?></h3>
        </div>
        <div class="group-header-actions">
            <div id="tombol-tambah-group"></div>
        </div>
    </div>
    
    <div class="groups-table-wrapper">
        <table id="groups-table" class="display" style="width:100%">
            <thead>
                <tr>
                    <th><?php _e('Nama', 'wp-equipment'); ?></th>
                    <th><?php _e('Service', 'wp-equipment'); ?></th>
                    <th><?php _e('Dokumen', 'wp-equipment'); ?></th>
                    <th><?php _e('Status', 'wp-equipment'); ?></th>
                    <th><?php _e('Actions', 'wp-equipment'); ?></th>
                </tr>
            </thead>
        </table>
    </div>
</div>
<?php
// Include modal form (combined create/edit form)
require_once WP_EQUIPMENT_PATH . 'src/Views/templates/category/forms/group-form.php';
require_once WP_EQUIPMENT_PATH . 'src/Views/components/confirmation-modal.php';

// Render confirmation modal
wp_equipment_render_confirmation_modal();
?>
