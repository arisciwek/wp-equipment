<?php
/**
* Category Details Partial Template
*
* @package     WP_Equipment
* @subpackage  Views/Templates/Category/Partials
* @version     1.0.0
* @author      arisciwek
*
* Path: /wp-equipment/src/Views/templates/category/partials/_category_details.php
*/

defined('ABSPATH') || exit;
?>

<!-- _category_details.php -->
<div id="category-details" class="tab-content active">
    <div class="category-details-grid">
        <!-- Basic Information -->
        <div class="postbox">
            <h3 class="hndle">
                <span class="dashicons dashicons-category"></span>
                <?php _e('Basic Information', 'wp-equipment'); ?>
            </h3>
            <div class="inside">
                <table class="form-table">
                    <tr>
                        <th><?php _e('Code', 'wp-equipment'); ?></th>
                        <td><span id="category-code"></span></td>
                    </tr>
                    <tr>
                        <th><?php _e('Name', 'wp-equipment'); ?></th>
                        <td><span id="category-name"></span></td>
                    </tr>
                    <tr>
                        <th><?php _e('Description', 'wp-equipment'); ?></th>
                        <td><span id="category-description"></span></td>
                    </tr>
                    <tr>
                        <th><?php _e('Status', 'wp-equipment'); ?></th>
                        <td><span id="category-status" class="status-badge"></span></td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Category Information -->
        <div class="postbox">
            <h3 class="hndle">
                <span class="dashicons dashicons-networking"></span>
                <?php _e('Category Information', 'wp-equipment'); ?>
            </h3>
            <div class="inside">
                <table class="form-table">
                    <tr>
                        <th><?php _e('Level', 'wp-equipment'); ?></th>
                        <td><span id="category-level"></span></td>
                    </tr>
                    <tr>
                        <th><?php _e('Parent Category', 'wp-equipment'); ?></th>
                        <td><span id="category-parent"></span></td>
                    </tr>
                    <tr>
                        <th><?php _e('Sort Order', 'wp-equipment'); ?></th>
                        <td><span id="category-sort-order"></span></td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Product Details -->
        <div class="postbox">
            <h3 class="hndle">
                <span class="dashicons dashicons-cart"></span>
                <?php _e('Product Details', 'wp-equipment'); ?>
            </h3>
            <div class="inside">
                <table class="form-table">
                    <tr>
                        <th><?php _e('Unit', 'wp-equipment'); ?></th>
                        <td><span id="category-unit"></span></td>
                    </tr>
                    <tr>
                        <th><?php _e('PNBP', 'wp-equipment'); ?></th>
                        <td><span id="category-pnbp"></span></td>
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
                        <th><?php _e('Created By', 'wp-equipment'); ?></th>
                        <td><span id="category-created-by"></span></td>
                    </tr>
                    <tr>
                        <th><?php _e('Created At', 'wp-equipment'); ?></th>
                        <td><span id="category-created-at"></span></td>
                    </tr>
                    <tr>
                        <th><?php _e('Last Updated', 'wp-equipment'); ?></th>
                        <td><span id="category-updated-at"></span></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
