<?php
/**
 * Service Form Modal Template
 *
 * @package     WP_Equipment
 * @subpackage  Views/Templates/Category/Forms
 * @version     1.0.0
 * @author      arisciwek
 *
 * Path: /wp-equipment/src/Views/templates/category/forms/service-form.php
 *
 * Description: Form modal untuk menambah dan mengedit layanan.
 *              Single form yang menangani operasi create dan edit.
 *              Includes input validation, error handling,
 *              dan AJAX submission handling.
 *              Terintegrasi dengan komponen toast notification.
 *
 * Changelog:
 * 1.0.0 - 2024-02-21
 * - Initial release
 * - Combined create/edit functionality
 * - Added validation markup
 * - Added AJAX integration
 */
defined('ABSPATH') || exit;
?>

<div id="service-modal" class="modal-overlay wp-equipment-modal" style="display: none;">
    <div class="modal-container">
        <div class="modal-header">
            <h3 id="service-modal-title"><?php _e('Tambah Layanan', 'wp-equipment'); ?></h3>
            <button type="button" class="modal-close" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>

        <form id="service-form" method="post">
            <?php wp_nonce_field('wp_equipment_nonce'); ?>
            <input type="hidden" name="id" id="service-id">
            <input type="hidden" name="category_id" id="service-category-id">

            <div class="modal-content">
                <!-- Informasi Dasar -->
                <div class="service-form-section">
                    <div class="section-header">
                        <h4><?php _e('Informasi Dasar', 'wp-equipment'); ?></h4>
                    </div>
                    
                    <div class="service-form-group">
                        <label for="service-name" class="required-field">
                            <?php _e('Nama Layanan', 'wp-equipment'); ?>
                        </label>
                        <input type="text"
                               id="service-name"
                               name="nama"
                               class="regular-text"
                               maxlength="100"
                               required>
                        <p class="description">
                            <?php _e('Nama lengkap layanan', 'wp-equipment'); ?>
                        </p>
                    </div>

                    <div class="service-form-group">
                        <label for="service-singkatan" class="required-field">
                            <?php _e('Singkatan', 'wp-equipment'); ?>
                        </label>
                        <input type="text"
                               id="service-singkatan"
                               name="singkatan"
                               class="regular-text"
                               maxlength="5"
                               required>
                        <p class="description">
                            <?php _e('Singkatan atau kode layanan (maks. 5 karakter)', 'wp-equipment'); ?>
                        </p>
                    </div>
                </div>

                <!-- Informasi Tambahan -->
                <div class="service-form-section">
                    <div class="section-header">
                        <h4><?php _e('Informasi Tambahan', 'wp-equipment'); ?></h4>
                    </div>
                    
                    <div class="service-form-group">
                        <label for="service-keterangan">
                            <?php _e('Keterangan', 'wp-equipment'); ?>
                        </label>
                        <textarea id="service-keterangan"
                                name="keterangan"
                                class="regular-text"
                                maxlength="255"
                                rows="3"></textarea>
                        <p class="description">
                            <?php _e('Keterangan tambahan untuk layanan (opsional)', 'wp-equipment'); ?>
                        </p>
                    </div>

                    <!-- Status (hanya muncul saat edit) -->
                    <div class="service-form-group edit-only" style="display: none;">
                        <label for="service-status" class="required-field">
                            <?php _e('Status', 'wp-equipment'); ?>
                        </label>
                        <select id="service-status" name="status" required>
                            <option value="active"><?php _e('Aktif', 'wp-equipment'); ?></option>
                            <option value="inactive"><?php _e('Nonaktif', 'wp-equipment'); ?></option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <div class="service-form-actions">
                    <button type="button" class="button cancel-button">
                        <?php _e('Batal', 'wp-equipment'); ?>
                    </button>
                    <button type="submit" class="button button-primary submit-button">
                        <?php _e('Simpan', 'wp-equipment'); ?>
                    </button>
                    <span class="spinner"></span>
                </div>
            </div>
        </form>
    </div>
</div>
