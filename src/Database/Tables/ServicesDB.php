<?php
/**
 * Services Table Schema
 *
 * @package     WP_Equipment
 * @subpackage  Database/Tables
 * @version     1.0.0
 * @author      arisciwek
 *
 * Path: /wp-equipment/src/Database/Tables/ServicesDB.php
 *
 * Description: Mendefinisikan struktur tabel services.
 *              Table prefix yang digunakan adalah 'app_'.
 *              Menyediakan parent table untuk groups.
 */

namespace WPEquipment\Database\Tables;

defined('ABSPATH') || exit;

class ServicesDB {
    public static function get_schema() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'app_services';
        $charset_collate = $wpdb->get_charset_collate();

        return "CREATE TABLE {$table_name} (
            id bigint(20) UNSIGNED NOT NULL auto_increment,
            singkatan varchar(5) NOT NULL,
            nama varchar(100) NOT NULL,
            keterangan text NULL,
            status enum('active','inactive') DEFAULT 'active',
            created_by bigint(20) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY nama_index (nama),
            KEY status_index (status)
        ) $charset_collate;";
    }
}
