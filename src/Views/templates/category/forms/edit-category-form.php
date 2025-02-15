<?php
/**
 * Edit Category Form Template
 *
 * @package     WP_Equipment
 * @subpackage  Views/Templates/Category/Forms
 * @version     1.0.0
 * @author      arisciwek
 *
 * Path: /wp-equipment/src/Views/templates/category/forms/edit-category-form.php
 *
 * Description: Template modal form untuk edit kategori.
 *              Menangani modifikasi data kategori yang sudah ada.
 *              Includes validasi form dan data population.
 *              Terintegrasi dengan kategori panel kanan.
 *
 * Dependencies:
 * - jQuery
 * - jQuery Validation
 * - CategoryForm component
 * - WordPress AJAX API
 * - CategoryDataTable for refresh
 *
 * Related Files:
 * - create-category-form.php: Form pembuatan kategori baru
 * - category-form.js: Handler untuk operasi form
 * - category-script.js: Main script untuk manajemen kategori
 * - CategoryController.php: Backend handler
 *
 * Changelog:
 * 1.0.0 - 2024-02-12
 * - Initial release
 * - Added proper form population
 * - Added validation rules
 * - Added modal management
 * - Added hierarchy handling
 * - Added parent category updates
 *
 * Last modified: 2024-02-12 16:45:00
 */

defined('ABSPATH') || exit;
?>

