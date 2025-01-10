<?php
/**
 * Licencees Table Schema
 *
 * @package     WP_Equipment
 * @subpackage  Database/Tables
 * @version     1.0.0
 * @author      arisciwek
 *
 * Path: /wp-equipment/src/Database/Tables/Licencees.php
 *
 * Description: Mendefinisikan struktur tabel licencees.
 *              Table prefix yang digunakan adalah 'app_'.
 *              Includes field untuk integrasi wilayah.
 *              Menyediakan foreign key ke equipments table.
 *
 * Fields:
 * - id             : Primary key
 * - equipment_id    : Foreign key ke equipment
 * - code           : Kode licence (4 digit)
 * - name           : Nama licence
 * - type           : Tipe wilayah (surat keterangan)
 * - provinsi_id    : ID provinsi (nullable)
 * - regency_id     : ID surat keterangan (nullable)
 * - created_by     : User ID pembuat
 * - created_at     : Timestamp pembuatan
 * - updated_at     : Timestamp update terakhir
 *
 * Foreign Keys:
 * - equipment_id    : REFERENCES app_equipments(id) ON DELETE CASCADE
 *
 * Changelog:
 * 1.0.0 - 2024-01-07
 * - Initial version
 * - Added basic licence fields
 * - Added wilayah integration fields
 * - Added foreign key constraint to equipments
 */

namespace WPEquipment\Database\Tables;

defined('ABSPATH') || exit;

namespace WPEquipment\Database\Tables;

class Licencees {
    public static function get_schema() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'app_licences';
        $charset_collate = $wpdb->get_charset_collate();

        return "CREATE TABLE {$table_name} (
            id bigint(20) UNSIGNED NOT NULL auto_increment,
            equipment_id bigint(20) UNSIGNED NOT NULL,
            code varchar(4) NOT NULL,
            name varchar(100) NOT NULL,
            type enum('pertama','berkala') NOT NULL,
            provinsi_id bigint(20) UNSIGNED NULL,
            regency_id bigint(20) UNSIGNED NULL,
            created_by bigint(20) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY equipment_name (equipment_id, name),
            UNIQUE KEY code (code),
            KEY created_by_index (created_by),
            CONSTRAINT `{$wpdb->prefix}app_licences_ibfk_1` 
                FOREIGN KEY (equipment_id) 
                REFERENCES `{$wpdb->prefix}app_equipments` (id) 
                ON DELETE CASCADE
        ) $charset_collate;";
    }
}
