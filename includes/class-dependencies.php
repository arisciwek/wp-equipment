<?php
/**
 * Dependencies Handler Class
 *
 * @package     WP_Equipment
 * @subpackage  Includes
 * @version     1.1.0
 * @author      arisciwek
 *
 * Path: /wp-equipment/includes/class-dependencies.php
 *
 * Description: Menangani dependencies plugin seperti CSS, JavaScript,
 *              dan library eksternal
 *
 * Changelog:
 * 1.1.0 - 2024-12-10
 * - Added licence management dependencies
 * - Added licence CSS and JS files
 * - Updated screen checks for licence assets
 * - Fixed path inconsistencies
 * - Added common-style.css
 *
 * 1.0.0 - 2024-11-23
 * - Initial creation
 * - Added asset enqueuing methods
 * - Added CDN dependencies
 */
class WP_Equipment_Dependencies {
    private $plugin_name;
    private $version;

    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    public function enqueue_styles() {
        $screen = get_current_screen();

        if (!$screen) return;

        // Settings page styles
        if ($screen->id === 'wp-equipment_page_wp-equipment-settings') {
            wp_enqueue_style('wp-equipment-common', WP_EQUIPMENT_URL . 'assets/css/settings/common-style.css', [], $this->version);
            wp_enqueue_style('wp-equipment-settings', WP_EQUIPMENT_URL . 'assets/css/settings/settings-style.css', ['wp-equipment-common'], $this->version);
            wp_enqueue_style('wp-equipment-modal', WP_EQUIPMENT_URL . 'assets/css/components/confirmation-modal.css', [], $this->version);

            $current_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'general';
            switch ($current_tab) {
                case 'permission':
                    wp_enqueue_style('wp-equipment-permission-tab', WP_EQUIPMENT_URL . 'assets/css/settings/permission-tab-style.css', [], $this->version);
                    break;
                case 'general':
                    wp_enqueue_style('wp-equipment-general-tab', WP_EQUIPMENT_URL . 'assets/css/settings/general-tab-style.css', [], $this->version);
                    break;
                case 'category':
                    wp_enqueue_style('wp-equipment-category-tab', WP_EQUIPMENT_URL . 'assets/css/settings/category-tab-style.css', [], $this->version);
                    break;
                case 'demo-data':
                    wp_enqueue_style('wp-equipment-demo-data-tab', WP_EQUIPMENT_URL . 'assets/css/settings/demo-data-tab-style.css', [], $this->version);
                    break;
            }
            return;
        }

        // Equipment and Licence pages styles
        if ($screen->id === 'toplevel_page_wp-equipment') {
            // Core styles
            wp_enqueue_style('wp-equipment-toast', WP_EQUIPMENT_URL . 'assets/css/components/toast.css', [], $this->version);
            wp_enqueue_style('wp-equipment-modal', WP_EQUIPMENT_URL . 'assets/css/components/confirmation-modal.css', [], $this->version);
            // Licence toast - terpisah
            wp_enqueue_style('licence-toast', WP_EQUIPMENT_URL . 'assets/css/licence/licence-toast.css', [], $this->version);

            // DataTables
            wp_enqueue_style('datatables', 'https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css', [], '1.13.7');

            // Equipment styles
            wp_enqueue_style('wp-equipment-equipment', WP_EQUIPMENT_URL . 'assets/css/equipment.css', [], $this->version);
            wp_enqueue_style('wp-equipment-equipment-form', WP_EQUIPMENT_URL . 'assets/css/equipment-form.css', [], $this->version);

            // Licence styles
            wp_enqueue_style('wp-equipment-licence', WP_EQUIPMENT_URL . 'assets/css/licence/licence.css', [], $this->version);
        }

        // In Equipment and Category pages styles
        if ($screen->id === 'toplevel_page_wp-equipment' || $screen->id === 'wp-equipment_page_wp-equipment-categories') {
            // Core styles - existing code...
            
            // Category styles
            wp_enqueue_style('wp-equipment-category', WP_EQUIPMENT_URL . 'assets/css/category/category-style.css', [], $this->version);
            wp_enqueue_style('wp-equipment-category-form', WP_EQUIPMENT_URL . 'assets/css/category/category-form.css', [], $this->version);
        }

    // In Equipment and Category pages styles
    if ($screen->id === 'wp-equipment_page_wp-equipment-categories') {
        // Core DataTables CSS
        wp_enqueue_style('datatables', 'https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css', [], '1.13.7');
        
        // Category styles
        wp_enqueue_style('wp-equipment-category', 
            WP_EQUIPMENT_URL . 'assets/css/category/category-style.css', 
            [], 
            $this->version
        );
        
        wp_enqueue_style('wp-equipment-category-form', 
            WP_EQUIPMENT_URL . 'assets/css/category/category-form.css', 
            [], 
            $this->version
        );
        
        // Custom DataTable Category CSS
        wp_enqueue_style('wp-equipment-datatable-category', 
            WP_EQUIPMENT_URL . 'assets/css/category/datatable-category.css',
            ['datatables'],
            $this->version
        );

        error_log('Category styles enqueued'); // Debugging
    }

    }

