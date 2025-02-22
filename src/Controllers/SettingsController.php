<?php
/**
 * File: SettingsController.php 
 * Path: /wp-equipment/src/Controllers/Settings/SettingsController.php
 * Description: Controller untuk mengelola halaman pengaturan plugin termasuk matrix permission
 * Version: 3.0.0
 * Last modified: 2024-11-28 08:45:00
 * 
 * Changelog:
 * v3.0.0 - 2024-11-28
 * - Perbaikan handling permission matrix
 * - Penambahan validasi dan error handling
 * - Optimasi performa loading data
 * - Penambahan logging aktivitas
 * 
 * v2.0.0 - 2024-11-27
 * - Integrasi dengan WordPress Roles API
 * 
 * Dependencies:
 * - PermissionModel
 * - SettingsModel 
 * - WordPress admin functions
 */

namespace WPEquipment\Controllers;

class SettingsController {
    public function init() {
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_init', [$this, 'register_development_settings']);
        
        // Register AJAX handlers with correct action names

        // Handled in SettingsController.php
        add_action('wp_ajax_generate_demo_data', [$this, 'handle_generate_demo_data']);

        add_action('wp_ajax_check_demo_data', [$this, 'handle_check_demo_data']);
    }

    public function __construct() {
        // Inisialisasi hooks saat controller dibuat
        $this->init();
    }

    /**
     * Get the appropriate generator class based on data type
     */
    private function getGeneratorClass($type) {


        error_log('=== Start WP Equipment getGeneratorClass ===');  // Log 1
        error_log('Received type: ' . $type);          // Log 2
        
        error_log('getGeneratorClass received type: [' . $type . ']');
        error_log('Type length: ' . strlen($type));
        error_log('Type character codes: ' . json_encode(array_map('ord', str_split($type))));   

        switch ($type) {
            case 'service':
                    return new \WPEquipment\Database\Demo\ServiceDemoData();
            case 'group':
                return new \WPEquipment\Database\Demo\GroupDemoData();        
            case 'category':
                return new \WPEquipment\Database\Demo\CategoryDemoData();
            // Add other types as needed
            default:
                throw new \Exception('Invalid demo data type: ' . $type);
        }
    }

    public function handle_generate_demo_data() {
        try {


            error_log('=== Start handle_generate_demo_data ===');  // Log 1
            
            // Validasi permissions first
            if (!current_user_can('manage_options')) {
                error_log('Permission denied');  // Log 2
                throw new \Exception('Permission denied');
            }
            error_log('Permission check passed');  // Log 3

            // Get and sanitize input
            $type = isset($_POST['type']) ? sanitize_text_field($_POST['type']) : '';
            $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';
            
            error_log('POST data - type: ' . print_r($_POST['type'], true));  // Log 4
            error_log('Sanitized type: ' . $type);  // Log 5
            
            if (!wp_verify_nonce($nonce, "generate_demo_{$type}")) {
                error_log('Nonce verification failed');  // Log 6
                throw new \Exception('Invalid security token');
            }
            error_log('Nonce verified');  // Log 7

            
            // Validate permissions first
            if (!current_user_can('manage_options')) {
                throw new \Exception('Permission denied');
            }

            $type = sanitize_text_field($_POST['type']);
            $nonce = sanitize_text_field($_POST['nonce']);

            if (!wp_verify_nonce($nonce, "generate_demo_{$type}")) {
                throw new \Exception('Invalid security token');
            }

            // Check if development mode is enabled
            $dev_settings = get_option('wp_equipment_development_settings', []);
            if (empty($dev_settings['enable_development'])) {
                wp_send_json_error([
                    'message' => 'Development mode is not enabled. Please enable it in settings first.',
                    'type' => 'dev_mode_off'
                ]);
                return;
            }

            // Get the generator class based on type
            $generator = $this->getGeneratorClass($type);
            
            // Run the generator
            if ($generator->run()) {
                // Clear relevant caches if needed
                if ($type === 'category') {
                    $cache = new \WPEquipment\Cache\EquipmentCacheManager();
                    $cache->invalidateDataTableCache('category_list');
                    $cache->delete('category_tree');
                }

                wp_send_json_success([
                    'message' => ucfirst($type) . ' data generated successfully.',
                    'type' => 'success'
                ]);
            } else {
                wp_send_json_error([
                    'message' => 'Failed to generate demo data.',
                    'type' => 'error'
                ]);
            }

        } catch (\Exception $e) {
            error_log('Demo data generation failed: ' . $e->getMessage());
            wp_send_json_error([
                'message' => $e->getMessage(),
                'type' => 'error'
            ]);
        }
    }

