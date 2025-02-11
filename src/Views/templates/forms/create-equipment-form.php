<?php
/**
 * Create Equipment Form Template
 *
 * @package     WP_Equipment
 * @subpackage  Views/Templates
 * @version     1.0.0
 * @author      arisciwek
 * 
 * Path: /wp-equipment/src/Views/templates/forms/create-equipment-form.php
 * 
 * Description: Template form untuk menambah equipment baru.
 *              Menggunakan modal dialog untuk tampilan form.
 *              Includes validasi client-side dan permission check.
 *              Terintegrasi dengan AJAX submission dan toast notifications.
 * 
 * Changelog:
 * 1.0.0 - 2024-12-02 18:30:00
 * - Initial release
 * - Added permission check
 * - Added nonce security
 * - Added form validation
 * - Added AJAX integration
 * 
 * Dependencies:
 * - WordPress admin styles
 * - equipment-toast.js for notifications
 * - equipment-form.css for styling
 * - equipment-form.js for handling
 */

defined('ABSPATH') || exit;
?>

<div id="create-equipment-modal" class="modal-overlay" style="display: none;">
    <div class="modal-container">
        <div class="modal-header">
            <h3>Tambah Peralatan</h3>
            <button type="button" class="modal-close" aria-label="Close">&times;</button>
        </div>
        <div class="modal-content">
            <form id="create-equipment-form" method="post">
                <?php wp_nonce_field('wp_equipment_nonce'); ?>
                <input type="hidden" name="action" value="create_equipment">
                
                <div class="wp-equipment-form-group">
                    <label for="equipment-code" class="required-field">
                        <?php _e('Kode Equipment', 'wp-equipment'); ?>
                    </label>
                    <input type="text" 
                           id="equipment-code" 
                           name="code" 
                           class="small-text" 
                           maxlength="2" 
                           pattern="\d{2}"
                           required>
                    <p class="description">
                        <?php _e('Masukkan 2 digit angka', 'wp-equipment'); ?>
                    </p>
                </div>

                <div class="wp-equipment-form-group">
                    <label for="equipment-name" class="required-field">
                        <?php _e('Nama Equipment', 'wp-equipment'); ?>
                    </label>
                    <input type="text" 
                           id="equipment-name" 
                           name="name" 
                           class="regular-text" 
                           maxlength="100" 
                           required>
                </div>

                <?php if (current_user_can('edit_all_equipments')): ?>
                <div class="wp-equipment-form-group">
                    <label for="equipment-owner">
                        <?php _e('Admin', 'wp-equipment'); ?>
                    </label>
                    <select id="equipment-owner" name="user_id" class="regular-text">
                        <option value=""><?php _e('Pilih Admin', 'wp-equipment'); ?></option>
                        <?php
                        //$users = get_users(['role__in' => ['administrator', 'editor', 'author']]);
                        $users = get_users(['role__in' => ['Equipment']]);
                        foreach ($users as $user) {
                            printf(
                                '<option value="%d">%s</option>',
                                $user->ID,
                                esc_html($user->display_name)
                            );
                        }
                        ?>
                    </select>
                </div>
                <?php endif; ?>
                
                <div class="submit-wrapper">
                    <button type="submit" class="button button-primary">
                        <?php _e('Simpan', 'wp-equipment'); ?>
                    </button>
                    <button type="button" class="button cancel-create">
                        <?php _e('Batal', 'wp-equipment'); ?>
                    </button>
                    <span class="spinner"></span>
                </div>
            </form>
        </div>
    </div>
</div>
