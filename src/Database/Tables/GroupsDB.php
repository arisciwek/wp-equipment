<?php
/**
 * Groups Table Schema
 *
 * @package     WP_Equipment
 * @subpackage  Database/Tables
 * @version     1.0.1
 * @author      arisciwek
 *
 * Path: /wp-equipment/src/Database/Tables/GroupsDB.php
 *
 * Description: Mendefinisikan struktur tabel groups.
 *              Table prefix yang digunakan adalah 'app_'.
 *              Includes field untuk dokumen dengan format DOCX/ODT.
 *              Menyediakan relasi antara services dan categories.
 * 
 * Changes:
 * - Added dokumen_name for original filename
 * - Added dokumen_size for file size tracking
 * - Added dokumen_uploaded_at timestamp
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
            dokumen_name varchar(255) NULL COMMENT 'Original filename',
            dokumen_path varchar(255) NULL COMMENT 'File storage path',
            dokumen_type enum('docx','odt') NULL,
            dokumen_size bigint(20) NULL COMMENT 'File size in bytes',
            dokumen_uploaded_at datetime NULL COMMENT 'Upload timestamp',
            status enum('active','inactive') DEFAULT 'active',
            created_by bigint(20) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY service_id_index (service_id),
            KEY nama_index (nama),
            KEY status_index (status),
            KEY dokumen_type_index (dokumen_type),
            CONSTRAINT fk_group_service 
                FOREIGN KEY (service_id) 
                REFERENCES {$wpdb->prefix}app_services(id)
                ON DELETE RESTRICT
                ON UPDATE CASCADE
        ) $charset_collate;";
    }
}
