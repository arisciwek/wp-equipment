<?php
/**
 * File: class-deactivator.php
 * Path: /wp-equipment/includes/class-deactivator.php
 * Description: Menangani proses deaktivasi plugin
 * 
 * @package     WP_Equipment
 * @subpackage  Includes
 * @version     1.0.1
 * @author      arisciwek
 * 
 * Description: Menangani proses deaktivasi plugin:
 *              - Menghapus seluruh tabel (fase development)
 *              - Membersihkan cache 
 *
 * Changelog:
 * 1.0.1 - 2024-01-07
 * - Added table cleanup during deactivation
 * - Added logging for development
 * 
 * 1.0.0 - 2024-11-23  
 * - Initial creation
 * - Added cache cleanup
 */

class WP_Equipment_Deactivator {
    private static function debug($message) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("[WP_Equipment_Deactivator] {$message}");
        }
    }

    private static function should_clear_data() {
        $dev_settings = get_option('wp_equipment_development_settings');
        if (isset($dev_settings['clear_data_on_deactivate']) && 
            $dev_settings['clear_data_on_deactivate']) {
            return true;
        }
        return defined('WP_EQUIPMENT_DEVELOPMENT') && WP_EQUIPMENT_DEVELOPMENT;
    }

    public static function deactivate() {
        global $wpdb;
        
        $should_clear_data = self::should_clear_data();

        // Hapus development settings terlebih dahulu
        delete_option('wp_equipment_development_settings');
        self::debug("Development settings cleared");

        try {
            // Only proceed with data cleanup if in development mode
            if (!$should_clear_data) {
                self::debug("Skipping data cleanup on plugin deactivation");
                return;
            }

            // Start transaction
            $wpdb->query('START TRANSACTION');

            // List of tables to be dropped in correct order (child tables first)
           $tables = [
                'app_licencees',              // Tabel independen 
                'app_equipments',             // Tabel independen
                'app_categories',             // Tabel yang berelasi ke groups
                'app_groups',                 // Tabel yang berelasi ke bidang jasa
                'app_sectors'                 // Tabel master/parent

            ];

            // Drop tables in order
            foreach ($tables as $table) {
                $table_name = $wpdb->prefix . $table;
                self::debug("Attempting to drop table: {$table_name}");
                $wpdb->query("DROP TABLE IF EXISTS {$table_name}");
            }

            // Clear all caches in wp_equipment group
            self::clearAllCaches();

            // Commit transaction
            $wpdb->query('COMMIT');
            
            self::debug("Plugin deactivation complete");

        } catch (\Exception $e) {
            $wpdb->query('ROLLBACK');
            self::debug("Error during deactivation: " . $e->getMessage());
        }
    }

    private static function clearAllCaches() {
        try {
            global $wp_object_cache;
            $cache_group = 'wp_equipment';

            // Hapus seluruh cache dalam grup
            if (isset($wp_object_cache->cache[$cache_group])) {
                unset($wp_object_cache->cache[$cache_group]);
                self::debug("Cleared all caches in group: {$cache_group}");
            }

        } catch (\Exception $e) {
            self::debug("Error clearing caches: " . $e->getMessage());
        }
    }

}