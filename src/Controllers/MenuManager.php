<?php
/**
 * File: MenuManager.php
 * Path: /wp-equipment/src/Controllers/MenuManager.php
 * 
 * @package     WP_Equipment
 * @subpackage  Admin/Controllers
 * @version     1.0.1
 * @author      arisciwek
 */

namespace WPEquipment\Controllers;

use WPEquipment\Controllers\SettingsController;

class MenuManager {
    private $plugin_name;
    private $version;
    private $settings_controller;

    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->settings_controller = new SettingsController();
    }

    public function init() {
        add_action('admin_menu', [$this, 'registerMenus']);
        $this->settings_controller->init();
    }

    public function registerMenus() {
        add_menu_page(
            __('WP Equipment', 'wp-equipment'),
            __('WP Equipment', 'wp-equipment'),
            'manage_options',
            'wp-equipment',
            [$this, 'renderMainPage'],
            'dashicons-location',
            30
        );

        add_submenu_page(
            'wp-equipment',
            __('Pengaturan', 'wp-equipment'),
            __('Pengaturan', 'wp-equipment'),
            'manage_options',
            'wp-equipment-settings',
            [$this->settings_controller, 'renderPage']
        );
    }

    public function renderMainPage() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Anda tidak memiliki izin untuk mengakses halaman ini.', 'wp-equipment'));
        }

        require_once WP_EQUIPMENT_PATH . 'src/Views/templates/equipment-dashboard.php';
    }
}
