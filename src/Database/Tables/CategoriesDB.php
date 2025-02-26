<?php
/**
 * Category Table Schema
 *
 * @package     WP_Equipment
 * @subpackage  Database/Tables
 * @version     1.0.0
 * @author      arisciwek
 *
 * Path: /wp-equipment/src/Database/Tables/CategoriesDB.php
 *
 * Description: Mendefinisikan struktur tabel categories.
 *              Table prefix yang digunakan adalah 'app_'.
 *              Includes field untuk hierarki dan relasi.
 *              Menyediakan foreign key untuk kategorisasi equipment.
 */

namespace WPEquipment\Database\Tables;

defined('ABSPATH') || exit;

class CategoriesDB {
    public static function get_schema() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'app_categories';
        $charset_collate = $wpdb->get_charset_collate();

        return "CREATE TABLE {$table_name} (
            id bigint(20) UNSIGNED NOT NULL auto_increment,
            code varchar(10) NOT NULL,
            name varchar(127) NOT NULL,
            description text NULL,
            level tinyint NOT NULL,
            parent_id bigint(20) UNSIGNED NULL,
            group_id bigint(20) UNSIGNED NULL,
            relation_id bigint(20) UNSIGNED NULL,
            sort_order int NOT NULL DEFAULT 0,
            unit varchar(50) NULL,
            pnbp decimal(10,2) NULL,
            status enum('active','inactive') DEFAULT 'inactive',
            created_by bigint(20) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY code (code),
            KEY parent_id_index (parent_id),
            KEY group_id_index (group_id),
            KEY level_index (level),
            KEY status_index (status),
            CONSTRAINT fk_category_group
                FOREIGN KEY (group_id)
                REFERENCES {$wpdb->prefix}app_groups(id)
                ON DELETE RESTRICT
                ON UPDATE CASCADE
        ) $charset_collate;";
    }
}
