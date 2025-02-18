<?php
/**
 * Database Installer
 *
 * @package     WP_Equipment
 * @subpackage  Database
 * @version     1.0.0
 * @author      arisciwek
 *
 * Path: /wp-equipment/src/Database/Installer.php
 *
 * Description: Mengelola instalasi database plugin.
 *              Handles table creation dengan foreign keys.
 *              Menggunakan transaction untuk data consistency.
 *              Includes demo data installation.
 *
 * Tables Created:
 * - app_equipments
 * - app_licencees
 * - app_equipment_employees
 * - app_equipment_membership_levels
 *
 * Foreign Keys:
 * - fk_licence_equipment
 * - fk_employee_equipment
 * - fk_employee_licence
 *
 * Changelog:
 * 1.0.0 - 2024-01-07
 * - Initial version
 * - Added table creation
 * - Added foreign key management
 * - Added demo data installation
 */

namespace WPEquipment\Database;

defined('ABSPATH') || exit;

class Installer {
    private static $tables = [
        'app_sectors',
        'app_groups',
        'app_categories',
        'app_equipments',
        'app_licencees'
    ];

    private static function debug($message) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("[Installer] " . $message);
        }
    }

    private static function verify_tables() {
        global $wpdb;
        foreach (self::$tables as $table) {
            $table_name = $wpdb->prefix . $table;
            $table_exists = $wpdb->get_var($wpdb->prepare(
                "SHOW TABLES LIKE %s",
                $table_name
            ));
            if (!$table_exists) {
                self::debug("Table not found: {$table_name}");
                throw new \Exception("Failed to create table: {$table_name}");
            }
            self::debug("Verified table exists: {$table_name}");
        }
    }
    
    public static function run() {
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        global $wpdb;
        
        try {
            $wpdb->query('START TRANSACTION');
            self::debug("Starting database installation...");

            // Create tables in order of dependencies
            self::debug("Creating sectors table...");
            dbDelta(Tables\SectorsDB::get_schema());

            self::debug("Creating groups table...");
            dbDelta(Tables\GroupsDB::get_schema());

            self::debug("Creating categories table...");
            dbDelta(Tables\CategoriesDB::get_schema());

            self::debug("Creating equipments table...");
            dbDelta(Tables\EquipmentsDB::get_schema());

            self::debug("Creating licencees table...");
            dbDelta(Tables\LicenceesDB::get_schema());

            // Verify all tables were created
            self::verify_tables();

            self::debug("Database installation completed successfully.");
            $wpdb->query('COMMIT');
            return true;

        } catch (\Exception $e) {
            $wpdb->query('ROLLBACK');
            self::debug('Database installation failed: ' . $e->getMessage());
            return false;
        }
    }
}
