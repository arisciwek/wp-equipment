<?php
/**
 * Licence Controller Class
 *
 * @package     WP_Equipment
 * @subpackage  Controllers/Licence
 * @version     1.0.0
 * @author      arisciwek
 *
 * Path: /wp-equipment/src/Controllers/Licence/LicenceController.php
 *
 * Description: Controller untuk mengelola data surat keterangan.
 *              Menangani operasi CRUD dengan integrasi cache.
 *              Includes validasi input, permission checks,
 *              dan response formatting untuk DataTables.
 *
 * Changelog:
 * 1.0.0 - 2024-12-10
 * - Initial implementation
 * - Added CRUD endpoints
 * - Added DataTables integration
 * - Added permission checks
 * - Added cache support
 */

namespace WPEquipment\Controllers\Licence;

use WPEquipment\Models\Licence\LicenceModel;
use WPEquipment\Validators\Licence\LicenceValidator;
use WPEquipment\Cache\EquipmentCacheManager;

class LicenceController {
    private LicenceModel $model;
    private LicenceValidator $validator;
    private EquipmentCacheManager $cache;
    private string $log_file;

    /**
     * Default log file path
     */
    private const DEFAULT_LOG_FILE = 'logs/licence.log';

    public function __construct() {
        $this->model = new LicenceModel();
        $this->validator = new LicenceValidator();
        $this->cache = new EquipmentCacheManager();

        // Initialize log file inside plugin directory
        $this->log_file = WP_EQUIPMENT_PATH . self::DEFAULT_LOG_FILE;

        // Ensure logs directory exists
        $this->initLogDirectory();

        // Register AJAX handlers
        add_action('wp_ajax_handle_licence_datatable', [$this, 'handleDataTableRequest']);
        add_action('wp_ajax_nopriv_handle_licence_datatable', [$this, 'handleDataTableRequest']);

        // Register other endpoints
        add_action('wp_ajax_get_licence', [$this, 'show']);
        add_action('wp_ajax_create_licence', [$this, 'store']);
        add_action('wp_ajax_update_licence', [$this, 'update']);
        add_action('wp_ajax_delete_licence', [$this, 'delete']);
    }

    /**
     * Initialize log directory if it doesn't exist
     */
    private function initLogDirectory(): void {
        // Gunakan wp_upload_dir() untuk mendapatkan writable directory
        $upload_dir = wp_upload_dir();
        $plugin_log_dir = $upload_dir['basedir'] . '/wp-equipment/logs';
        
        // Update log file path dengan format yang lebih informatif
        $this->log_file = $plugin_log_dir . '/equipment-' . date('Y-m') . '.log';

        // Buat direktori jika belum ada
        if (!file_exists($plugin_log_dir)) {
            if (!wp_mkdir_p($plugin_log_dir)) {
                // Jika gagal, gunakan sys_get_temp_dir sebagai fallback
                $this->log_file = rtrim(sys_get_temp_dir(), '/') . '/wp-equipment.log';
                error_log('Failed to create log directory in uploads: ' . $plugin_log_dir);
                return;
            }

            // Protect directory dengan .htaccess
            file_put_contents($plugin_log_dir . '/.htaccess', 'deny from all');
            chmod($plugin_log_dir, 0755);
        }

        // Buat file log jika belum ada
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
     * Log debug messages to file
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

        // Coba tulis ke file
        $written = @error_log($log_message, 3, $this->log_file);
        
        // Jika gagal, log ke default WordPress debug log
        if (!$written) {
            error_log('WP Equipment Plugin: ' . $log_message);
        }
    }
    
