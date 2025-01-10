<?php
/**
* Equipment Controller Class
*
* @package     WP_Equipment
* @subpackage  Controllers
* @version     1.0.0
* @author      arisciwek
*
* Path: /wp-equipment/src/Controllers/EquipmentController.php
*
* Description: Controller untuk mengelola data equipment.
*              Menangani operasi CRUD dengan integrasi cache.
*              Includes validasi input, permission checks,
*              dan response formatting untuk panel kanan.
*              Menyediakan endpoints untuk DataTables server-side.
*
* Changelog:
* 1.0.1 - 2024-12-08
* - Added view_own_equipment permission check in show method
* - Enhanced permission validation
* - Improved error handling for permission checks
*
* Changelog:
* 1.0.0 - 2024-12-03 14:30:00
* - Refactor CRUD responses untuk panel kanan
* - Added cache integration di semua endpoints
* - Added konsisten response format
* - Added validasi dan permission di semua endpoints
* - Improved error handling dan feedback
*/

namespace WPEquipment\Controllers;

use WPEquipment\Models\EquipmentModel;
use WPEquipment\Models\Branch\LicenceModel;
use WPEquipment\Validators\EquipmentValidator;
use WPEquipment\Cache\CacheManager;

class EquipmentController {
    private EquipmentModel $model;
    private EquipmentValidator $validator;
    private CacheManager $cache;
    private LicenceModel $licenceModel;  // Tambahkan ini

    private string $log_file;

    /**
     * Default log file path
     */
    private const DEFAULT_LOG_FILE = 'logs/equipment.log';

    public function __construct() {
        $this->model = new EquipmentModel();
        $this->licenceModel = new LicenceModel();  // Inisialisasi di constructor
        $this->validator = new EquipmentValidator();
        $this->cache = new CacheManager();

        // Inisialisasi log file di dalam direktori plugin
        $this->log_file = WP_EQUIPMENT_PATH . self::DEFAULT_LOG_FILE;

        // Pastikan direktori logs ada
        $this->initLogDirectory();

        // Register AJAX handlers
        add_action('wp_ajax_handle_equipment_datatable', [$this, 'handleDataTableRequest']);
        add_action('wp_ajax_nopriv_handle_equipment_datatable', [$this, 'handleDataTableRequest']);

        // Register endpoint untuk update
        add_action('wp_ajax_update_equipment', [$this, 'update']);

        // Register endpoint lain yang diperlukan
        add_action('wp_ajax_get_equipment', [$this, 'show']);
        add_action('wp_ajax_create_equipment', [$this, 'store']);
        add_action('wp_ajax_delete_equipment', [$this, 'delete']);

    }

