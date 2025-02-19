<?php
/**
 * Group Controller Class
 *
 * @package     WP_Equipment
 * @subpackage  Controllers
 * @version     1.0.0
 * @author      arisciwek
 *
 * Path: /wp-equipment/src/Controllers/GroupController.php
 *
 * Description: Controller untuk mengelola data grup.
 *              Menangani operasi CRUD dengan cache management.
 *              Includes validasi input, permission checks,
 *              dan response formatting untuk panel kanan.
 *              Menyediakan endpoints untuk DataTables server-side.
 */

namespace WPEquipment\Controllers;

use WPEquipment\Models\GroupModel;
use WPEquipment\Models\ServiceModel;
use WPEquipment\Validators\GroupValidator;
use WPEquipment\Cache\EquipmentCacheManager;

class GroupController {
    private GroupModel $model;
    private ServiceModel $serviceModel;
    private GroupValidator $validator;
    private EquipmentCacheManager $cache;
    private string $log_file;

    private const DEFAULT_LOG_FILE = 'logs/group.log';

    public function __construct() {
        $this->model = new GroupModel();
        $this->serviceModel = new ServiceModel();
        $this->validator = new GroupValidator();
        $this->cache = new EquipmentCacheManager();

        // Initialize log file
        $this->log_file = WP_EQUIPMENT_PATH . self::DEFAULT_LOG_FILE;
        $this->initLogDirectory();

        // Register AJAX handlers
        add_action('wp_ajax_handle_group_datatable', [$this, 'handleDataTableRequest']);
        add_action('wp_ajax_get_group', [$this, 'show']);
        add_action('wp_ajax_create_group', [$this, 'store']);
        add_action('wp_ajax_update_group', [$this, 'update']);
        add_action('wp_ajax_delete_group', [$this, 'delete']);
        add_action('wp_ajax_get_group_stats', [$this, 'getGroupStats']);
        add_action('wp_ajax_create_group_button', [$this, 'createGroupButton']);
        add_action('wp_ajax_get_service_groups', [$this, 'getServiceGroups']);
    }

