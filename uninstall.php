<?php
// If uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete plugin tables
global $wpdb;
$tables = array(
    $wpdb->prefix . 'wp_equipment_equipment'
);

foreach ($tables as $table) {
    $wpdb->query("DROP TABLE IF EXISTS $table");
}

// Delete plugin options
delete_option('wp_equipment_version');
delete_option('wp_equipment_db_version');