    /**
     * Initialize log directory if it doesn't exist
     */
    private function initLogDirectory(): void {
        // Get WordPress uploads directory information
        $upload_dir = wp_upload_dir();
        $equipment_base_dir = $upload_dir['basedir'] . '/wp-equipment';
        $equipment_log_dir = $equipment_base_dir . '/logs';
        
        // Update log file path with monthly rotation format
        $this->log_file = $equipment_log_dir . '/equipment-' . date('Y-m') . '.log';

        // Create base wp-equipment directory if it doesn't exist
        if (!file_exists($equipment_base_dir)) {
            if (!wp_mkdir_p($equipment_base_dir)) {
                $this->log_file = rtrim(sys_get_temp_dir(), '/') . '/wp-equipment.log';
                error_log('Failed to create base directory in uploads: ' . $equipment_base_dir);
                return;
            }
            
            // Add .htaccess to base directory
            $base_htaccess_content = "# Protect Directory\n";
            $base_htaccess_content .= "<FilesMatch \"^.*$\">\n";
            $base_htaccess_content .= "Order Deny,Allow\n";
            $base_htaccess_content .= "Deny from all\n";
            $base_htaccess_content .= "</FilesMatch>\n";
            $base_htaccess_content .= "\n";
            $base_htaccess_content .= "# Allow specific file types if needed\n";
            $base_htaccess_content .= "<FilesMatch \"\.(jpg|jpeg|png|gif|css|js)$\">\n";
            $base_htaccess_content .= "Order Allow,Deny\n";
            $base_htaccess_content .= "Allow from all\n";
            $base_htaccess_content .= "</FilesMatch>";
            
            @file_put_contents($equipment_base_dir . '/.htaccess', $base_htaccess_content);
            @chmod($equipment_base_dir, 0755);
        }

        // Create logs directory if it doesn't exist
        if (!file_exists($equipment_log_dir)) {
            if (!wp_mkdir_p($equipment_log_dir)) {
                $this->log_file = rtrim(sys_get_temp_dir(), '/') . '/wp-equipment.log';
                error_log('Failed to create log directory in uploads: ' . $equipment_log_dir);
                return;
            }

            // Add .htaccess to logs directory with strict rules
            $logs_htaccess_content = "# Deny access to all files\n";
            $logs_htaccess_content .= "Order deny,allow\n";
            $logs_htaccess_content .= "Deny from all\n\n";
            $logs_htaccess_content .= "# Deny access to log files specifically\n";
            $logs_htaccess_content .= "<Files ~ \"\.log$\">\n";
            $logs_htaccess_content .= "Order allow,deny\n";
            $logs_htaccess_content .= "Deny from all\n";
            $logs_htaccess_content .= "</Files>\n\n";
            $logs_htaccess_content .= "# Extra protection\n";
            $logs_htaccess_content .= "<IfModule mod_php.c>\n";
            $logs_htaccess_content .= "php_flag engine off\n";
            $logs_htaccess_content .= "</IfModule>";
            
            @file_put_contents($equipment_log_dir . '/.htaccess', $logs_htaccess_content);
            @chmod($equipment_log_dir, 0755);
        }

        // Create log file if it doesn't exist
        if (!file_exists($this->log_file)) {
            if (@touch($this->log_file)) {
                chmod($this->log_file, 0644);
            } else {
                error_log('Failed to create log file: ' . $this->log_file);
                $this->log_file = rtrim(sys_get_temp_dir(), '/') . '/wp-equipment.log';
                return;
            }
        }

        // Double check writability
        if (!is_writable($this->log_file)) {
            error_log('Log file not writable: ' . $this->log_file);
            $this->log_file = rtrim(sys_get_temp_dir(), '/') . '/wp-equipment.log';
        }
    }

    /**
     * Log debug messages ke file
     *
     * @param mixed $message Pesan yang akan dilog
     * @return void
     */
    private function debug_log($message): void {
        // Hanya jalankan jika WP_DEBUG aktif
        if (!defined('WP_DEBUG') || !WP_DEBUG) {
            return;
        }

        $timestamp = current_time('mysql');

        if (is_array($message) || is_object($message)) {
            $message = print_r($message, true);
        }

        $log_message = "[{$timestamp}] {$message}\n";

        // Gunakan error_log bawaan WordPress dengan custom log file
        error_log($log_message, 3, $this->log_file);
    }

    public function handleDataTableRequest() {
        try {
            // Verify nonce
            if (!check_ajax_referer('wp_equipment_nonce', 'nonce', false)) {
                throw new \Exception('Security check failed');
            }

            // Get and validate parameters
            $draw = isset($_POST['draw']) ? intval($_POST['draw']) : 1;
            $start = isset($_POST['start']) ? intval($_POST['start']) : 0;
            $length = isset($_POST['length']) ? intval($_POST['length']) : 10;
            $search = isset($_POST['search']['value']) ? sanitize_text_field($_POST['search']['value']) : '';

            // Get order parameters
            $orderColumn = isset($_POST['order'][0]['column']) ? intval($_POST['order'][0]['column']) : 0;
            $orderDir = isset($_POST['order'][0]['dir']) ? sanitize_text_field($_POST['order'][0]['dir']) : 'asc';

            // Map column index to column name
            $columns = ['code', 'name', 'owner_name', 'licence_count', 'actions']; // tambah owner_name

            $orderBy = isset($columns[$orderColumn]) ? $columns[$orderColumn] : 'code';

            if ($orderBy === 'actions') {
                $orderBy = 'code'; // Default sort jika kolom actions
            }

            try {
                $result = $this->model->getDataTableData($start, $length, $search, $orderBy, $orderDir);

                if (!$result) {
                    throw new \Exception('No data returned from model');
                }

                $data = [];
                foreach ($result['data'] as $equipment) {
                    $data[] = [
                        'id' => $equipment->id,
                        'code' => esc_html($equipment->code),
                        'name' => esc_html($equipment->name),
                        'owner_name' => esc_html($equipment->owner_name ?? '-'), // tambah owner_name
                        'licence_count' => intval($equipment->licence_count),
                        'actions' => $this->generateActionButtons($equipment)
                    ];
                }

                $response = [
                    'draw' => $draw,
                    'recordsTotal' => $result['total'],
                    'recordsFiltered' => $result['filtered'],
                    'data' => $data,
                ];

                wp_send_json($response);

            } catch (\Exception $modelException) {
                throw new \Exception('Database error: ' . $modelException->getMessage());
            }

        } catch (\Exception $e) {
            wp_send_json_error([
                'message' => $e->getMessage(),
                'code' => $e->getCode()
            ], 400);
        }
    }