<div id="edit-category-modal" class="modal-overlay" style="display: none;">
    <div class="modal-container">
        <!-- Header Modal -->
        <div class="modal-header">
            <h3><?php _e('Edit Kategori', 'wp-equipment'); ?> <span id="edit-category-title"></span></h3>
            <button type="button" class="modal-close">&times;</button>
        </div>

        <!-- Form Utama -->
        <form id="edit-category-form" method="post">
            <!-- Hidden Fields -->
            <input type="hidden" id="edit-category-id" name="id">
            
            <div class="modal-content">
                <!-- Informasi Dasar -->
                <div class="category-form-section">
                    <h4><?php _e('Informasi Dasar', 'wp-equipment'); ?></h4>
                    
                    <div class="form-row">
                        <!-- Kode Kategori -->
                        <div class="form-group info-dasar" data-field="code">
                            <label for="edit-category-code">
                                <?php _e('Kode', 'wp-equipment'); ?>
                                <span class="required">*</span>
                            </label>
                            <input type="text" 
                                   id="edit-category-code" 
                                   name="code" 
                                   class="regular-text" 
                                   maxlength="20"
                                   required>
                            <span class="form-text">
                                <?php _e('Kode unik untuk kategori', 'wp-equipment'); ?>
                            </span>
                        </div>

                        <!-- Nama Kategori -->
                        <div class="form-group info-dasar" data-field="name">
                            <label for="edit-category-name">
                                <?php _e('Nama', 'wp-equipment'); ?>
                                <span class="required">*</span>
                            </label>
                            <input type="text" 
                                   id="edit-category-name" 
                                   name="name" 
                                   class="regular-text" 
                                   maxlength="100"
                                   required>
                            <span class="form-text">
                                <?php _e('Nama lengkap kategori', 'wp-equipment'); ?>
                            </span>
                        </div>
                    </div>

                    <!-- Deskripsi -->
                    <div class="form-group info-produk">
                        <label for="edit-category-description">
                            <?php _e('Deskripsi', 'wp-equipment'); ?>
                        </label>
                        <textarea id="edit-category-description" 
                                  name="description" 
                                  rows="3" 
                                  class="regular-text"></textarea>
                    </div>
                </div>

                <!-- Informasi Hierarki -->
                <div class="category-form-section">
                    <h4><?php _e('Hierarki', 'wp-equipment'); ?></h4>
                    
                    <div class="form-row">
                        <!-- Level -->
                        <div class="form-group info-level">
                            <label for="edit-category-level">
                                <?php _e('Level', 'wp-equipment'); ?>
                                <span class="required">*</span>
                            </label>
                            <select id="edit-category-level" name="level" required>
                                <option value=""><?php _e('Pilih Level', 'wp-equipment'); ?></option>
                                <option value="1"><?php _e('Level 1 - Kategori Utama', 'wp-equipment'); ?></option>
                                <option value="2"><?php _e('Level 2 - Sub Kategori', 'wp-equipment'); ?></option>
                                <option value="3"><?php _e('Level 3 - Tipe Layanan', 'wp-equipment'); ?></option>
                            </select>
                            <span class="form-text level-warning" style="display: none; color: #d63638;">
                                <?php _e('Mengubah level dapat mempengaruhi hierarki kategori', 'wp-equipment'); ?>
                            </span>
                        </div>

                        <!-- Parent Category -->
                        <div class="form-group">
                            <label for="edit-category-parent">
                                <?php _e('Kategori Induk', 'wp-equipment'); ?>
                            </label>
                            <select id="edit-category-parent" name="parent_id" disabled>
                                <option value=""><?php _e('Pilih Kategori Induk', 'wp-equipment'); ?></option>
                            </select>
                            <input type="hidden" name="parent_id_hidden" value="">
                            <span class="form-text parent-warning" style="display: none;">
                                <?php _e('Kategori ini memiliki sub-kategori', 'wp-equipment'); ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Informasi Produk -->
                <div class="category-form-section">
                    <h4><?php _e('Informasi Produk', 'wp-equipment'); ?></h4>
                    
                    <div class="form-row">
                        <!-- Unit -->
                        <div class="form-group">
                            <label for="edit-category-unit">
                                <?php _e('Satuan', 'wp-equipment'); ?>
                            </label>
                            <input type="text" 
                                   id="edit-category-unit" 
                                   name="unit" 
                                   class="regular-text" 
                                   maxlength="50">
                            <span class="form-text">
                                <?php _e('Contoh: Pcs, Box, Unit', 'wp-equipment'); ?>
                            </span>
                        </div>

                        <!-- PNBP -->
                        <div class="form-group">
                            <label for="edit-category-pnbp">
                                <?php _e('Harga', 'wp-equipment'); ?>
                            </label>
                            <input type="number" 
                                   id="edit-category-pnbp" 
                                   name="pnbp" 
                                   class="regular-text" 
                                   min="0" 
                                   step="0.01">
                            <span class="form-text">
                                <?php _e('Harga default untuk kategori ini', 'wp-equipment'); ?>
                            </span>
                        </div>
                    </div>

                    <!-- Sort Order -->
                    <div class="form-group">
                        <label for="edit-category-sort-order">
                            <?php _e('Urutan', 'wp-equipment'); ?>
                        </label>
                        <input type="number" 
                               id="edit-category-sort-order" 
                               name="sort_order" 
                               class="small-text" 
                               min="0">
                        <span class="form-text">
                            <?php _e('Urutan tampilan (opsional)', 'wp-equipment'); ?>
                        </span>
                    </div>
                </div>

                <!-- Status Information -->
                <div class="category-form-section">
                    <h4><?php _e('Informasi Status', 'wp-equipment'); ?></h4>
                    
                    <div class="form-row status-info">
                        <div class="info-group">
                            <label><?php _e('Dibuat Oleh:', 'wp-equipment'); ?></label>
                            <span id="edit-category-created-by">-</span>
                        </div>
                        <div class="info-group">
                            <label><?php _e('Tanggal Dibuat:', 'wp-equipment'); ?></label>
                            <span id="edit-category-created-at">-</span>
                        </div>
                        <div class="info-group">
                            <label><?php _e('Terakhir Diubah:', 'wp-equipment'); ?></label>
                            <span id="edit-category-updated-at">-</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Modal -->
            <div class="modal-footer">
                <div class="form-submit">
                    <button type="submit" class="button button-primary" id="submit-edit-category">
                        <?php _e('Simpan Perubahan', 'wp-equipment'); ?>
                    </button>
                    <button type="button" class="button cancel-button" id="cancel-edit-category">
                        <?php _e('Batal', 'wp-equipment'); ?>
                    </button>
                    <span class="spinner"></span>
                </div>
            </div>
        </form>
    </div>
</div>
