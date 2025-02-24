<?php
/**
 * Group Form Modal Template
 *
 * @package     WP_Equipment
 * @subpackage  Views/Templates/Category/Forms
 * @version     1.0.0
 * @author      arisciwek
 *
 * Path: /wp-equipment/src/Views/templates/category/forms/group-form.php
 *
 * Description: Form modal untuk menambah dan mengedit group.
 *              Single form yang menangani operasi create dan edit.
 *              Includes input validation, file upload,
 *              dan AJAX submission handling.
 *              Terintegrasi dengan service selection.
 *
 * Changelog:
 * 1.0.0 - 2024-02-24
 * - Initial release
 * - Added service selection
 * - Added file upload
 * - Added validation markup
 */

defined('ABSPATH') || exit;
?>

<div id="group-modal" class="modal-overlay wp-equipment-modal" style="display: none;">
    <div class="modal-container">
        <div class="modal-header">
            <h3 id="group-modal-title"><?php _e('Tambah Group', 'wp-equipment'); ?></h3>
            <button type="button" class="modal-close" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>

        <form id="group-form" method="post" enctype="multipart/form-data">
            <?php wp_nonce_field('wp_equipment_nonce'); ?>
            <input type="hidden" name="id" id="group-id">
            <input type="hidden" name="service_id" id="group-service-id">

            <div class="modal-content">
                <!-- Service Selection -->
                <div class="group-form-section">
                    <div class="section-header">
                        <h4><?php _e('Informasi Service', 'wp-equipment'); ?></h4>
                    </div>
                    
                    <div class="group-form-group">
                        <label for="group-service" class="required-field">
                            <?php _e('Service', 'wp-equipment'); ?>
                        </label>
                        <select id="group-service" name="service_id" class="regular-text" required>
                            <option value=""><?php _e('Pilih Service', 'wp-equipment'); ?></option>
                        </select>
                        <p class="description">
                            <?php _e('Service yang terkait dengan group ini', 'wp-equipment'); ?>
                        </p>
                    </div>
                </div>

                <!-- Informasi Dasar -->
                <div class="group-form-section">
                    <div class="section-header">
                        <h4><?php _e('Informasi Dasar', 'wp-equipment'); ?></h4>
                    </div>
                    
                    <div class="group-form-group">
                        <label for="group-name" class="required-field">
                            <?php _e('Nama Group', 'wp-equipment'); ?>
                        </label>
                        <input type="text"
                               id="group-name"
                               name="nama"
                               class="regular-text"
                               maxlength="100"
                               required>
                        <p class="description">
                            <?php _e('Nama lengkap group', 'wp-equipment'); ?>
                        </p>
                    </div>

                    <div class="group-form-group">
                        <label for="group-keterangan">
                            <?php _e('Keterangan', 'wp-equipment'); ?>
                        </label>
                        <textarea id="group-keterangan"
                                name="keterangan"
                                class="regular-text"
                                maxlength="255"
                                rows="3"></textarea>
                        <p class="description">
                            <?php _e('Keterangan tambahan untuk group (opsional)', 'wp-equipment'); ?>
                        </p>
                    </div>
                </div>

                <!-- Dokumen -->
                <div class="group-form-section">
                    <div class="section-header">
                        <h4><?php _e('Dokumen', 'wp-equipment'); ?></h4>
                    </div>
                    
                    <div class="group-form-group">
                        <label for="group-dokumen">
                            <?php _e('Upload Dokumen', 'wp-equipment'); ?>
                        </label>
                        <input type="file"
                               id="group-dokumen"
                               name="dokumen"
                               accept=".docx,.odt">
                        <p class="description">
                            <?php _e('Format yang didukung: DOCX, ODT. Maksimal 5MB', 'wp-equipment'); ?>
                        </p>
                        <div id="current-document" class="edit-only" style="display: none;">
                            <p class="description"><?php _e('Dokumen saat ini:', 'wp-equipment'); ?></p>
                            <div id="document-info"></div>
                        </div>
                    </div>
                </div>

                <!-- Status (hanya muncul saat edit) -->
                <div class="group-form-group edit-only" style="display: none;">
                    <label for="group-status" class="required-field">
                        <?php _e('Status', 'wp-equipment'); ?>
                    </label>
                    <select id="group-status" name="status" required>
                        <option value="active"><?php _e('Aktif', 'wp-equipment'); ?></option>
                        <option value="inactive"><?php _e('Nonaktif', 'wp-equipment'); ?></option>
                    </select>
                </div>
            </div>

            <div class="modal-footer">
                <div class="group-form-actions">
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
