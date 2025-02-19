<?php
/**
* Group Form Partial Template
*
* @package     WP_Equipment
* @subpackage  Views/Templates/Group/Partials
* @version     1.0.0
* @author      arisciwek
*
* Path: /wp-equipment/src/Views/templates/group/partials/_group_form.php
*/

defined('ABSPATH') || exit;
?>

<div id="group-form-container" class="tab-content">
    <form id="group-form" method="post" enctype="multipart/form-data">
        <input type="hidden" name="id" id="group-id">
        
        <!-- Service Selection -->
        <div class="form-field">
            <label for="service_id"><?php _e('Bidang Jasa', 'wp-equipment'); ?> <span class="required">*</span></label>
            <select name="service_id" id="service_id" required>
                <option value=""><?php _e('Pilih Bidang Jasa', 'wp-equipment'); ?></option>
                <?php
                // Services akan diisi via AJAX
                ?>
            </select>
            <p class="description"><?php _e('Pilih sektor untuk grup ini', 'wp-equipment'); ?></p>
        </div>

        <!-- Basic Information -->
        <div class="form-field">
            <label for="nama"><?php _e('Nama Grup', 'wp-equipment'); ?> <span class="required">*</span></label>
            <input type="text" name="nama" id="nama" required maxlength="100">
            <p class="description"><?php _e('Masukkan nama grup (maksimal 100 karakter)', 'wp-equipment'); ?></p>
        </div>

        <div class="form-field">
            <label for="keterangan"><?php _e('Keterangan', 'wp-equipment'); ?></label>
            <textarea name="keterangan" id="keterangan" rows="4"></textarea>
            <p class="description"><?php _e('Masukkan keterangan grup (opsional)', 'wp-equipment'); ?></p>
        </div>

        <!-- Document Upload -->
        <div class="form-field">
            <label for="dokumen"><?php _e('Dokumen', 'wp-equipment'); ?></label>
            <input type="file" name="dokumen" id="dokumen" accept=".docx,.odt">
            <p class="description">
                <?php _e('Upload dokumen dalam format DOCX atau ODT (maksimal 5MB)', 'wp-equipment'); ?>
                <br>
                <span id="current-doc"></span>
            </p>
        </div>

        <!-- Status Selection (for edit mode) -->
        <div class="form-field status-field" style="display: none;">
            <label for="status"><?php _e('Status', 'wp-equipment'); ?></label>
            <select name="status" id="status">
                <option value="active"><?php _e('Aktif', 'wp-equipment'); ?></option>
                <option value="inactive"><?php _e('Tidak Aktif', 'wp-equipment'); ?></option>
            </select>
            <p class="description"><?php _e('Pilih status grup', 'wp-equipment'); ?></p>
        </div>

        <!-- Form Actions -->
        <div class="form-actions">
            <button type="submit" class="button button-primary save-group">
                <?php _e('Simpan Grup', 'wp-equipment'); ?>
            </button>
            <button type="button" class="button cancel-group">
                <?php _e('Batal', 'wp-equipment'); ?>
            </button>
        </div>
    </form>
</div>

<style>
.form-field {
    margin-bottom: 1.5em;
}

.form-field label {
    display: block;
    font-weight: 600;
    margin-bottom: 0.5em;
}

.form-field .required {
    color: #d63638;
}

.form-field input[type="text"],
.form-field select,
.form-field textarea {
    width: 100%;
    max-width: 100%;
}

.form-field textarea {
    min-height: 100px;
}

.form-field input[type="file"] {
    margin: 0.5em 0;
}

.form-actions {
    margin-top: 2em;
    padding-top: 1em;
    border-top: 1px solid #dcdcde;
}

.form-actions .button {
    margin-right: 0.5em;
}

.description {
    font-size: 13px;
    color: #646970;
    margin-top: 0.25em;
}

#current-doc {
    display: inline-block;
    margin-top: 0.5em;
    font-style: italic;
}

/* Error state */
.form-field.has-error label {
    color: #d63638;
}

.form-field.has-error input,
.form-field.has-error select,
.form-field.has-error textarea {
    border-color: #d63638;
}

.form-field .error-message {
    color: #d63638;
    font-size: 12px;
    margin-top: 0.25em;
}
</style>
