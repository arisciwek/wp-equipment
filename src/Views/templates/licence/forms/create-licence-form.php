<?php
/**
 * Create Licence Form Template
 *
 * @package     WP_Equipment
 * @subpackage  Views/Templates/Licence/Forms
 * @version     1.0.0
 * @author      arisciwek
 *
 * Path: /wp-equipment/src/Views/templates/licence/forms/create-licence-form.php
 *
 * Description: Form modal untuk menambah surat keterangan baru.
 *              Includes input validation, error handling,
 *              dan AJAX submission handling.
 *              Terintegrasi dengan komponen toast notification.
 *
 * Changelog:
 * 1.0.0 - 2024-12-10
 * - Initial release
 * - Added form structure
 * - Added validation markup
 * - Added AJAX integration
 */
defined('ABSPATH') || exit;
?>

<div id="create-licence-modal" class="modal-overlay" style="display: none;">
    <div class="modal-container">
        <div class="modal-header">
            <h3><?php _e('Tambah Surat Keterangan', 'wp-equipment'); ?></h3>
            <button type="button" class="modal-close" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>

        <form id="create-licence-form" method="post">
            <?php wp_nonce_field('wp_equipment_nonce'); ?>
            <input type="hidden" name="equipment_id" id="equipment_id">

            <div class="modal-content">
                <div class="licence-form-group">
                    <label for="licence-code" class="required-field">
                        <?php _e('Kode Surat Keterangan', 'wp-equipment'); ?>
                    </label>
                    <input type="text"
                           id="licence-code"
                           name="code"
                           class="small-text"
                           maxlength="4"
                           pattern="\d{4}"
                           required>
                    <p class="description">
                        <?php _e('Masukkan 4 digit angka', 'wp-equipment'); ?>
                    </p>
                </div>
                <div class="licence-form-group">
                    <label for="licence-name" class="required-field">
                        <?php _e('Nama Surat Keterangan', 'wp-equipment'); ?>
                    </label>
                    <input type="text"
                           id="licence-name"
                           name="name"
                           class="regular-text"
                           maxlength="100"
                           required>
                </div>

                <div class="licence-form-group">
                    <label for="licence-type" class="required-field">
                        <?php _e('Tipe', 'wp-equipment'); ?>
                    </label>
                    <select id="licence-type" name="type" required>
                        <option value=""><?php _e('Pilih Tipe', 'wp-equipment'); ?></option>
                        <option value="pertama"><?php _e('Pertama', 'wp-equipment'); ?></option>
                        <option value="berkala"><?php _e('Berkala', 'wp-equipment'); ?></option>
                    </select>
                </div>
            </div>

            <div class="modal-footer">
                <div class="licence-form-actions">
                    <button type="button" class="button cancel-create">
                        <?php _e('Batal', 'wp-equipment'); ?>
                    </button>
                    <button type="submit" class="button button-primary">
                        <?php _e('Simpan', 'wp-equipment'); ?>
                    </button>
                    <span class="spinner"></span>
                </div>
            </div>
        </form>
    </div>
</div>
