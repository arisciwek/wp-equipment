<?php
/**
 * Cache Diagnostics Template
 *
 * @package     WP_Equipment
 * @subpackage  Views/Admin
 * @version     1.0.0
 * @author      arisciwek
 *
 * Path: /wp-equipment/src/Views/templates/settings/tab-cache-diagnostics.php
 */

defined('ABSPATH') || exit;

// Enqueue diagnostics-tab-script.js
wp_enqueue_script('wp-equipment-diagnostics-tab', 
    WP_EQUIPMENT_URL . 'assets/js/settings/diagnostics-tab-script.js', 
    ['jquery'], 
    WP_EQUIPMENT_VERSION, 
    true
);

// Lokalisasi script untuk wpEquipmentData
wp_localize_script('wp-equipment-diagnostics-tab', 'wpEquipmentData', [
    'ajaxUrl' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('wp_equipment_nonce')
]);

?>

<div class="wrap wp-equipment-diagnostics">
    <h2><?php _e('WP Equipment Diagnostics', 'wp-equipment'); ?></h2>
    
    <div class="diagnostics-tabs">
        <a href="#" class="diagnostics-tab active" data-tab="cache"><?php _e('Cache', 'wp-equipment'); ?></a>
        <a href="#" class="diagnostics-tab" data-tab="database"><?php _e('Database', 'wp-equipment'); ?></a>
        <a href="#" class="diagnostics-tab" data-tab="system"><?php _e('System', 'wp-equipment'); ?></a>
    </div>
    
    <div class="diagnostics-content cache active" id="cache-diagnostics">
        <div class="diagnostics-card">
            <h3><?php _e('Cache Diagnostics', 'wp-equipment'); ?></h3>
            
            <div class="diagnostics-loading">
                <span class="spinner is-active"></span>
                <?php _e('Running cache diagnostics...', 'wp-equipment'); ?>
            </div>
            
            <div class="diagnostics-results" style="display: none;">
                <table class="widefat">
                    <thead>
                        <tr>
                            <th><?php _e('Setting', 'wp-equipment'); ?></th>
                            <th><?php _e('Value', 'wp-equipment'); ?></th>
                        </tr>
                    </thead>
                    <tbody id="cache-results">
                        <!-- Results will be loaded here -->
                    </tbody>
                </table>
                
                <h4><?php _e('Recommendations', 'wp-equipment'); ?></h4>
                <ul id="cache-recommendations">
                    <!-- Recommendations will be loaded here -->
                </ul>
            </div>
            
            <div class="diagnostics-actions">
                <button type="button" id="run-cache-diagnostics" class="button button-primary">
                    <?php _e('Run Diagnostics', 'wp-equipment'); ?>
                </button>
                
                <button type="button" id="clear-all-cache" class="button button-secondary">
                    <?php _e('Clear All Cache', 'wp-equipment'); ?>
                </button>
            </div>
        </div>
    </div>
    
    <div class="diagnostics-content database" id="database-diagnostics" style="display: none;">
        <div class="diagnostics-card">
            <h3><?php _e('Database Diagnostics', 'wp-equipment'); ?></h3>
            <p><?php _e('Database diagnostics will be implemented in a future update.', 'wp-equipment'); ?></p>
        </div>
    </div>
    
    <div class="diagnostics-content system" id="system-diagnostics" style="display: none;">
        <div class="diagnostics-card">
            <h3><?php _e('System Information', 'wp-equipment'); ?></h3>
            <p><?php _e('System diagnostics will be implemented in a future update.', 'wp-equipment'); ?></p>
        </div>
    </div>
</div>

<script>
</script>

<style>
.wp-equipment-diagnostics .diagnostics-tabs {
    margin: 20px 0;
    border-bottom: 1px solid #ccc;
}

.wp-equipment-diagnostics .diagnostics-tab {
    display: inline-block;
    padding: 10px 15px;
    margin-right: 5px;
    background: #f1f1f1;
    border: 1px solid #ccc;
    border-bottom: none;
    text-decoration: none;
    color: #444;
}

.wp-equipment-diagnostics .diagnostics-tab.active {
    background: #fff;
    border-bottom: 1px solid #fff;
    margin-bottom: -1px;
}

.wp-equipment-diagnostics .diagnostics-card {
    background: #fff;
    border: 1px solid #ccc;
    padding: 20px;
    margin-bottom: 20px;
}

.wp-equipment-diagnostics .diagnostics-actions {
    margin-top: 20px;
    padding-top: 15px;
    border-top: 1px solid #eee;
}

.wp-equipment-diagnostics .status-badge {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 3px;
    text-transform: uppercase;
    font-weight: bold;
    font-size: 12px;
}

.wp-equipment-diagnostics .status-badge.success {
    background: #d4edda;
    color: #155724;
}

.wp-equipment-diagnostics .status-badge.error {
    background: #f8d7da;
    color: #721c24;
}

.wp-equipment-diagnostics .status-badge.neutral {
    background: #e2e3e5;
    color: #383d41;
}
</style>
