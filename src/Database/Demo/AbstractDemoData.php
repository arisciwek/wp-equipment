<?php
/**
 * Abstract Base Class for Demo Data Generation
 *
 * @package     WP_Equipment
 * @subpackage  Database/Demo
 * @version     1.0.0
 * @author      arisciwek
 *
 * Path: /wp-equipment/src/Database/Demo/AbstractDemoData.php
 *
 * Description: Base abstract class for demo data generation.
 *              Provides common functionality and structure for:
 *              - Membership levels data generation
 *              - Equipment data generation
 *              - Licence data generation
 *              - Employee data generation
 *              
 * Order of Execution:
 * 1. Membership Levels (base config)
 * 2. Equipments (with WP Users)
 * 3. Licencees 
 * 4. Employees
 *
 * Dependencies:
 * - WPUserGenerator for WordPress user creation
 * - WordPress database ($wpdb)
 * - WPEquipment Models:
 *   * EquipmentMembershipModel
 *   * EquipmentModel
 *   * LicenceModel
 *
 * Changelog:
 * 1.0.0 - 2024-01-27
 * - Initial version
 * - Added base abstract structure
 * - Added model dependencies
 */

namespace WPEquipment\Database\Demo;

use WPEquipment\Cache\EquipmentCacheManager;

defined('ABSPATH') || exit;

abstract class AbstractDemoData {
    protected $wpdb;
    protected $equipmentModel;
    protected $licenceModel;
    protected EquipmentCacheManager $cache;
    protected $debug_mode = false;

    private static $category_ids = [];
    protected $categories;
    protected $categoryModel;
    protected $categoryController;

    private static $sector_ids = [];
    protected $sectors;
    protected $sectorModel;
    protected $sectorController;


    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        
        // Initialize debug mode from WordPress settings
        $settings = get_option('wp_equipment_settings', []);
        $this->debug_mode = !empty($settings['enable_debug']);
        
        // Also check WP_DEBUG constant
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $this->debug_mode = true;
        }
        
        // Initialize cache manager immediately since it doesn't require plugins_loaded
        $this->cache = new EquipmentCacheManager();
        
        // Initialize models after plugins are loaded to prevent memory issues
        add_action('plugins_loaded', [$this, 'initModels'], 30);
        // Inisialisasi langsung model dan controller yang dibutuhkan
        
        // AbstractDemoData.php 
        $this->sectorModel = new \WPEquipment\Models\SectorModel();
        $this->sectorController = new \WPEquipment\Controllers\SectorController();

        $this->categoryModel = new \WPEquipment\Models\CategoryModel();
        $this->categoryController = new \WPEquipment\Controllers\CategoryController();


    }

    public function initModels() {
        // Only initialize if not already done
        if (!isset($this->equipmentModel)) {
            if (class_exists('\WPEquipment\Models\Equipment\EquipmentModel')) {
                $this->equipmentModel = new \WPEquipment\Models\Equipment\EquipmentModel();
            }
        }
        
        if (!isset($this->licenceModel)) {
            if (class_exists('\WPEquipment\Models\Licence\LicenceModel')) {
                $this->licenceModel = new \WPEquipment\Models\Licence\LicenceModel();
            }
        }
        
    }

    /**
     * Check if development mode is enabled
     */
    protected function isDevelopmentMode(): bool {
        // Check option from settings
        $dev_settings = get_option('wp_equipment_development_settings', []);
        $dev_mode_enabled = !empty($dev_settings['enable_development']);

        // Also check for WP_EQUIPMENT_DEVELOPMENT constant if defined
        if (defined('WP_EQUIPMENT_DEVELOPMENT')) {
            $dev_mode_enabled = $dev_mode_enabled || WP_EQUIPMENT_DEVELOPMENT;
        }

        return $dev_mode_enabled;
    }

    /**
     * Check if existing data should be cleared
     */
    protected function shouldClearData(): bool {
        return $this->isDevelopmentMode();
    }

    /**
     * Debug logging
     */
    protected function debug($message): void {
        if (!$this->debug_mode) {
            return;
        }

        if (is_array($message) || is_object($message)) {
            $message = print_r($message, true);
        }

        error_log('[' . get_class($this) . '] ' . $message);
    }

    /**
     * Validate data before generation
     */
    abstract protected function validate(): bool;

    /**
     * Generate the demo data
     */
    abstract protected function generate(): void;


    public function run() {
        try {
            // Ensure models are initialized
            $this->initModels();
            
            // Increase memory limit for demo data generation
            wp_raise_memory_limit('admin');
            
            $this->wpdb->query('START TRANSACTION');
            
            if (!$this->validate()) {
                throw new \Exception("Validation failed in " . get_class($this));
            }

            $this->generate();

            $this->wpdb->query('COMMIT');
            return true;

        } catch (\Exception $e) {
            $this->wpdb->query('ROLLBACK');
            $this->debug("Demo data generation failed: " . $e->getMessage());
            return false;
        }
    }

}
        