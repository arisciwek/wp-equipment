<?php
/**
 * Licence List Template
 *
 * @package     WP_Equipment
 * @subpackage  Views/Templates/Licence/Partials
 * @version     1.0.0
 * @author      arisciwek
 *
 * Path: /wp-equipment/src/Views/templates/licence/partials/_licence_list.php
 *
 * Description: Template untuk menampilkan daftar surat keterangan.
 *              Includes DataTable, loading states, empty states,
 *              dan action buttons dengan permission checks.
 *
 * Changelog:
 * 1.0.0 - 2024-12-10
 * - Initial release
 * - Added loading states
 * - Added empty state messages
 * - Added proper DataTable structure
 */

defined('ABSPATH') || exit;
?>

<div id="licence-list" class="tab-content">
    <div class="wp-equipment-licence-header">
        <div class="licence-header-title">
            <h3><?php _e('Daftar Surat Keterangan', 'wp-equipment'); ?></h3>
        </div>
        <div class="licence-header-actions">
            <?php if (current_user_can('add_licence')): ?>
                <button type="button" class="button button-primary" id="add-licence-btn">
                    <span class="dashicons dashicons-plus-alt"></span>
                    <?php _e('Tambah Surat Keterangan', 'wp-equipment'); ?>
                </button>
            <?php endif; ?>
        </div>
    </div>

    <div class="wp-equipment-licence-content">
        <!-- Loading State -->
        <div class="licence-loading-state" style="display: none;">
            <span class="spinner is-active"></span>
            <p><?php _e('Memuat data...', 'wp-equipment'); ?></p>
        </div>

        <!-- Empty State -->
        <div class="empty-state" style="display: none;">
            <div class="empty-state-content">
                <span class="dashicons dashicons-location"></span>
                <h4><?php _e('Belum Ada Data', 'wp-equipment'); ?></h4>
                <p>
                    <?php
                    if (current_user_can('add_licence')) {
                        _e('Belum ada surat keterangan yang ditambahkan. Klik tombol "Tambah Surat Keterangan" untuk menambahkan data baru.', 'wp-equipment');
                    } else {
                        _e('Belum ada surat keterangan yang ditambahkan.', 'wp-equipment');
                    }
                    ?>
                </p>
            </div>
        </div>

        <!-- Data Table -->
        <div class="wi-table-container">
            <table id="licence-table" class="display" style="width:100%">
                <thead>
                    <tr>
                        <th><?php _e('Kode', 'wp-equipment'); ?></th>
                        <th><?php _e('Nama', 'wp-equipment'); ?></th>
                        <th><?php _e('Tipe', 'wp-equipment'); ?></th>
                        <th class="text-center no-sort">
                            <?php _e('Aksi', 'wp-equipment'); ?>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <!-- DataTables will populate this -->
                </tbody>
                <tfoot>
                    <tr>
                        <th><?php _e('Kode', 'wp-equipment'); ?></th>
                        <th><?php _e('Nama', 'wp-equipment'); ?></th>
                        <th><?php _e('Tipe', 'wp-equipment'); ?></th>
                        <th><?php _e('Aksi', 'wp-equipment'); ?></th>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Error State -->
        <div class="error-state" style="display: none;">
            <div class="error-state-content">
                <span class="dashicons dashicons-warning"></span>
                <h4><?php _e('Gagal Memuat Data', 'wp-equipment'); ?></h4>
                <p><?php _e('Terjadi kesalahan saat memuat data. Silakan coba lagi.', 'wp-equipment'); ?></p>
                <button type="button" class="button reload-table">
                    <span class="dashicons dashicons-update"></span>
                    <?php _e('Muat Ulang', 'wp-equipment'); ?>
                </button>
            </div>
        </div>
    </div>

    <!-- Export Buttons (Optional, can be enabled via settings) -->
    <?php if (apply_filters('wp_equipment_enable_export', false)): ?>
        <div class="export-actions">
            <button type="button" class="button export-excel">
                <span class="dashicons dashicons-media-spreadsheet"></span>
                <?php _e('Export Excel', 'wp-equipment'); ?>
            </button>
            <button type="button" class="button export-pdf">
                <span class="dashicons dashicons-pdf"></span>
                <?php _e('Export PDF', 'wp-equipment'); ?>
            </button>
        </div>
    <?php endif; ?>
</div>

<?php
// Include related modals
require_once WP_EQUIPMENT_PATH . 'src/Views/templates/licence/forms/create-licence-form.php';
require_once WP_EQUIPMENT_PATH . 'src/Views/templates/licence/forms/edit-licence-form.php';
?>
