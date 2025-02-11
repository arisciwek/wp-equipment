<?php
/**
 * Category Table Schema
 *
 * @package     WP_Equipment
 * @subpackage  Database/Tables
 * @version     1.0.0
 * @author      arisciwek
 *
 * Path: /wp-equipment/src/Database/Tables/CategoryDB.php
 *
 * Description: Mendefinisikan struktur tabel categories.
 *              Table prefix yang digunakan adalah 'app_'.
 *              Includes field untuk hierarki dan relasi.
 *              Menyediakan foreign key untuk kategorisasi equipment.
 */

namespace WPEquipment\Database\Tables;

defined('ABSPATH') || exit;

class CategoryDB {
    public static function get_schema() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'app_categories';
        $charset_collate = $wpdb->get_charset_collate();

        return "CREATE TABLE {$table_name} (
            id bigint(20) UNSIGNED NOT NULL auto_increment,
            code varchar(10) NOT NULL,
            name varchar(100) NOT NULL,
            description text NULL,
            level tinyint NOT NULL,
            parent_id bigint(20) UNSIGNED NULL,
            group_id bigint(20) UNSIGNED NULL,
            relation_id bigint(20) UNSIGNED NULL,
            sort_order int NOT NULL DEFAULT 0,
            unit varchar(50) NULL,
            price decimal(10,2) NULL,
            status enum('active','inactive') DEFAULT 'active',
            created_by bigint(20) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY code (code),
            KEY parent_id_index (parent_id),
            KEY group_id_index (group_id),
            KEY level_index (level),
            KEY status_index (status)
        ) $charset_collate;";
    }
}