    public function handleDataTableRequest() {
        try {
            // Verify nonce
            if (!check_ajax_referer('wp_equipment_nonce', 'nonce', false)) {
                throw new \Exception('Security check failed');
            }

            // Get and validate equipment_id
            $equipment_id = isset($_POST['equipment_id']) ? intval($_POST['equipment_id']) : 0;
            if (!$equipment_id) {
                throw new \Exception('Invalid equipment ID');
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
            $columns = ['name', 'type', 'actions'];
            $orderBy = isset($columns[$orderColumn]) ? $columns[$orderColumn] : 'name';

            if ($orderBy === 'actions') {
                $orderBy = 'name'; // Default sort if actions column
            }

            try {
                $result = $this->model->getDataTableData(
                    $equipment_id,
                    $start,
                    $length,
                    $search,
                    $orderBy,
                    $orderDir
                );

                if (!$result) {
                    throw new \Exception('No data returned from model');
                }

                $data = [];
                foreach ($result['data'] as $licence) {
                    $data[] = [
                        'id' => $licence->id,
                        'code' => esc_html($licence->code),
                        'name' => esc_html($licence->name),
                        'type' => esc_html($licence->type),
                        'equipment_name' => esc_html($licence->equipment_name),
                        'actions' => $this->generateActionButtons($licence)
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

    private function generateActionButtons($licence) {
        $actions = '';

        if (current_user_can('view_licence_detail')) {
            $actions .= sprintf(
                '<button type="button" class="button view-licence" data-id="%d" title="%s">' .
                '<i class="dashicons dashicons-visibility"></i></button> ',
                $licence->id,
                __('Lihat', 'wp-equipment')
            );
        }

        if (current_user_can('edit_all_licencees') ||
            (current_user_can('edit_own_licence') && $licence->created_by === get_current_user_id())) {
            $actions .= sprintf(
                '<button type="button" class="button edit-licence" data-id="%d" title="%s">' .
                '<i class="dashicons dashicons-edit"></i></button> ',
                $licence->id,
                __('Edit', 'wp-equipment')
            );
        }

        if (current_user_can('delete_licence')) {
            $actions .= sprintf(
                '<button type="button" class="button delete-licence" data-id="%d" title="%s">' .
                '<i class="dashicons dashicons-trash"></i></button>',
                $licence->id,
                __('Hapus', 'wp-equipment')
            );
        }

        return $actions;
    }

    public function store() {
        try {
            check_ajax_referer('wp_equipment_nonce', 'nonce');

            if (!current_user_can('add_licence')) {
                wp_send_json_error([
                    'message' => __('Insufficient permissions', 'wp-equipment')
                ]);
                return;
            }

            $data = [
                'equipment_id' => intval($_POST['equipment_id']),
                'code' => sanitize_text_field($_POST['code']),
                'name' => sanitize_text_field($_POST['name']),
                'type' => sanitize_text_field($_POST['type']),
                'created_by' => get_current_user_id()
            ];

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
                $this->debug_log('Failed to create licence');
                wp_send_json_error([
                    'message' => __('Failed to create licence', 'wp-equipment')
                ]);
                return;
            }

            $this->debug_log('Licence created with ID: ' . $id);

            // Get fresh data for response
            $licence = $this->model->find($id);
            if (!$licence) {
                $this->debug_log('Failed to retrieve created licence');
                wp_send_json_error([
                    'message' => __('Failed to retrieve created licence', 'wp-equipment')
                ]);
                return;
            }

            wp_send_json_success([
                'message' => __('Licence created successfully', 'wp-equipment'),
                'licence' => $licence
            ]);

        } catch (\Exception $e) {
            $this->debug_log('Store error: ' . $e->getMessage());
            $this->debug_log('Stack trace: ' . $e->getTraceAsString());
            wp_send_json_error([
                'message' => $e->getMessage() ?: __('Failed to add licence', 'wp-equipment'),
                'error_details' => WP_DEBUG ? $e->getTraceAsString() : null
            ]);
        }
    }

    public function update() {
        try {
            check_ajax_referer('wp_equipment_nonce', 'nonce');

            $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
            if (!$id) {
                throw new \Exception('Invalid licence ID');
            }

            // Validate input
            $data = [
                'name' => sanitize_text_field($_POST['name']),
                'type' => sanitize_text_field($_POST['type'])
            ];

            $errors = $this->validator->validateUpdate($data, $id);
            if (!empty($errors)) {
                wp_send_json_error(['message' => implode(', ', $errors)]);
                return;
            }

            // Update data
            $updated = $this->model->update($id, $data);
            if (!$updated) {
                throw new \Exception('Failed to update licence');
            }

            // Get updated data
            $licence = $this->model->find($id);
            if (!$licence) {
                throw new \Exception('Failed to retrieve updated licence');
            }

            wp_send_json_success([
                'message' => __('Licence updated successfully', 'wp-equipment'),
                'licence' => $licence
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
                throw new \Exception('Invalid licence ID');
            }

            $licence = $this->model->find($id);
            if (!$licence) {
                throw new \Exception('Licence not found');
            }

            // Add permission check
            if (!current_user_can('view_licence_detail') &&
                (!current_user_can('view_own_licence') || $licence->created_by !== get_current_user_id())) {
                throw new \Exception('You do not have permission to view this licence');
            }

            wp_send_json_success([
                'licence' => $licence
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
                throw new \Exception('Invalid licence ID');
            }

            // Validate delete operation
            $errors = $this->validator->validateDelete($id);
            if (!empty($errors)) {
                throw new \Exception(reset($errors));
            }

            // Perform delete
            if (!$this->model->delete($id)) {
                throw new \Exception('Failed to delete licence');
            }

            wp_send_json_success([
                'message' => __('Data Pertama / berkala berhasil dihapus', 'wp-equipment')
            ]);

        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }
}