    public function enqueue_scripts() {
        $screen = get_current_screen();
        if (!$screen) return;

        // Settings page scripts
        if ($screen->id === 'wp-equipment_page_wp-equipment-settings') {
            wp_enqueue_script('wp-equipment-toast', WP_EQUIPMENT_URL . 'assets/js/components/toast.js', ['jquery'], $this->version, true);
            wp_enqueue_script('confirmation-modal', WP_EQUIPMENT_URL . 'assets/js/components/confirmation-modal.js', ['jquery'], $this->version, true);
            wp_enqueue_script('wp-equipment-settings', WP_EQUIPMENT_URL . 'assets/js/settings/settings-script.js', ['jquery', 'wp-equipment-toast'], $this->version, true);
            
            $current_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'general';
            switch ($current_tab) {
                case 'permission':
                    wp_enqueue_style('wp-equipment-permission-tab', WP_EQUIPMENT_URL . 'assets/js/settings/permission-tab-script.js', [], $this->version);
                    break;
                case 'general':
                    wp_enqueue_style('wp-equipment-general-tab', WP_EQUIPMENT_URL . 'assets/js/settings/general-tab-script.js', [], $this->version);
                    break;
                case 'demo-data':
                    wp_enqueue_script('wp-equipment-demo-data-tab', WP_EQUIPMENT_URL . 'assets/js/settings/demo-data-tab-script.js', ['jquery'], $this->version);
                    break;
            }
            return;

        }

        // Equipment and Licence pages scripts
        if ($screen->id === 'toplevel_page_wp-equipment') {
            // Core dependencies
            wp_enqueue_script('jquery-validate', 'https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js', ['jquery'], '1.19.5', true);
            wp_enqueue_script('datatables', 'https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js', ['jquery'], '1.13.7', true);

            // Components
            wp_enqueue_script('equipment-toast', WP_EQUIPMENT_URL . 'assets/js/components/equipment-toast.js', ['jquery'], $this->version, true);
            wp_enqueue_script('confirmation-modal', WP_EQUIPMENT_URL . 'assets/js/components/confirmation-modal.js', ['jquery'], $this->version, true);
            // Licence toast
            wp_enqueue_script('licence-toast', WP_EQUIPMENT_URL . 'assets/js/licence/licence-toast.js', ['jquery'], $this->version, true);

            // Equipment scripts - path fixed according to tree.md
            wp_enqueue_script('equipment-datatable', WP_EQUIPMENT_URL . 'assets/js/components/equipment-datatable.js', ['jquery', 'datatables', 'equipment-toast'], $this->version, true);
            wp_enqueue_script('create-equipment-form', WP_EQUIPMENT_URL . 'assets/js/components/create-equipment-form.js', ['jquery', 'jquery-validate', 'equipment-toast'], $this->version, true);
            wp_enqueue_script('edit-equipment-form', WP_EQUIPMENT_URL . 'assets/js/components/edit-equipment-form.js', ['jquery', 'jquery-validate', 'equipment-toast'], $this->version, true);

            wp_enqueue_script('equipment',
                WP_EQUIPMENT_URL . 'assets/js/equipment.js',
                [
                    'jquery',
                    'equipment-toast',
                    'equipment-datatable',
                    'create-equipment-form',
                    'edit-equipment-form'
                ],
                $this->version,
                true
            );

            // Gunakan wpEquipmentData untuk semua
            $equipment_nonce = wp_create_nonce('wp_equipment_nonce');
            wp_localize_script('equipment', 'wpEquipmentData', [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => $equipment_nonce,
                'debug' => true
            ]);


            // Licence scripts
            wp_enqueue_script('licence-datatable', WP_EQUIPMENT_URL . 'assets/js/licence/licence-datatable.js', ['jquery', 'datatables', 'equipment-toast', 'equipment'], $this->version, true);
            wp_enqueue_script('licence-toast', WP_EQUIPMENT_URL . 'assets/js/licence/licence-toast.js', ['jquery'], $this->version, true);
            // Update dependencies untuk form
            wp_enqueue_script('create-licence-form', WP_EQUIPMENT_URL . 'assets/js/licence/create-licence-form.js', ['jquery', 'jquery-validate', 'licence-toast', 'licence-datatable'], $this->version, true);
            wp_enqueue_script('edit-licence-form', WP_EQUIPMENT_URL . 'assets/js/licence/edit-licence-form.js', ['jquery', 'jquery-validate', 'licence-toast', 'licence-datatable'], $this->version, true);
        }
        
        if ($screen->id === 'wp-equipment_page_wp-equipment-categories') {
            // Core dependencies
            wp_enqueue_script('jquery-validate', 'https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js', ['jquery'], '1.19.5', true);

            wp_enqueue_script('category', 
                WP_EQUIPMENT_URL . 'assets/js/category/category-script.js', 
                ['jquery', 'equipment-toast', 'category-datatable', 'category-form', 'confirmation-modal'], 
                $this->version, 
                true
            );
            wp_enqueue_script('datatables', 'https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js', ['jquery'], '1.13.7', true);

            // Components
            wp_enqueue_script('equipment-toast', WP_EQUIPMENT_URL . 'assets/js/components/equipment-toast.js', ['jquery'], $this->version, true);
            
            // Category scripts
            wp_enqueue_script('category-form', 
                WP_EQUIPMENT_URL . 'assets/js/category/category-form.js', 
                ['jquery', 'jquery-validate', 'equipment-toast'], 
                $this->version, 
                true
            );

            wp_enqueue_script('confirmation-modal', WP_EQUIPMENT_URL . 'assets/js/components/confirmation-modal.js', ['jquery'], $this->version, true);
            wp_enqueue_script('category-datatable', 
                WP_EQUIPMENT_URL . 'assets/js/category/category-datatable.js', 
                ['jquery', 'datatables', 'equipment-toast', 'category-form', 'confirmation-modal'],
                $this->version, 
                true
            );

            wp_enqueue_script('category', 
                WP_EQUIPMENT_URL . 'assets/js/category/category-script.js', 
                ['jquery', 'equipment-toast', 'category-datatable', 'category-form'], 
                $this->version, 
                true
            );

            // Localize script
            wp_localize_script('category-datatable', 'wpEquipmentData', [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('wp_equipment_nonce'),
                'texts' => [
                    'loading' => __('Loading...', 'wp-equipment'),
                    'error' => __('Error occurred', 'wp-equipment'),
                    'success' => __('Success', 'wp-equipment')
                ]
            ]);
        }


    }

    public function enqueue_select_handler() {
        // Cek apakah sudah di-enqueue sebelumnya
        if (wp_script_is('wp-equipment-select-handler', 'enqueued')) {
            return;
        }

        wp_enqueue_script('wp-equipment-select-handler', 
            WP_EQUIPMENT_URL . 'assets/js/components/select-handler.js', 
            ['jquery'], 
            $this->version, 
            true
        );

        wp_localize_script('wp-equipment-select-handler', 'wpEquipmentSelectData', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wp_equipment_nonce'),
            'texts' => [
                'select_equipment' => __('Pilih Equipment', 'wp-equipment'),
                'select_licence' => __('Pilih Licence', 'wp-equipment'),
                'loading' => __('Memuat...', 'wp-equipment')
            ]
        ]);
    }

}
