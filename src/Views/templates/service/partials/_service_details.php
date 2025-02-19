<?php
/**
* Service Details Partial Template
*
* @package     WP_Equipment
* @subpackage  Views/Templates/Service/Partials
* @version     1.0.0
* @author      arisciwek
*
* Path: /wp-equipment/src/Views/templates/service/partials/_service_details.php
*/

defined('ABSPATH') || exit;
?>

<div id="service-details" class="tab-content active">
    <div class="service-details-grid">
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
                        <td><span id="service-nama"></span></td>
                    </tr>
                    <tr>
                        <th><?php _e('Keterangan', 'wp-equipment'); ?></th>
                        <td><span id="service-keterangan"></span></td>
                    </tr>
                    <tr>
                        <th><?php _e('Status', 'wp-equipment'); ?></th>
                        <td><span id="service-status" class="status-badge"></span></td>
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
                        <td><span id="service-total-groups">0</span></td>
                    </tr>
                    <tr>
                        <th><?php _e('Active Groups', 'wp-equipment'); ?></th>
                        <td><span id="service-active-groups">0</span></td>
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
                        <td><span id="service-created-by">-</span></td>
                    </tr>
                    <tr>
                        <th><?php _e('Created At', 'wp-equipment'); ?></th>
                        <td><span id="service-created-at">-</span></td>
                    </tr>
                    <tr>
                        <th><?php _e('Last Updated', 'wp-equipment'); ?></th>
                        <td><span id="service-updated-at">-</span></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
