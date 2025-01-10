<?php
/**
 * Equipment Employees Table Schema
 *
 * @package     WP_Equipment
 * @subpackage  Database/Tables
 * @version     1.0.0
 * @author      arisciwek
 *
 * Path: /wp-equipment/src/Database/Tables/EquipmentEmployees.php
 *
 * Description: Mendefinisikan struktur tabel employees.
 *              Table prefix yang digunakan adalah 'app_'.
 *              Includes relasi dengan tabel equipments.
 *              Menyediakan data karyawan equipment.
 *
 * Fields:
 * - id             : Primary key
 * - equipment_id    : Foreign key ke equipment
 * - licence_id      : Foreign key ke licence
 * - user_id        : Foreign key ke user
 * - name           : Nama karyawan
 * - position       : Jabatan karyawan
 * - department     : Departemen
 * - email          : Email karyawan (unique)
 * - phone          : Nomor telepon
 * - created_by     : User ID pembuat
 * - created_at     : Timestamp pembuatan
 * - updated_at     : Timestamp update terakhir
 * - status         : Status aktif/nonaktif
 *
 * Foreign Keys:
 * - equipment_id    : REFERENCES app_equipments(id) ON DELETE CASCADE
 *
 * Changelog:
 * 1.0.0 - 2024-01-07
 * - Initial version
 * - Added basic employee fields
 * - Added equipment relation
 * - Added contact information fields
 */

namespace WPEquipment\Database\Tables;

defined('ABSPATH') || exit;

class EquipmentEmployees {
    public static function get_schema() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'app_equipment_employees';
        $charset_collate = $wpdb->get_charset_collate();

        return "CREATE TABLE {$table_name} (
            id bigint(20) UNSIGNED NOT NULL auto_increment,
            equipment_id bigint(20) UNSIGNED NOT NULL,
            licence_id bigint(20) UNSIGNED NOT NULL,
            user_id bigint(20) UNSIGNED NOT NULL,
            name varchar(100) NOT NULL,
            position varchar(100) NULL,
            department varchar(100) NULL,
            email varchar(100) NOT NULL,
            phone varchar(20) NULL,
            created_by bigint(20) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            status enum('active','inactive') DEFAULT 'active',
            PRIMARY KEY (id),
            UNIQUE KEY email (email),
            KEY equipment_id_index (equipment_id),
            KEY created_by_index (created_by),
            CONSTRAINT `{$wpdb->prefix}app_equipment_employees_ibfk_1` 
                FOREIGN KEY (equipment_id) 
                REFERENCES `{$wpdb->prefix}app_equipments` (id) 
                ON DELETE CASCADE
        ) $charset_collate;";
    }
}
