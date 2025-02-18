<?php
/**
* Sector Details Partial Template
*
* @package     WP_Equipment
* @subpackage  Views/Templates/Sector/Partials
* @version     1.0.0
* @author      arisciwek
*
* Path: /wp-equipment/src/Views/templates/sector/partials/_sector_details.php
*/

defined('ABSPATH') || exit;
?>

<div id="sector-details" class="tab-content active">
    <div class="sector-details-grid">
        <!-- Basic Information -->
        <div class="postbox">
            <h3 class="hndle">
                <span class="dashicons dashicons-portfolio"></span>
                <?php _e('Basic Information', 'wp-equipment'); ?>
            </h3>
            <div class="inside">
                <table class="form-table">
                    <tr>
                        <th><?php _e('Nama', 'wp-equipment'); ?></th>
                        <td><span id="sector-nama"></span></td>
                    </tr>
                    <tr>
                        <th><?php _e('Keterangan', 'wp-equipment'); ?></th>
                        <td><span id="sector-keterangan"></span></td>
                    </tr>
                    <tr>
                        <th><?php _e('Status', 'wp-equipment'); ?></th>
                        <td><span id="sector-status" class="status-badge"></span></td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Group Statistics -->
        <div class="postbox">
            <h3 class="hndle">
                <span class="dashicons dashicons-chart-bar"></span>
                <?php _e('Group Statistics', 'wp-equipment'); ?>
            </h3>
            <div class="inside">
                <table class="form-table">
                    <tr>
                        <th><?php _e('Total Groups', 'wp-equipment'); ?></th>
                        <td><span id="sector-total-groups">0</span></td>
                    </tr>
                    <tr>
                        <th><?php _e('Active Groups', 'wp-equipment'); ?></th>
                        <td><span id="sector-active-groups">0</span></td>
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
                        <td><span id="sector-created-by">-</span></td>
                    </tr>
                    <tr>
                        <th><?php _e('Created At', 'wp-equipment'); ?></th>
                        <td><span id="sector-created-at">-</span></td>
                    </tr>
                    <tr>
                        <th><?php _e('Last Updated', 'wp-equipment'); ?></th>
                        <td><span id="sector-updated-at">-</span></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
