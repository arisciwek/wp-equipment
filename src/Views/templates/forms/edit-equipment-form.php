<?php
/**
 * Edit Peralatan Form Template
 *
 * @package     WP_Equipment
 * @subpackage  Views/Templates/Forms
 * @version     1.0.0
 * @author      arisciwek
 * 
 * Path: /wp-equipment/src/Views/templates/forms/edit-equipment-form.php
 * 
 * Description: Modal form template untuk edit equipment.
 *              Includes validation, security checks,
 *              dan AJAX submission handling.
 *              Terintegrasi dengan EquipmentForm component.
 * 
 * Changelog:
 * 1.0.0 - 2024-12-05
 * - Initial implementation
 * - Added nonce security
 * - Added form validation
 * - Added permission checks
 * - Added AJAX integration
 */

defined('ABSPATH') || exit;
?>

<div id="edit-equipment-modal" class="modal-overlay" style="display: none;">
    <div class="modal-container">
        <div class="modal-header">
            <h3>Edit Peralatan</h3>
            <button type="button" class="modal-close" aria-label="Close">&times;</button>
        </div>
        <div class="modal-content">
            <div id="edit-mode">
                <form id="edit-equipment-form" class="wp-equipment-form">
                    <?php wp_nonce_field('wp_equipment_nonce'); ?>
                    <input type="hidden" id="equipment-id" name="id" value="">
                    
                    <div class="wp-equipment-form-group">
                        <label for="edit-code" class="required-field">Kode Equipment</label>
                        <input type="text" 
                               id="edit-code" 
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
                        <label for="edit-name" class="required-field">Nama Equipment</label>
                        <input type="text" 
                               id="edit-name" 
                               name="name" 
                               class="regular-text"
                               maxlength="100" 
                               required>
                    </div>

                    <?php if (current_user_can('edit_all_equipments')): ?>
                    <div class="wp-equipment-form-group">
                        <label for="edit-user" class="required-field">
                            <?php _e('User Admin', 'wp-equipment'); ?>
                        </label>
                        <select name="user_id" id="edit-user" class="regular-text">
                            <option value=""><?php _e('Pilih Admin', 'wp-equipment'); ?></option>
                            <?php
                            $users = get_users(['role__in' => ['Customer']]);
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
                        <button type="submit" class="button button-primary">Update</button>
                        <button type="button" class="button cancel-edit">Batal</button>
                        <span class="spinner"></span>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
