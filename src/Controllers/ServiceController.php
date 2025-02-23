<?php
/**
 * Service Controller Class
 *
 * @package     WP_Equipment
 * @subpackage  Controllers
 * @version     1.0.0
 * @author      arisciwek
 *
 * Path: /wp-equipment/src/Controllers/ServiceController.php
 *
 * Description: Controller untuk mengelola data sektor.
 *              Menangani operasi CRUD dengan cache management.
 *              Includes validasi input, permission checks,
 *              dan response formatting untuk panel kanan.
 *              Menyediakan endpoints untuk DataTables server-side.
 */

namespace WPEquipment\Controllers;

use WPEquipment\Models\ServiceModel;
use WPEquipment\Validators\ServiceValidator;
use WPEquipment\Cache\EquipmentCacheManager;

class ServiceController {
    private ServiceModel $model;
    private ServiceValidator $validator;
    private EquipmentCacheManager $cache;
    private string $log_file;

    private const DEFAULT_LOG_FILE = 'logs/service.log';

    public function __construct() {
        $this->model = new ServiceModel();
        $this->validator = new ServiceValidator();
        $this->cache = new EquipmentCacheManager();

        // Initialize log file
        $this->log_file = WP_EQUIPMENT_PATH . self::DEFAULT_LOG_FILE;
        $this->initLogDirectory();

        // Register AJAX handlers
        add_action('wp_ajax_handle_service_datatable', [$this, 'handleDataTableRequest']);
        add_action('wp_ajax_get_service', [$this, 'show']);
        add_action('wp_ajax_create_service', [$this, 'store']);
        add_action('wp_ajax_update_service', [$this, 'update']);
        add_action('wp_ajax_delete_service', [$this, 'delete']);
        add_action('wp_ajax_get_service_stats', [$this, 'getServiceStats']);
        add_action('wp_ajax_create_service_button', [$this, 'createServiceButton']);
    }

    private function initLogDirectory(): void {
        $upload_dir = wp_upload_dir();
        $service_base_dir = $upload_dir['basedir'] . '/wp-equipment';
        $service_log_dir = $service_base_dir . '/logs';
        
        $this->log_file = $service_log_dir . '/service-' . date('Y-m') . '.log';

        if (!file_exists($service_base_dir)) {
            wp_mkdir_p($service_base_dir);
            $htaccess_content = "Order deny,allow\nDeny from all";
            @file_put_contents($service_base_dir . '/.htaccess', $htaccess_content);
        }

        if (!file_exists($service_log_dir)) {
            wp_mkdir_p($service_log_dir);
            @file_put_contents($service_log_dir . '/.htaccess', $htaccess_content);
        }
    }

    private function debug_log($message): void {
        if (!defined('WP_DEBUG') || !WP_DEBUG) {
            return;
        }

        $timestamp = current_time('mysql');
        if (is_array($message) || is_object($message)) {
            $message = print_r($message, true);
        }

        error_log("[{$timestamp}] {$message}\n", 3, $this->log_file);
    }

