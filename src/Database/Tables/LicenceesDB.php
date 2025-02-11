<?php
/**
 * Licencees Table Schema
 *
 * @package     WP_Equipment
 * @subpackage  Database/Tables
 * @version     1.0.1
 * @author      arisciwek
 *
 * Path: /wp-equipment/src/Database/Tables/BranchesDB.php
 *
 * Description: Mendefinisikan struktur tabel licencees.
 *              Table prefix yang digunakan adalah 'app_'.
 *              Includes field untuk integrasi wilayah.
 *              Menyediakan foreign key ke equipments table.
 *
 * Fields:
 * - id             : Primary key
 * - equipment_id    : Foreign key ke equipment
 * - code           : Format 
 * - name           : Nama licence
 * - type           : Tipe wilayah (cabang)
 * - provinsi_id    : ID provinsi (nullable)
 * - regency_id     : ID cabang (nullable)
 * - created_by     : User ID pembuat
 * - created_at     : Timestamp pembuatan
 * - updated_at     : Timestamp update terakhir
 *
 * Foreign Keys:
 * - equipment_id    : REFERENCES app_equipments(id) ON DELETE CASCADE
 *
 * Changelog:
 * 1.0.1 - 2024-01-19
 * - Modified code field to varchar(17) for new format BR-TTTTRRRR-NNN
 * - Added unique constraint for equipment_id + code
 * 
 * 1.0.0 - 2024-01-07
 * - Initial version
 */

namespace WPEquipment\Database\Tables;

defined('ABSPATH') || exit;

class LicenceesDB {
    public static function get_schema() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'app_licencees';
        $charset_collate = $wpdb->get_charset_collate();

        return "CREATE TABLE {$table_name} (
            id bigint(20) UNSIGNED NOT NULL auto_increment,
            equipment_id bigint(20) UNSIGNED NOT NULL,
            code varchar(13) NOT NULL,
            name varchar(100) NOT NULL,
            type enum('cabang','pusat') NOT NULL,
            nitku varchar(20) NULL COMMENT 'Nomor Identitas Tempat Kegiatan Usaha',
            postal_code varchar(5) NULL COMMENT 'Kode pos',
            latitude decimal(10,8) NULL COMMENT 'Koordinat lokasi',
            longitude decimal(11,8) NULL COMMENT 'Koordinat lokasi',
            address text NULL,
            phone varchar(20) NULL,
            email varchar(100) NULL,
            provinsi_id bigint(20) UNSIGNED NULL,
            regency_id bigint(20) UNSIGNED NULL,
            user_id bigint(20) UNSIGNED NULL,
            created_by bigint(20) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            status enum('active','inactive') DEFAULT 'active',
            PRIMARY KEY  (id),
            UNIQUE KEY code (code),
            UNIQUE KEY equipment_name (equipment_id, name),
            KEY equipment_id_index (equipment_id),
            KEY created_by_index (created_by),
            KEY nitku_index (nitku),
            KEY postal_code_index (postal_code),
            KEY location_index (latitude, longitude)
        ) $charset_collate;";
    }
}
