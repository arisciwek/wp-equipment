<?php
/**
 * Plugin Name: WP Equipment
 * Plugin URI:
 * Description: Plugin untuk mengelola data Equipment dan Licencenya
 *   
 * @package     WPEquipment
 * @version     1.0.0
 * @author      arisciwek
 * 
 * License: GPL v2 or later
 */

defined('ABSPATH') || exit;
define('WP_EQUIPMENT_VERSION', '1.0.0');

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

    private function defineConstants() {
        define('WP_EQUIPMENT_FILE', __FILE__);
        define('WP_EQUIPMENT_PATH', plugin_dir_path(__FILE__));
        define('WP_EQUIPMENT_URL', plugin_dir_url(__FILE__));
        define('WP_EQUIPMENT_DEVELOPMENT', false);
    }

    private function __construct() {
        $this->plugin_name = 'wp-equipment';
        $this->version = WP_EQUIPMENT_VERSION;


        // Register autoloader first
        spl_autoload_register(function ($class) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                //error_log("Autoloader attempting to load: " . $class);
            }

            $prefix = 'WPEquipment\\';
            $base_dir = plugin_dir_path(__FILE__) . 'src/';
            
            $len = strlen($prefix);
            if (strncmp($prefix, $class, $len) !== 0) {
                return;
            }
            
            $relative_class = substr($class, $len);
            $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
            
            if (defined('WP_DEBUG') && WP_DEBUG) {
                //error_log("Looking for file: " . $file);
                //error_log("File exists: " . (file_exists($file) ? 'yes' : 'no'));
            }

            if (file_exists($file)) {
                require $file;
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    //error_log("Successfully loaded: " . $file);
                    return true;
                }
            }
        });

        $this->defineConstants();
        $this->includeDependencies();
        $this->initHooks();
    }
    /*
    private function includeDependencies() {
        // Register includes autoloader first
        require_once WP_EQUIPMENT_PATH . 'includes/class-includes-autoloader.php';
        $includes_autoloader = new WP_Equipment_Includes_Autoloader(WP_EQUIPMENT_PATH . 'includes');
        $includes_autoloader->register();

        // Initialize wp-mpdf if available
        if (file_exists(WP_EQUIPMENT_PATH . '../wp-mpdf/wp-mpdf.php')) {
            require_once WP_EQUIPMENT_PATH . '../wp-mpdf/wp-mpdf.php';
            if (function_exists('wp_mpdf_init')) {
                wp_mpdf_init();
            }
        }

        // Initialize loader
        $this->loader = new WP_Equipment_Loader();

        // Initialize settings controller
        new \WPEquipment\Controllers\SettingsController();
    }
    */

    
    private function includeDependencies() {
        require_once WP_EQUIPMENT_PATH . 'includes/class-loader.php';
        require_once WP_EQUIPMENT_PATH . 'includes/class-activator.php';
        require_once WP_EQUIPMENT_PATH . 'includes/class-deactivator.php';
        require_once WP_EQUIPMENT_PATH . 'includes/class-dependencies.php';

        // Initialize wp-mpdf
        require_once WP_EQUIPMENT_PATH . '../wp-mpdf/wp-mpdf.php';
            if (function_exists('wp_mpdf_init')) {
                wp_mpdf_init();  // Initialize wp-mpdf first
            }

        $this->loader = new WP_Equipment_Loader();

        new \WPEquipment\Controllers\SettingsController();

    }

    

    private function initHooks() {
        register_activation_hook(__FILE__, array('WP_Equipment_Activator', 'activate'));
        register_deactivation_hook(__FILE__, array('WP_Equipment_Deactivator', 'deactivate'));

        // Inisialisasi dependencies
        $dependencies = new WP_Equipment_Dependencies($this->plugin_name, $this->version);

        // Register hooks
        $this->loader->add_action('admin_enqueue_scripts', $dependencies, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $dependencies, 'enqueue_scripts');

        // Inisialisasi menu
        $menu_manager = new \WPEquipment\Controllers\MenuManager($this->plugin_name, $this->version);
        $this->loader->add_action('init', $menu_manager, 'init');

        register_activation_hook(__FILE__, array('WP_Equipment_Activator', 'activate'));
        register_deactivation_hook(__FILE__, array('WP_Equipment_Deactivator', 'deactivate'));
        
        // Set auto increment untuk user ID
        //register_activation_hook(__FILE__, function() {
        //    global $wpdb;
        //    $wpdb->query("ALTER TABLE {$wpdb->prefix}users AUTO_INCREMENT = 211");
        //});

    $this->initControllers(); 
      new \WPEquipment\Controllers\Licence\LicenceController();
    }

    private function initControllers() {
        // Inisialisasi controllers
        $this->equipment_controller = new \WPEquipment\Controllers\EquipmentController();

        // Register AJAX hooks SEBELUM init

        // Tambahkan handler untuk stats
        add_action('wp_ajax_get_equipment_stats', [$this->equipment_controller, 'getStats']);
        add_action('wp_ajax_handle_equipment_datatable', [$this->equipment_controller, 'handleDataTableRequest']);
        add_action('wp_ajax_get_equipment', [$this->equipment_controller, 'show']);
    }


    public function run() {
        $this->loader->run();
    }
}

// Initialize plugin
function wp_equipment() {
    return WPEquipment::getInstance();
}

// Start the plugin
wp_equipment()->run();
