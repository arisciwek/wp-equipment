<?php
/**
 * Create Category Form Template
 *
 * @package     WP_Equipment
 * @subpackage  Views/Templates/Category/Forms
 * @version     1.0.0
 * @author      arisciwek
 *
 * Path: /wp-equipment/src/Views/templates/category/forms/create-category-form.php
 *
 * Description: Template modal form untuk membuat kategori baru.
 *              Handle validasi input dan interaksi form.
 *              Terintegrasi dengan kategori model dan controller.
 *
 * Dependencies:
 * - jQuery
 * - jQuery Validation
 * - CategoryForm component
 * - WordPress AJAX API
 *
 * Changelog:
 * 1.0.0 - 2024-02-12
 * - Initial release
 * - Added proper form validation
 * - Added modal management
 * - Added hierarchy selection
 * - Added unit and pnbp fields
 *
 * Last modified: 2024-02-12 16:30:00
 */

defined('ABSPATH') || exit;
?>

<div id="create-category-modal" class="modal-overlay" style="display: none;">
    <div class="modal-container">
        <!-- Header Modal -->
        <div class="modal-header">
            <h3><?php _e('Tambah Kategori Baru', 'wp-equipment'); ?></h3>
            <button type="button" class="modal-close">&times;</button>
        </div>

        <!-- Form Utama -->
        <form id="create-category-form" method="post">

            <!-- Di dalam create-category-form.php dan edit-category-form.php -->
            <div class="modal-content">
                <div class="form-left-column">
                    <!-- Informasi Dasar -->
                    <div class="category-form-section">
                        <h4><?php _e('Informasi Dasar', 'wp-equipment'); ?></h4>
                        
                        <div class="form-row">
                            <!-- Kode Kategori -->
                            <div class="form-group">
                                <label for="category-code">
                                    <?php _e('Kode', 'wp-equipment'); ?>
                                    <span class="required">*</span>
                                </label>
                                <input type="text" 
                                       id="category-code" 
                                       name="code" 
                                       class="regular-text" 
                                       maxlength="20"
                                       required>
                                <span class="form-text">
                                    <?php _e('Kode unik untuk kategori', 'wp-equipment'); ?>
                                </span>
                            </div>

                            <!-- Nama Kategori -->
                            <div class="form-group">
                                <label for="category-name">
                                    <?php _e('Nama', 'wp-equipment'); ?>
                                    <span class="required">*</span>
                                </label>
                                <input type="text" 
                                       id="category-name" 
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
                        <div class="form-group">
                            <label for="category-description">
                                <?php _e('Deskripsi', 'wp-equipment'); ?>
                            </label>
                            <textarea id="category-description" 
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
                            <div class="form-group">
                                <label for="category-level">
                                    <?php _e('Level', 'wp-equipment'); ?>
                                    <span class="required">*</span>
                                </label>
                                <select id="category-level" name="level" required>
                                    <option value=""><?php _e('Pilih Level', 'wp-equipment'); ?></option>
                                    <option value="1"><?php _e('Level 1 - Kategori Utama', 'wp-equipment'); ?></option>
                                    <option value="2"><?php _e('Level 2 - Sub Kategori', 'wp-equipment'); ?></option>
                                    <option value="3"><?php _e('Level 3 - Tipe Layanan', 'wp-equipment'); ?></option>
                                </select>
                            </div>

                            <!-- Parent Category -->
                            <div class="form-group">
                                <label for="category-parent">
                                    <?php _e('Kategori Induk', 'wp-equipment'); ?>
                                </label>
                                <select id="category-parent" name="parent_id" disabled>
                                    <option value=""><?php _e('Pilih Kategori Induk', 'wp-equipment'); ?></option>
                                </select>
                                <span class="form-text">
                                    <?php _e('Pilih level terlebih dahulu', 'wp-equipment'); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-right-column">
                    <!-- Informasi Produk -->
                    <div class="category-form-section">
                        <h4><?php _e('Informasi Produk', 'wp-equipment'); ?></h4>
                        
                        <div class="form-row">
                            <!-- Unit -->
                            <div class="form-group">
                                <label for="category-unit">
                                    <?php _e('Satuan', 'wp-equipment'); ?>
                                </label>
                                <input type="text" 
                                       id="category-unit" 
                                       name="unit" 
                                       class="regular-text" 
                                       maxlength="10">
                                <span class="form-text">
                                    <?php _e('Contoh: Pcs, Box, Unit', 'wp-equipment'); ?>
                                </span>
                            </div>

                            <!-- PNBP -->
                            <div class="form-group">
                                <label for="category-pnbp">
                                    <?php _e('Harga', 'wp-equipment'); ?>
                                </label>
                                <input type="number" 
                                       id="category-pnbp" 
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
                        <div class="form-group info-urutan">
                            <label for="category-sort-order">
                                <?php _e('Urutan', 'wp-equipment'); ?>
                            </label>
                            <input type="number" 
                                   id="category-sort-order" 
                                   name="sort_order" 
                                   class="small-text" 
                                   min="0" 
                                   value="0">
                            <span class="form-text">
                                <?php _e('Urutan tampilan (opsional)', 'wp-equipment'); ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Modal -->
            <div class="modal-footer">
                <div class="form-submit">
                    <button type="submit" class="button button-primary" id="submit-create-category">
                        <?php _e('Simpan Kategori', 'wp-equipment'); ?>
                    </button>
                    <button type="button" class="button cancel-button" id="cancel-create-category">
                        <?php _e('Batal', 'wp-equipment'); ?>
                    </button>
                    <span class="spinner"></span>
                </div>
            </div>
        </form>
    </div>
</div>
