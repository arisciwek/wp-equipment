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
 * - app_licences
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
        'app_equipments',
        'app_licences',
        'app_equipment_employees',
        'app_equipment_membership_levels'
    ];

    public static function run() {
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        global $wpdb;
        
        try {
            $wpdb->query('START TRANSACTION');

	        // Database Tables
	        require_once WP_EQUIPMENT_PATH . 'src/Database/Tables/Equipments.php';
	        require_once WP_EQUIPMENT_PATH . 'src/Database/Tables/Licencees.php';
	        require_once WP_EQUIPMENT_PATH . 'src/Database/Tables/EquipmentMembershipLevels.php';
	        require_once WP_EQUIPMENT_PATH . 'src/Database/Tables/EquipmentEmployees.php';

            // Create tables in correct order (parent tables first)
            dbDelta(Tables\EquipmentMembershipLevels::get_schema());
            dbDelta(Tables\Equipments::get_schema());
            dbDelta(Tables\Licencees::get_schema());
            dbDelta(Tables\EquipmentEmployees::get_schema());

            // Verify tables were created
            foreach (self::$tables as $table) {
                $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}{$table}'");
                if (!$table_exists) {
                    throw new \Exception("Failed to create table: {$wpdb->prefix}{$table}");
                }
            }

            // Drop any existing foreign keys for clean slate
            self::ensure_no_foreign_keys();
            
            // Add foreign key constraints
            self::add_foreign_keys();

            // Insert default membership levels
            //Tables\EquipmentMembershipLevels::insert_defaults();

            // Add demo data - TAMBAHKAN INI
            require_once WP_EQUIPMENT_PATH . 'src/Database/Demo_Data.php';
            Demo_Data::load();

            $wpdb->query('COMMIT');
            return true;

        } catch (\Exception $e) {
            $wpdb->query('ROLLBACK');
            error_log('Database installation failed: ' . $e->getMessage());
            return false;
        }
    }


    private static function ensure_no_foreign_keys() {
        global $wpdb;
        
        // Tables that might have foreign keys
        $tables_with_fk = ['app_licences', 'app_equipment_employees'];
        
        foreach ($tables_with_fk as $table) {
            $foreign_keys = $wpdb->get_results("
                SELECT CONSTRAINT_NAME 
                FROM information_schema.TABLE_CONSTRAINTS 
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = '{$wpdb->prefix}{$table}' 
                AND CONSTRAINT_TYPE = 'FOREIGN KEY'
            ");

            foreach ($foreign_keys as $key) {
                $wpdb->query("
                    ALTER TABLE {$wpdb->prefix}{$table} 
                    DROP FOREIGN KEY {$key->CONSTRAINT_NAME}
                ");
            }
        }
    }

    private static function add_foreign_keys() {
        global $wpdb;

        $constraints = [
            // Licencees constraints
            [
                'name' => 'fk_licence_equipment',
                'sql' => "ALTER TABLE {$wpdb->prefix}app_licences
                         ADD CONSTRAINT fk_licence_equipment
                         FOREIGN KEY (equipment_id)
                         REFERENCES {$wpdb->prefix}app_equipments(id)
                         ON DELETE CASCADE"
            ],
            // Employee constraints
            [
                'name' => 'fk_employee_equipment',
                'sql' => "ALTER TABLE {$wpdb->prefix}app_equipment_employees
                         ADD CONSTRAINT fk_employee_equipment
                         FOREIGN KEY (equipment_id)
                         REFERENCES {$wpdb->prefix}app_equipments(id)
                         ON DELETE CASCADE"
            ],
            [
                'name' => 'fk_employee_licence',
                'sql' => "ALTER TABLE {$wpdb->prefix}app_equipment_employees
                         ADD CONSTRAINT fk_employee_licence
                         FOREIGN KEY (licence_id)
                         REFERENCES {$wpdb->prefix}app_licences(id)
                         ON DELETE CASCADE"
            ]
        ];

        foreach ($constraints as $constraint) {
            $result = $wpdb->query($constraint['sql']);
            if ($result === false) {
                throw new \Exception("Failed to add foreign key {$constraint['name']}: " . $wpdb->last_error);
            }
        }
    }
}