    private function generateActionButtons($equipment) {
        $actions = '';

        if (current_user_can('view_equipment_detail')) {
            $actions .= sprintf(
                '<button type="button" class="button view-equipment" data-id="%d" title="%s"><i class="dashicons dashicons-visibility"></i></button> ',
                $equipment->id,
                __('Lihat', 'wp-equipment')
            );
        }

        if (current_user_can('edit_all_equipments') ||
            (current_user_can('edit_own_equipment') && $equipment->created_by === get_current_user_id())) {
            $actions .= sprintf(
                '<button type="button" class="button edit-equipment" data-id="%d" title="%s"><i class="dashicons dashicons-edit"></i></button> ',
                $equipment->id,
                __('Edit', 'wp-equipment')
            );
        }

        if (current_user_can('delete_equipment')) {
            $actions .= sprintf(
                '<button type="button" class="button delete-equipment" data-id="%d" title="%s"><i class="dashicons dashicons-trash"></i></button>',
                $equipment->id,
                __('Hapus', 'wp-equipment')
            );
        }

        return $actions;
    }

    public function store() {
        try {
            check_ajax_referer('wp_equipment_nonce', 'nonce');

            if (!current_user_can('create_equipment')) {
                wp_send_json_error([
                    'message' => __('Insufficient permissions', 'wp-equipment')
                ]);
                return;
            }

            $current_user_id = get_current_user_id();
            
            // Debug POST data - hanya log data yang kita perlukan
            $debug_post = [
                'name' => $_POST['name'] ?? 'not set',
                'code' => $_POST['code'] ?? 'not set',
                'user_id' => $_POST['user_id'] ?? 'not set',
            ];
            $this->debug_log('Relevant POST data:');
            $this->debug_log($debug_post);
            
            // Basic data
            $data = [
                'name' => sanitize_text_field($_POST['name']),
                'code' => sanitize_text_field($_POST['code']),
                'created_by' => $current_user_id
            ];

            // Handle user_id
            if (isset($_POST['user_id']) && !empty($_POST['user_id'])) {
                $data['user_id'] = absint($_POST['user_id']);
            } else {
                $data['user_id'] = $current_user_id;
            }

            // Debug final data
            $this->debug_log('Data to be saved:');
            $this->debug_log($data);

            // Validate input
            $errors = $this->validator->validateCreate($data);
            if (!empty($errors)) {
                wp_send_json_error([
                    'message' => is_array($errors) ? implode(', ', $errors) : $errors,
                    'errors' => $errors
                ]);
                return;
            }

            // Get ID from creation
            $id = $this->model->create($data);
            if (!$id) {
                wp_send_json_error([
                    'message' => __('Failed to create equipment', 'wp-equipment')
                ]);
                return;
            }

            // Get fresh data for response
            $equipment = $this->model->find($id);
            if (!$equipment) {
                wp_send_json_error([
                    'message' => __('Failed to retrieve created equipment', 'wp-equipment')
                ]);
                return;
            }

            wp_send_json_success([
                'id' => $id,
                'equipment' => $equipment,
                'licence_count' => 0,
                'message' => __('Equipment created successfully', 'wp-equipment')
            ]);

        } catch (\Exception $e) {
            wp_send_json_error([
                'message' => $e->getMessage() ?: 'Terjadi kesalahan saat menambah equipment',
                'error_details' => WP_DEBUG ? $e->getTraceAsString() : null
            ]);
        }
    }

