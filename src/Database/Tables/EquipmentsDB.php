<?php
/**
 * Equipments Table Schema
 *
 * @package     WP_Equipment
 * @subpackage  Database/Tables
 * @version     1.0.2
 * @author      arisciwek
 *
 * Path: /wp-equipment/src/Database/Tables/EquipmentsDB.php
 *
 * Description: Mendefinisikan struktur tabel equipments.
 *              Table prefix yang digunakan adalah 'app_'.
 *              Includes field untuk integrasi wilayah.
 *              Menyediakan foreign key untuk equipment-licence.
 *
 * Fields:
 * - id             : Primary key
 * - code           : Format 
 * - name           : Nama equipment
 * - nik            : Nomor Induk Kependudukan
 * - npwp           : Nomor Pokok Wajib Pajak
 * - provinsi_id    : ID provinsi (nullable)
 * - regency_id     : ID cabang (nullable)
 * - user_id        : ID User WP sebagai Owner (nullable)
 * - created_by     : User ID pembuat
 * - created_at     : Timestamp pembuatan
 * - updated_at     : Timestamp update terakhir
 *
 * Changelog:
 * 1.0.2 - 2024-01-19
 * - Modified code field to varchar(13) for new format CUST-TTTTRRRR
 * - Removed unique constraint from name field
 * - Added unique constraint for name+province+regency
 * 
 * 1.0.1 - 2024-01-11
 * - Added nik field with unique constraint
 * - Added npwp field with unique constraint
 * 
 * 1.0.0 - 2024-01-07
 * - Initial version
 */

namespace WPEquipment\Database\Tables;

defined('ABSPATH') || exit;

class EquipmentsDB {
    public static function get_schema() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'app_equipments';
        $charset_collate = $wpdb->get_charset_collate();

        return "CREATE TABLE {$table_name} (
            id bigint(20) UNSIGNED NOT NULL auto_increment,
            code varchar(10) NOT NULL,
            name varchar(100) NOT NULL,
            npwp varchar(20) NULL,
            nib varchar(20) NULL,
            status enum('inactive','active') NOT NULL DEFAULT 'inactive',
            provinsi_id bigint(20) UNSIGNED NULL,
            regency_id bigint(20) UNSIGNED NULL,
            user_id bigint(20) UNSIGNED NULL,
            created_by bigint(20) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY code (code),
            UNIQUE KEY nib (nib),
            UNIQUE KEY npwp (npwp),
            UNIQUE KEY name_region (name, provinsi_id, regency_id),
            KEY created_by_index (created_by)
        ) $charset_collate;";
    }
}
