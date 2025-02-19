<?php
/**
 * Groups Table Schema
 *
 * @package     WP_Equipment
 * @subpackage  Database/Tables
 * @version     1.0.0
 * @author      arisciwek
 *
 * Path: /wp-equipment/src/Database/Tables/GroupsDB.php
 *
 * Description: Mendefinisikan struktur tabel groups.
 *              Table prefix yang digunakan adalah 'app_'.
 *              Includes field untuk dokumen dengan format DOCX/ODT.
 *              Menyediakan relasi antara services dan categories.
 */

namespace WPEquipment\Database\Tables;

defined('ABSPATH') || exit;

class GroupsDB {
    public static function get_schema() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'app_groups';
        $charset_collate = $wpdb->get_charset_collate();

        return "CREATE TABLE {$table_name} (
            id bigint(20) UNSIGNED NOT NULL auto_increment,
            service_id bigint(20) UNSIGNED NOT NULL,
            nama varchar(100) NOT NULL,
            keterangan text NULL,
            dokumen_path varchar(255) NULL,
            dokumen_type enum('docx','odt') NULL,
            status enum('active','inactive') DEFAULT 'active',
            created_by bigint(20) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY service_id_index (service_id),
            KEY nama_index (nama),
            KEY status_index (status),
            CONSTRAINT fk_group_service 
                FOREIGN KEY (service_id) 
                REFERENCES {$wpdb->prefix}app_services(id)
                ON DELETE RESTRICT
                ON UPDATE CASCADE
        ) $charset_collate;";
    }
}