    private function initLogDirectory(): void {
        $upload_dir = wp_upload_dir();
        $group_base_dir = $upload_dir['basedir'] . '/wp-equipment';
        $group_log_dir = $group_base_dir . '/logs';
        
        $this->log_file = $group_log_dir . '/group-' . date('Y-m') . '.log';

        if (!file_exists($group_base_dir)) {
            wp_mkdir_p($group_base_dir);
            $htaccess_content = "Order deny,allow\nDeny from all";
            @file_put_contents($group_base_dir . '/.htaccess', $htaccess_content);
        }

        if (!file_exists($group_log_dir)) {
            wp_mkdir_p($group_log_dir);
            @file_put_contents($group_log_dir . '/.htaccess', $htaccess_content);
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

    public function createGroupButton() {
        try {
            check_ajax_referer('wp_equipment_nonce', 'nonce');
            
            if (!current_user_can('manage_options')) {
                wp_send_json_success(['button' => '']);
                return;
            }

            $service_id = isset($_POST['service_id']) ? intval($_POST['service_id']) : 0;
            if (!$service_id) {
                throw new \Exception('Invalid service ID');
            }

            // Get service dari cache atau database
            $service = $this->cache->get('service_detail', $service_id);
            if ($service === null) {
                $service = $this->serviceModel->find($service_id);
                if ($service) {
                    $this->cache->set('service_detail', $service, 300, $service_id);
                }
            }

            if (!$service || $service->status !== 'active') {
                wp_send_json_success(['button' => '']);
                return;
            }

            $button = '<button type="button" class="button button-primary" id="add-group-btn" data-service="' . esc_attr($service_id) . '">';
            $button .= '<span class="dashicons dashicons-plus-alt"></span>';
            $button .= sprintf(__('Tambah Grup ke %s', 'wp-equipment'), esc_html($service->nama));
            $button .= '</button>';

            wp_send_json_success(['button' => $button]);

        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    public function handleDataTableRequest() {
        try {
            // Verify nonce and permissions
            check_ajax_referer('wp_equipment_nonce', 'nonce');
            if (!current_user_can('manage_options')) {
                throw new \Exception('Insufficient permissions');
            }

            // Get service ID if filtering by service
            $service_id = isset($_POST['service_id']) ? intval($_POST['service_id']) : null;

            // Get DataTable parameters
            $draw = isset($_POST['draw']) ? intval($_POST['draw']) : 1;
            $start = isset($_POST['start']) ? intval($_POST['start']) : 0;
            $length = isset($_POST['length']) ? intval($_POST['length']) : 10;
            $search = isset($_POST['search']['value']) ? sanitize_text_field($_POST['search']['value']) : '';
            
            $orderColumn = isset($_POST['order'][0]['column']) ? intval($_POST['order'][0]['column']) : 0;
            $orderDir = isset($_POST['order'][0]['dir']) ? sanitize_text_field($_POST['order'][0]['dir']) : 'asc';

            // Define sortable columns
            $columns = ['nama', 'service_nama', 'dokumen_type', 'status', 'actions'];
            $orderBy = isset($columns[$orderColumn]) ? $columns[$orderColumn] : 'nama';

            // Try to get from cache first
            $cache_key = "group_datatable_{$start}_{$length}_{$search}_{$orderBy}_{$orderDir}";
            if ($service_id) {
                $cache_key .= "_{$service_id}";
            }
            
            $result = $this->cache->get('group', $cache_key);

            if ($result === null) {
                $result = $this->model->getDataTableData($start, $length, $search, $orderBy, $orderDir, $service_id);
                $this->cache->set('group', $result, 300, $cache_key); // Cache for 5 minutes
            }

            // Format response
            $response = [
                'draw' => $draw,
                'recordsTotal' => $result['total'],
                'recordsFiltered' => $result['filtered'],
                'data' => array_map(function($group) {
                    $doc_link = '';
                    if ($group->dokumen_path && $group->dokumen_type) {
                        $doc_link = sprintf(
                            '<a href="%s" target="_blank" class="button">%s</a>',
                            esc_url(site_url($group->dokumen_path)),
                            esc_html(strtoupper($group->dokumen_type))
                        );
                    }

                    return [
                        'id' => $group->id,
                        'nama' => esc_html($group->nama),
                        'service_nama' => esc_html($group->service_nama),
                        'keterangan' => esc_html($group->keterangan ?: '-'),
                        'dokumen' => $doc_link,
                        'status' => esc_html($group->status),
                        'actions' => $this->generateActionButtons($group)
                    ];
                }, $result['data'])
            ];

            wp_send_json($response);

        } catch (\Exception $e) {
            $this->debug_log('DataTable Error: ' . $e->getMessage());
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    private function generateActionButtons($group): string {
        if (!current_user_can('manage_options')) {
            return '';
        }

        $actions = sprintf(
            '<button type="button" class="button view-group" data-id="%d" title="%s">
                <i class="dashicons dashicons-visibility"></i>
            </button> ',
            $group->id,
            __('View', 'wp-equipment')
        );

        $actions .= sprintf(
            '<button type="button" class="button edit-group" data-id="%d" title="%s">
                <i class="dashicons dashicons-edit"></i>
            </button> ',
            $group->id,
            __('Edit', 'wp-equipment')
        );

        $actions .= sprintf(
            '<button type="button" class="button delete-group" data-id="%d" title="%s">
                <i class="dashicons dashicons-trash"></i>
            </button>',
            $group->id,
            __('Delete', 'wp-equipment')
        );

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
                throw new \Exception('Invalid group ID');
            }

            // Try to get from cache first
            $group = $this->cache->get('group_detail', $id);
            
            if ($group === null) {
                $group = $this->model->find($id);
                if (!$group) {
                    throw new \Exception('Group not found');
                }
                $this->cache->set('group_detail', $group, 300, $id);
            }

            // Format timestamps
            $group->created_at = mysql2date('Y-m-d H:i:s', $group->created_at);
            $group->updated_at = mysql2date('Y-m-d H:i:s', $group->updated_at);

            // Get creator info
            if ($group->created_by) {
                $creator = get_userdata($group->created_by);
                $group->created_by_name = $creator ? $creator->display_name : null;
            }

            wp_send_json_success([
                'group' => $group,
                'meta' => [
                    'can_edit' => current_user_can('manage_options'),
                    'can_delete' => current_user_can('manage_options')
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
                'service_id' => intval($_POST['service_id']),
                'nama' => sanitize_text_field($_POST['nama']),
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

            // Create group
            $id = $this->model->create($data);
            if (!$id) {
                throw new \Exception('Failed to create group');
            }

            // Clear related caches
            $this->cache->invalidateDataTableCache('group_list');
            $this->cache->delete('group_stats');
            $this->cache->delete('service_detail', $data['service_id']);

            // Get created group
            $group = $this->model->find($id);
            
            wp_send_json_success([
                'message' => __('Group created successfully', 'wp-equipment'),
                'group' => $group
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
                throw new \Exception('Invalid group ID');
            }

            $data = [
                'service_id' => intval($_POST['service_id']),
                'nama' => sanitize_text_field($_POST['nama']),
                'keterangan' => sanitize_textarea_field($_POST['keterangan'] ?? ''),
                'status' => sanitize_text_field($_POST['status'] ?? 'active')
            ];

            // Get existing data for cache clearing
            $existing = $this->model->find($id);
            if (!$existing) {
                throw new \Exception('Group not found');
            }

            // Validate data
            $errors = $this->validator->validateUpdate($data, $id);
            if (!empty($errors)) {
                wp_send_json_error([
                    'message' => implode(', ', $errors),
                    'errors' => $errors
                ]);
                return;
            }

            // Update group
            $updated = $this->model->update($id, $data);
            if (!$updated) {
                throw new \Exception('Failed to update group');
            }

            // Clear related caches
            $this->cache->delete('group_detail', $id);
            $this->cache->invalidateDataTableCache('group_list');
            
            // Clear service caches if service_id changed
            if ($existing->service_id != $data['service_id']) {
                $this->cache->delete('service_detail', $existing->service_id);
                $this->cache->delete('service_detail', $data['service_id']);
            }

            // Get updated group
            $group = $this->model->find($id);
            
            wp_send_json_success([
                'message' => __('Group updated successfully', 'wp-equipment'),
                'group' => $group
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
                throw new \Exception('Invalid group ID');
            }

            // Validate deletion
            $errors = $this->validator->validateDelete($id);
            if (!empty($errors)) {
                wp_send_json_error(['message' => implode(', ', $errors)]);
                return;
            }

            // Get group before deletion for response
            $group = $this->model->find($id);
            if (!$group) {
                throw new \Exception('Group not found');
            }

            // Delete group
            $deleted = $this->model->delete($id);
            if (!$deleted) {
                throw new \Exception('Failed to delete group');
            }

            // Clear related caches
            $this->cache->delete('group_detail', $id);
            $this->cache->invalidateDataTableCache('group_list');
            $this->cache->delete('group_stats');
            $this->cache->delete('service_detail', $group->service_id);

            wp_send_json_success([
                'message' => __('Group deleted successfully', 'wp-equipment'),
                'group' => $group
            ]);

        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    public function getGroupStats() {
        try {
            check_ajax_referer('wp_equipment_nonce', 'nonce');

            if (!current_user_can('manage_options')) {
                throw new \Exception('Insufficient permissions');
            }

            // Get service ID if filtering by service
            $service_id = isset($_POST['service_id']) ? intval($_POST['service_id']) : null;

            // Try to get from cache first
            $cache_key = 'group_stats';
            if ($service_id) {
                $cache_key .= "_{$service_id}";
            }
            
            $stats = $this->cache->get($cache_key);
            
            if ($stats === null) {
                $stats = [
                    'total' => $this->model->getTotalCount($service_id),
                    'active' => $this->model->getActiveCount($service_id),
                    'recent' => $this->model->getRecentGroups(5)
                ];
                
                // Cache stats for 5 minutes
                $this->cache->set($cache_key, $stats, 300);
            }

            wp_send_json_success($stats);

        } catch (\Exception $e) {
            wp_send_json_error([
                'message' => $e->getMessage()
            ]);
        }
    }

    public function getServiceGroups() {
        try {
            check_ajax_referer('wp_equipment_nonce', 'nonce');

            if (!current_user_can('manage_options')) {
                throw new \Exception('Insufficient permissions');
            }

            $service_id = isset($_POST['service_id']) ? intval($_POST['service_id']) : 0;
            if (!$service_id) {
                throw new \Exception('Invalid service ID');
            }

            // Try to get from cache first
            $groups = $this->cache->get('service_groups', $service_id);
            
            if ($groups === null) {
                $groups = $this->model->getByService($service_id);
                if ($groups) {
                    $this->cache->set('service_groups', $groups, 300, $service_id);
                }
            }

            wp_send_json_success(['groups' => $groups]);

        } catch (\Exception $e) {
            wp_send_json_error([
                'message' => $e->getMessage()
            ]);
        }
    }

    public function createGroup($data) {
        try {
            $group_id = $this->model->create($data);
            
            if (!$group_id) {
                throw new \Exception("Gagal membuat group");
            }

            // Clear related caches
            $this->cache->invalidateDataTableCache('group_list');
            
            return $group_id;
        } catch (\Exception $e) {
            error_log("Error in createGroup: " . $e->getMessage());
            return false;
        }
    }

    public function createDemoGroup($data) {
        return $this->createGroup($data);
    }
}
