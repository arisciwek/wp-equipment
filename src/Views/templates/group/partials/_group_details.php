<?php
/**
* Group Details Partial Template
*
* @package     WP_Equipment
* @subpackage  Views/Templates/Group/Partials
* @version     1.0.0
* @author      arisciwek
*
* Path: /wp-equipment/src/Views/templates/group/partials/_group_details.php
*/

defined('ABSPATH') || exit;
?>

<div id="group-details" class="tab-content active">
    <div class="group-details-grid">
        <!-- Basic Information -->
        <div class="postbox">
            <h3 class="hndle">
                <span class="dashicons dashicons-groups"></span>
                <?php _e('Informasi Dasar', 'wp-equipment'); ?>
            </h3>
            <div class="inside">
                <table class="form-table">
                    <tr>
                        <th><?php _e('Nama', 'wp-equipment'); ?></th>
                        <td><span id="group-nama"></span></td>
                    </tr>
                    <tr>
                        <th><?php _e('Bidang Jasa', 'wp-equipment'); ?></th>
                        <td><span id="group-service"></span></td>
                    </tr>
                    <tr>
                        <th><?php _e('Keterangan', 'wp-equipment'); ?></th>
                        <td><span id="group-keterangan"></span></td>
                    </tr>
                    <tr>
                        <th><?php _e('Status', 'wp-equipment'); ?></th>
                        <td><span id="group-status" class="status-badge"></span></td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Document Information -->
        <div class="postbox">
            <h3 class="hndle">
                <span class="dashicons dashicons-media-document"></span>
                <?php _e('Dokumen', 'wp-equipment'); ?>
            </h3>
            <div class="inside">
                <table class="form-table">
                    <tr>
                        <th><?php _e('Tipe Dokumen', 'wp-equipment'); ?></th>
                        <td><span id="group-doc-type"></span></td>
                    </tr>
                    <tr>
                        <th><?php _e('File', 'wp-equipment'); ?></th>
                        <td>
                            <div id="group-doc-container">
                                <?php /* Dokumen link akan ditambahkan via JavaScript */ ?>
                            </div>
                        </td>
                    </tr>
                    <tr id="group-doc-upload-container" style="display: none;">
                        <th><?php _e('Upload Dokumen', 'wp-equipment'); ?></th>
                        <td>
                            <input type="file" id="group-doc-upload" name="dokumen" accept=".docx,.odt" />
                            <p class="description">
                                <?php _e('Format yang didukung: DOCX, ODT. Maksimal 5MB.', 'wp-equipment'); ?>
                            </p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Timeline Information -->
        <div class="postbox">
            <h3 class="hndle">
                <span class="dashicons dashicons-calendar-alt"></span>
                <?php _e('Timeline', 'wp-equipment'); ?>
            </h3>
            <div class="inside">
                <table class="form-table">
                    <tr>
                        <th><?php _e('Dibuat Oleh', 'wp-equipment'); ?></th>
                        <td><span id="group-created-by">-</span></td>
                    </tr>
                    <tr>
                        <th><?php _e('Dibuat Pada', 'wp-equipment'); ?></th>
                        <td><span id="group-created-at">-</span></td>
                    </tr>
                    <tr>
                        <th><?php _e('Terakhir Diupdate', 'wp-equipment'); ?></th>
                        <td><span id="group-updated-at">-</span></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Script Template untuk Dokumen -->
<script type="text/template" id="group-doc-template">
    <a href="{link}" target="_blank" class="button">
        <span class="dashicons dashicons-media-document"></span>
        <?php _e('Lihat Dokumen', 'wp-equipment'); ?> ({type})
    </a>
</script>

<!-- Template untuk Badge Status -->
<script type="text/template" id="status-badge-template">
    <span class="status-badge status-{status}">
        {label}
    </span>
</script>

<!-- CSS Inline untuk Badge Status -->
<style>
.status-badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
    text-transform: uppercase;
}
.status-badge.status-active {
    background-color: #e6ffe6;
    color: #006600;
    border: 1px solid #00cc00;
}
.status-badge.status-inactive {
    background-color: #ffe6e6;
    color: #cc0000;
    border: 1px solid #ff0000;
}
.group-details-grid {
    display: grid;
    grid-gap: 20px;
    margin: 20px 0;
}
.group-details-grid .postbox {
    margin: 0;
}
.group-details-grid .hndle {
    border-bottom: 1px solid #ccd0d4;
    padding: 8px 12px;
    margin: 0;
    font-size: 14px;
}
.group-details-grid .inside {
    padding: 12px;
    margin: 0;
}
.group-details-grid .dashicons {
    margin-right: 8px;
    color: #646970;
}
.group-details-grid .form-table th {
    width: 150px;
    padding: 8px 0;
}
.group-details-grid .form-table td {
    padding: 8px 0;
}
.group-details-grid .button .dashicons {
    margin-right: 4px;
    color: inherit;
}
#group-doc-upload {
    margin: 8px 0;
}
.description {
    font-size: 12px;
    color: #646970;
    margin-top: 4px;
}
</style>