    /**
     * Check demo data existence
     */
    public function handle_check_demo_data() {
        try {
            if (!current_user_can('manage_options')) {
                throw new \Exception('Permission denied');
            }

            $type = sanitize_text_field($_POST['type']);
            $nonce = sanitize_text_field($_POST['nonce']);

            if (!wp_verify_nonce($nonce, "check_demo_{$type}")) {
                throw new \Exception('Invalid security token');
            }

            // Get development mode status
            $dev_settings = get_option('wp_equipment_development_settings', []);
            $dev_mode_enabled = !empty($dev_settings['enable_development']);

            // Check data existence based on type
            global $wpdb;
            switch ($type) {
                case 'category':
                    $count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}app_categories");
                    break;
                default:
                    $count = 0;
            }

            wp_send_json_success([
                'has_data' => ($count > 0),
                'count' => $count,
                'dev_mode' => $dev_mode_enabled
            ]);

        } catch (\Exception $e) {
            wp_send_json_error([
                'message' => $e->getMessage()
            ]);
        }
    }

    public function register_settings() {
        // General Settings
        register_setting(
            'wp_equipment_settings',
            'wp_equipment_settings',
            array(
                'sanitize_callback' => [$this, 'sanitize_settings'],
                'default' => array(
                    'datatables_page_length' => 25,
                    'enable_cache' => 0,
                    'cache_duration' => 3600,
                    'enable_debug' => 0,
                    'enable_pusher' => 0,
                    'pusher_app_key' => '',
                    'pusher_app_secret' => '',
                    'pusher_cluster' => 'ap1'
                )
            )
        );

    }

    public function sanitize_settings($input) {
        $sanitized = array();

        // General settings sanitization
        $sanitized['datatables_page_length'] = absint($input['datatables_page_length']);
        $sanitized['enable_cache'] = isset($input['enable_cache']) ? 1 : 0;
        $sanitized['cache_duration'] = absint($input['cache_duration']);
        $sanitized['enable_debug'] = isset($input['enable_debug']) ? 1 : 0;

        // Pusher sanitization
        $sanitized['enable_pusher'] = isset($input['enable_pusher']) ? 1 : 0;
        $sanitized['pusher_app_key'] = sanitize_text_field($input['pusher_app_key']);
        $sanitized['pusher_app_secret'] = sanitize_text_field($input['pusher_app_secret']);
        $sanitized['pusher_cluster'] = sanitize_text_field($input['pusher_cluster']);

        return $sanitized;
    }
    public function register_development_settings() {
        register_setting(
            'wp_equipment_development_settings',
            'wp_equipment_development_settings',
            array(
                'sanitize_callback' => [$this, 'sanitize_development_settings'],
                'default' => array(
                    'enable_development' => 0,
                    'clear_data_on_deactivate' => 0
                )
            )
        );
    }

    public function sanitize_development_settings($input) {
        $sanitized = array();
        $sanitized['enable_development'] = isset($input['enable_development']) ? 1 : 0;
        $sanitized['clear_data_on_deactivate'] = isset($input['clear_data_on_deactivate']) ? 1 : 0;
        return $sanitized;
    }

    public function renderPage() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Anda tidak memiliki izin untuk mengakses halaman ini.', 'wp-equipment'));
        }

        $current_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'general';
        
        require_once WP_EQUIPMENT_PATH . 'src/Views/templates/settings/settings_page.php';
        $this->loadTabView($current_tab);
    }

    private function loadTabView($tab) {
        // Define allowed tabs and their templates
        $allowed_tabs = [
            'general' => 'tab-general.php',
            'permissions' => 'tab-permissions.php',
            'demo-data' => 'tab-demo-data.php'
        ];
        
        // Validate tab exists
        if (!isset($allowed_tabs[$tab])) {
            $tab = 'general';
        }
        
        $tab_file = WP_EQUIPMENT_PATH . 'src/Views/templates/settings/' . $allowed_tabs[$tab];
        
        if (file_exists($tab_file)) {
            require_once $tab_file;
        } else {
            echo sprintf(
                __('Tab file tidak ditemukan: %s', 'wp-equipment'),
                esc_html($tab_file)
            );
        }
    }

}