    public function update() {
        try {
            check_ajax_referer('wp_equipment_nonce', 'nonce');

            $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
            if (!$id) {
                throw new \Exception('Invalid equipment ID');
            }

            // Get existing equipment data
            $existing_equipment = $this->model->find($id);
            if (!$existing_equipment) {
                throw new \Exception('Equipment not found');
            }

            // Check permissions
            if (!current_user_can('edit_all_equipments') && 
                (!current_user_can('edit_own_equipment') || $existing_equipment->created_by !== get_current_user_id())) {
                wp_send_json_error([
                    'message' => __('You do not have permission to edit this equipment', 'wp-equipment')
                ]);
                return;
            }

            // Basic data
            $data = [
                'name' => sanitize_text_field($_POST['name']),
                'code' => sanitize_text_field($_POST['code'])
            ];

            $this->debug_log('POST: ' . print_r($_POST, true));

            // Handle user_id
            if (isset($_POST['user_id'])) {
                if (current_user_can('edit_all_equipments')) {
                    $data['user_id'] = !empty($_POST['user_id']) ? intval($_POST['user_id']) : null;
                    $this->debug_log('Setting user_id to: ' . print_r($data['user_id'], true));
                } else {
                    $this->debug_log('User lacks permission to change user_id');
                }
            }

            // If no edit_all_equipments capability, user_id remains unchanged

            // Validate input
            $errors = $this->validator->validateUpdate($data, $id);
            if (!empty($errors)) {
                wp_send_json_error(['message' => implode(', ', $errors)]);
                return;
            }

            // Update data
            $updated = $this->model->update($id, $data);
            if (!$updated) {
                throw new \Exception('Failed to update equipment');
            }

            // Get updated data
            $equipment = $this->model->find($id);
            if (!$equipment) {
                throw new \Exception('Failed to retrieve updated equipment');
            }

            wp_send_json_success([
                'message' => __('Equipment updated successfully', 'wp-equipment'),
                'data' => [
                    'equipment' => $equipment,
                    'licence_count' => $this->model->getBranchCount($id)
                ]
            ]);

        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    public function show() {
        try {
            check_ajax_referer('wp_equipment_nonce', 'nonce');

            $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
            if (!$id) {
                throw new \Exception('Invalid equipment ID');
            }

            $equipment = $this->model->find($id);
            if (!$equipment) {
                throw new \Exception('Equipment not found');
            }

            // Add user permission check
            if (!current_user_can('view_equipment_detail') && 
                (!current_user_can('view_own_equipment') || $equipment->user_id !== get_current_user_id())) {
                throw new \Exception('You do not have permission to view this equipment');
            }

            // Add owner information to response
            if ($equipment->user_id) {
                $user = get_userdata($equipment->user_id);
                if ($user) {
                    $equipment->owner_name = $user->display_name;
                }
            }

            wp_send_json_success([
                'equipment' => $equipment,
                'licence_count' => $this->model->getBranchCount($id)
            ]);

        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    public function delete() {
        try {
            check_ajax_referer('wp_equipment_nonce', 'nonce');

            $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
            if (!$id) {
                throw new \Exception('Invalid equipment ID');
            }

            // Validate delete operation
            $errors = $this->validator->validateDelete($id);
            if (!empty($errors)) {
                throw new \Exception(reset($errors));
            }

            // Perform delete
            if (!$this->model->delete($id)) {
                throw new \Exception('Failed to delete equipment');
            }

            // Clear cache
            $this->cache->invalidateEquipmentCache($id);

            wp_send_json_success([
                'message' => __('Data Peralatan berhasil dihapus', 'wp-equipment')
            ]);

        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    public function getStats() {
        try {
            check_ajax_referer('wp_equipment_nonce', 'nonce');

            if (!current_user_can('view_equipment_list')) {
                wp_send_json_error([
                    'message' => __('Insufficient permissions', 'wp-equipment')
                ]);
                return;
            }

            $stats = [
                'total_equipments' => $this->model->getTotalCount(),
                'total_licencees' => $this->licenceModel->getTotalCount()
            ];

            wp_send_json_success($stats);

        } catch (\Exception $e) {
            wp_send_json_error([
                'message' => $e->getMessage()
            ]);
        }
    }


}
