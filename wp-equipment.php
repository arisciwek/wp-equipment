<?php
/**
 * Plugin Name: WP Equipment
 * Plugin URI:
 * Description: Plugin untuk mengelola data Equipment dan Licencenya
 * Version: 1.0.0
 * Author: arisciwek
 * License: GPL v2 or later
 * 
 * @package     WP_Equipment
 * @version     1.0.0
 * @author      arisciwek
 */

defined('ABSPATH') || exit;

// Define plugin constants
define('WP_EQUIPMENT_VERSION', '1.0.0');
define('WP_EQUIPMENT_FILE', __FILE__);
define('WP_EQUIPMENT_PATH', plugin_dir_path(__FILE__));
define('WP_EQUIPMENT_URL', plugin_dir_url(__FILE__));
define('WP_EQUIPMENT_DEVELOPMENT', false);

class WPEquipment {
    private static $instance = null;
    private $loader;
    private $plugin_name;
    private $version;
    private $equipment_controller;
    private $dashboard_controller;

    public static function getInstance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->plugin_name = 'wp-equipment';
        $this->version = WP_EQUIPMENT_VERSION;

        // Register autoloader first
        require_once WP_EQUIPMENT_PATH . 'includes/class-autoloader.php';
        $autoloader = new WPEquipmentAutoloader('WPEquipment\\', WP_EQUIPMENT_PATH);
        $autoloader->register();

        $this->includeDependencies();
        $this->initHooks();
    }

    private function includeDependencies() {
        require_once WP_EQUIPMENT_PATH . 'includes/class-loader.php';
        require_once WP_EQUIPMENT_PATH . 'includes/class-activator.php';
        require_once WP_EQUIPMENT_PATH . 'includes/class-deactivator.php';
        require_once WP_EQUIPMENT_PATH . 'includes/class-dependencies.php';

        $this->loader = new WP_Equipment_Loader();

        // Initialize Settings Controller
        new \WPEquipment\Controllers\SettingsController();
    }

    private function initHooks() {
        // Register activation/deactivation hooks
        register_activation_hook(WP_EQUIPMENT_FILE, array('WP_Equipment_Activator', 'activate'));
        register_deactivation_hook(WP_EQUIPMENT_FILE, array('WP_Equipment_Deactivator', 'deactivate'));

        // Initialize dependencies
        $dependencies = new WP_Equipment_Dependencies($this->plugin_name, $this->version);

        // Register asset hooks
        $this->loader->add_action('admin_enqueue_scripts', $dependencies, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $dependencies, 'enqueue_scripts');

        // Initialize menu
        $menu_manager = new \WPEquipment\Controllers\MenuManager($this->plugin_name, $this->version);
        $this->loader->add_action('init', $menu_manager, 'init');

        // Initialize controllers
        $this->initControllers();
    }

    private function initControllers() {
        // Equipment Controller
        $this->equipment_controller = new \WPEquipment\Controllers\EquipmentController();

        new \WPEquipment\Controllers\Licence\LicenceController();
        new \WPEquipment\Controllers\ServiceController();
        new \WPEquipment\Controllers\GroupController();

        // Register AJAX handlers
        add_action('wp_ajax_get_equipment_stats', [$this->equipment_controller, 'getStats']);
        add_action('wp_ajax_handle_equipment_datatable', [$this->equipment_controller, 'handleDataTableRequest']);
        add_action('wp_ajax_get_equipment', [$this->equipment_controller, 'show']);
    }

    public function run() {
        $this->loader->run();
    }
}

function wp_equipment() {
    return WPEquipment::getInstance();
}

// Initialize the plugin
wp_equipment()->run();