    public function createServiceButton() {
        try {
            check_ajax_referer('wp_equipment_nonce', 'nonce');
            
            if (!current_user_can('manage_options')) {
                wp_send_json_success(['button' => '']);
                return;
            }

            $button = '<button type="button" class="button button-primary" id="add-service-btn">';
            $button .= '<span class="dashicons dashicons-plus-alt"></span>';
            $button .= __('Tambah Bidang Jasa', 'wp-equipment');
            $button .= '</button>';

            wp_send_json_success(['button' => $button]);

        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    public function handleDataTableRequest() {
        try {
            // Debug incoming request
            error_log('Service DataTable Request: ' . print_r($_POST, true));
            
            // Debug nonce
            error_log('Nonce from request: ' . $_POST['nonce']);
            error_log('Expected nonce: ' . wp_create_nonce('wp_equipment_nonce'));

            // Cek nonce validation
            $nonce_check = check_ajax_referer('wp_equipment_nonce', 'nonce', false);
            error_log('Nonce check result: ' . ($nonce_check ? 'valid' : 'invalid'));

            if (!$nonce_check) {
                wp_send_json_error([
                    'message' => 'Invalid nonce',
                    'debug' => [
                        'provided_nonce' => $_POST['nonce'],
                        'expected_nonce' => wp_create_nonce('wp_equipment_nonce')
                    ]
                ]);
                return;
            }       

            check_ajax_referer('wp_equipment_nonce', 'nonce');
    
            if (!current_user_can('manage_options')) {
                throw new \Exception('Insufficient permissions');
            }
    
            $draw = isset($_POST['draw']) ? intval($_POST['draw']) : 1;
            $start = isset($_POST['start']) ? intval($_POST['start']) : 0;
            $length = isset($_POST['length']) ? intval($_POST['length']) : 10;
            $search = isset($_POST['search']['value']) ? sanitize_text_field($_POST['search']['value']) : '';
    
            $orderColumn = isset($_POST['order'][0]['column']) ? intval($_POST['order'][0]['column']) : 0;
            $orderDir = isset($_POST['order'][0]['dir']) ? sanitize_text_field($_POST['order'][0]['dir']) : 'asc';
    
            $columns = ['singkatan', 'nama', 'keterangan', 'status', 'actions'];
            $orderBy = isset($columns[$orderColumn]) ? $columns[$orderColumn] : 'nama';
    
            if ($orderBy === 'actions') {
                $orderBy = 'nama';
            }
    
            try {

                // 3. Check cache
                $cached_result = $this->cache->getDataTableCache(
                    'service_list',
                    get_current_user_id(),
                    $start, 
                    $length,
                    $search,
                    $orderBy,
                    $orderDir,
                    $response
                );

                if ($cached_result !== null) {
                    $this->debug_log('Returning cached data');
                    wp_send_json($cached_result);
                    return;
                }

                $result = $this->model->getDataTableData($start, $length, $search, $orderBy, $orderDir);
    
                error_log('Query result: ' . print_r($result, true));

                if (!$result) {
                    throw new \Exception('No data returned from model');
                }
    
                $data = [];
                if (!empty($result['data'])) {
                    foreach ($result['data'] as $service) {
                        $data[] = [
                            'id' => $service->id,
                            'singkatan' => esc_html($service->singkatan),
                            'nama' => esc_html($service->nama),
                            'keterangan' => $service->keterangan ? esc_html($service->keterangan) : '-',
                            'status' => esc_html($service->status),
                            'actions' => $this->generateActionButtons($service)
                        ];
                    }
                }
    
                $response = [
                    'draw' => $draw,
                    'recordsTotal' => intval($result['total']),
                    'recordsFiltered' => intval($result['filtered']), 
                    'data' => $data
                ];

                $this->cache->setDataTableCache(
                    'service_list',
                    get_current_user_id(),
                    $start,
                    $length,
                    $search,
                    $orderBy,
                    $orderDir,
                    $response
                );
    
                wp_send_json($response);
    
            } catch (\Exception $e) {
                throw new \Exception('Database error: ' . $e->getMessage());
            }
    
        } catch (\Exception $e) {
            wp_send_json_error([
                'message' => $e->getMessage()
            ]);
        }
    }

    private function generateActionButtons($service): string {
        if (!current_user_can('manage_options')) {
            return '';
        }

        $actions = sprintf(
            '<button type="button" class="button view-service" data-id="%d" title="%s">
                <i class="dashicons dashicons-visibility"></i>
            </button> ',
            $service->id,
            __('View', 'wp-equipment')
        );

        $actions .= sprintf(
            '<button type="button" class="button edit-service" data-id="%d" title="%s">
                <i class="dashicons dashicons-edit"></i>
            </button> ',
            $service->id,
            __('Edit', 'wp-equipment')
        );

        if (!$this->model->hasGroups($service->id)) {
            $actions .= sprintf(
                '<button type="button" class="button delete-service" data-id="%d" title="%s">
                    <i class="dashicons dashicons-trash"></i>
                </button>',
                $service->id,
                __('Delete', 'wp-equipment')
            );
        }

        return $actions;
    }

    public function show() {
        try {
            check_ajax_referer('wp_equipment_nonce', 'nonce');

            if (!current_user_can('manage_options')) {
                throw new \Exception('Insufficient permissions');
            }

            $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
            if (!$id) {
                throw new \Exception('Invalid service ID');
            }

            // Try to get from cache first
            $service = $this->cache->get('service_detail', $id);
            
            if ($service === null) {
                $service = $this->model->find($id);
                if (!$service) {
                    throw new \Exception('Service not found');
                }
                $this->cache->set('service_detail', $service, 300, $id);
            }

            // Format timestamps
            $service->created_at = mysql2date('Y-m-d H:i:s', $service->created_at);
            $service->updated_at = mysql2date('Y-m-d H:i:s', $service->updated_at);

            // Get creator info
            if ($service->created_by) {
                $creator = get_userdata($service->created_by);
                $service->created_by_name = $creator ? $creator->display_name : null;
            }

            // Get group statistics
            $stats = $this->model->getGroupStats($id);

            wp_send_json_success([
                'service' => $service,
                'stats' => $stats,
                'meta' => [
                    'can_edit' => current_user_can('manage_options'),
                    'can_delete' => current_user_can('manage_options') && !$this->model->hasGroups($id)
                ]
            ]);

        } catch (\Exception $e) {
            wp_send_json_error([
                'message' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
        }
    }

    public function store() {
        try {
            check_ajax_referer('wp_equipment_nonce', 'nonce');

            if (!current_user_can('manage_options')) {
                throw new \Exception('Insufficient permissions');
            }

            $data = [
                'nama' => sanitize_text_field($_POST['nama']),
                'singkatan' => sanitize_text_field($_POST['singkatan']),
                'keterangan' => sanitize_textarea_field($_POST['keterangan'] ?? ''),
                'status' => 'active'
            ];

            // Validate data
            $errors = $this->validator->validateCreate($data);
            if (!empty($errors)) {
                wp_send_json_error([
                    'message' => implode(', ', $errors),
                    'errors' => $errors
                ]);
                return;
            }

            // Create service
            $id = $this->model->create($data);
            if (!$id) {
                throw new \Exception('Failed to create service');
            }

            // Clear related caches
            $this->cache->invalidateDataTableCache('service_list');
            $this->cache->delete('service_stats');

            // Get created service
            $service = $this->model->find($id);
            
            wp_send_json_success([
                'message' => __('Service created successfully', 'wp-equipment'),
                'service' => $service
            ]);

        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    public function update() {
        try {
            check_ajax_referer('wp_equipment_nonce', 'nonce');

            if (!current_user_can('manage_options')) {
                throw new \Exception('Insufficient permissions');
            }

            $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
            if (!$id) {
                throw new \Exception('Invalid service ID');
            }

            $data = [
                'nama' => sanitize_text_field($_POST['nama']),
                'singkatan' => sanitize_text_field($_POST['singkatan']), // Tambahkan ini
                'keterangan' => sanitize_textarea_field($_POST['keterangan'] ?? ''),
                'status' => sanitize_text_field($_POST['status'] ?? 'active')
            ];

            // Validate data
            $errors = $this->validator->validateUpdate($data, $id);
            if (!empty($errors)) {
                wp_send_json_error([
                    'message' => implode(', ', $errors),
                    'errors' => $errors
                ]);
                return;
            }

            // Update service
            $updated = $this->model->update($id, $data);
            if (!$updated) {
                throw new \Exception('Failed to update service');
            }

            // Clear related caches
            $this->cache->delete('service_detail', $id);
            $this->cache->invalidateDataTableCache('service_list');

            // Get updated service
            $service = $this->model->find($id);
            
            wp_send_json_success([
                'message' => __('Service updated successfully', 'wp-equipment'),
                'service' => $service
            ]);

        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    public function delete() {
        try {
            check_ajax_referer('wp_equipment_nonce', 'nonce');

            if (!current_user_can('manage_options')) {
                throw new \Exception('Insufficient permissions');
            }

            $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
            if (!$id) {
                throw new \Exception('Invalid service ID');
            }

            // Validate deletion
            $errors = $this->validator->validateDelete($id);
            if (!empty($errors)) {
                wp_send_json_error(['message' => implode(', ', $errors)]);
                return;
            }

            // Get service before deletion for response
            $service = $this->model->find($id);
            if (!$service) {
                throw new \Exception('Service not found');
            }

            // Delete service
            $deleted = $this->model->delete($id);
            if (!$deleted) {
                throw new \Exception('Failed to delete service');
            }

            // Clear related caches
            $this->cache->delete('service_detail', $id);
            $this->cache->invalidateDataTableCache('service_list');
            $this->cache->delete('service_stats');

            wp_send_json_success([
                'message' => __('Service deleted successfully', 'wp-equipment'),
                'service' => $service
            ]);

        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    public function getServiceStats() {
        try {
            check_ajax_referer('wp_equipment_nonce', 'nonce');

            if (!current_user_can('manage_options')) {
                throw new \Exception('Insufficient permissions');
            }

            // Try to get from cache first
            $stats = $this->cache->get('service_stats');
            
            if ($stats === null) {
                $stats = [
                    'total' => $this->model->getTotalCount(),
                    'active' => $this->model->getActiveCount(),
                    'with_groups' => $this->model->getCountWithGroups(),
                    'recent' => $this->model->getRecentServices(5)
                ];
                
                // Cache stats for 5 minutes
                $this->cache->set('service_stats', $stats, 300);
            }

            wp_send_json_success($stats);

        } catch (\Exception $e) {
            wp_send_json_error([
                'message' => $e->getMessage()
            ]);
        }
    }

    public function createService($data) {
        try {
            $service_id = $this->serviceModel->create($data);
            
            if (!$service_id) {
                throw new \Exception("Gagal membuat service");
            }

            // Clear related caches
            $this->cache->invalidateDataTableCache('service_list');
            
            return $service_id;
        } catch (\Exception $e) {
            error_log("Error in createService: " . $e->getMessage());
            return false;
        }
    }

    public function createDemoService($data) {
        return $this->createService($data);
    }

}
