<?php
/**
 * Sector Controller Class
 *
 * @package     WP_Equipment
 * @subpackage  Controllers
 * @version     1.0.0
 * @author      arisciwek
 *
 * Path: /wp-equipment/src/Controllers/SectorController.php
 *
 * Description: Controller untuk mengelola data sektor.
 *              Menangani operasi CRUD dengan cache management.
 *              Includes validasi input, permission checks,
 *              dan response formatting untuk panel kanan.
 *              Menyediakan endpoints untuk DataTables server-side.
 */

namespace WPEquipment\Controllers;

use WPEquipment\Models\SectorModel;
use WPEquipment\Validators\SectorValidator;
use WPEquipment\Cache\EquipmentCacheManager;

class SectorController {
    private SectorModel $model;
    private SectorValidator $validator;
    private EquipmentCacheManager $cache;
    private string $log_file;

    private const DEFAULT_LOG_FILE = 'logs/sector.log';

    public function __construct() {
        $this->model = new SectorModel();
        $this->validator = new SectorValidator();
        $this->cache = new EquipmentCacheManager();

        // Initialize log file
        $this->log_file = WP_EQUIPMENT_PATH . self::DEFAULT_LOG_FILE;
        $this->initLogDirectory();

        // Register AJAX handlers
        add_action('wp_ajax_handle_sector_datatable', [$this, 'handleDataTableRequest']);
        add_action('wp_ajax_get_sector', [$this, 'show']);
        add_action('wp_ajax_create_sector', [$this, 'store']);
        add_action('wp_ajax_update_sector', [$this, 'update']);
        add_action('wp_ajax_delete_sector', [$this, 'delete']);
        add_action('wp_ajax_get_sector_stats', [$this, 'getSectorStats']);
        add_action('wp_ajax_create_sector_button', [$this, 'createSectorButton']);
    }

    private function initLogDirectory(): void {
        $upload_dir = wp_upload_dir();
        $sector_base_dir = $upload_dir['basedir'] . '/wp-equipment';
        $sector_log_dir = $sector_base_dir . '/logs';
        
        $this->log_file = $sector_log_dir . '/sector-' . date('Y-m') . '.log';

        if (!file_exists($sector_base_dir)) {
            wp_mkdir_p($sector_base_dir);
            $htaccess_content = "Order deny,allow\nDeny from all";
            @file_put_contents($sector_base_dir . '/.htaccess', $htaccess_content);
        }

        if (!file_exists($sector_log_dir)) {
            wp_mkdir_p($sector_log_dir);
            @file_put_contents($sector_log_dir . '/.htaccess', $htaccess_content);
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

    public function createSectorButton() {
        try {
            check_ajax_referer('wp_equipment_nonce', 'nonce');
            
            if (!current_user_can('manage_options')) {
                wp_send_json_success(['button' => '']);
                return;
            }

            $button = '<button type="button" class="button button-primary" id="add-sector-btn">';
            $button .= '<span class="dashicons dashicons-plus-alt"></span>';
            $button .= __('Tambah Sektor', 'wp-equipment');
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

            // Get DataTable parameters
            $draw = isset($_POST['draw']) ? intval($_POST['draw']) : 1;
            $start = isset($_POST['start']) ? intval($_POST['start']) : 0;
            $length = isset($_POST['length']) ? intval($_POST['length']) : 10;
            $search = isset($_POST['search']['value']) ? sanitize_text_field($_POST['search']['value']) : '';
            
            $orderColumn = isset($_POST['order'][0]['column']) ? intval($_POST['order'][0]['column']) : 0;
            $orderDir = isset($_POST['order'][0]['dir']) ? sanitize_text_field($_POST['order'][0]['dir']) : 'asc';

            // Define sortable columns
            $columns = ['nama', 'keterangan', 'total_groups', 'status', 'actions'];
            $orderBy = isset($columns[$orderColumn]) ? $columns[$orderColumn] : 'nama';

            // Try to get from cache first
            $cache_key = "sector_datatable_{$start}_{$length}_{$search}_{$orderBy}_{$orderDir}";
            $result = $this->cache->get('sector', $cache_key);

            if ($result === null) {
                $result = $this->model->getDataTableData($start, $length, $search, $orderBy, $orderDir);
                $this->cache->set('sector', $result, 300, $cache_key); // Cache for 5 minutes
            }

            // Format response
            $response = [
                'draw' => $draw,
                'recordsTotal' => $result['total'],
                'recordsFiltered' => $result['filtered'],
                'data' => array_map(function($sector) {
                    return [
                        'id' => $sector->id,
                        'nama' => esc_html($sector->nama),
                        'keterangan' => esc_html($sector->keterangan ?: '-'),
                        'total_groups' => intval($sector->total_groups),
                        'status' => esc_html($sector->status),
                        'actions' => $this->generateActionButtons($sector)
                    ];
                }, $result['data'])
            ];

            wp_send_json($response);

        } catch (\Exception $e) {
            $this->debug_log('DataTable Error: ' . $e->getMessage());
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    private function generateActionButtons($sector): string {
        if (!current_user_can('manage_options')) {
            return '';
        }

        $actions = sprintf(
            '<button type="button" class="button view-sector" data-id="%d" title="%s">
                <i class="dashicons dashicons-visibility"></i>
            </button> ',
            $sector->id,
            __('View', 'wp-equipment')
        );

        $actions .= sprintf(
            '<button type="button" class="button edit-sector" data-id="%d" title="%s">
                <i class="dashicons dashicons-edit"></i>
            </button> ',
            $sector->id,
            __('Edit', 'wp-equipment')
        );

        if (!$this->model->hasGroups($sector->id)) {
            $actions .= sprintf(
                '<button type="button" class="button delete-sector" data-id="%d" title="%s">
                    <i class="dashicons dashicons-trash"></i>
                </button>',
                $sector->id,
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
                throw new \Exception('Invalid sector ID');
            }

            // Try to get from cache first
            $sector = $this->cache->get('sector_detail', $id);
            
            if ($sector === null) {
                $sector = $this->model->find($id);
                if (!$sector) {
                    throw new \Exception('Sector not found');
                }
                $this->cache->set('sector_detail', $sector, 300, $id);
            }

            // Format timestamps
            $sector->created_at = mysql2date('Y-m-d H:i:s', $sector->created_at);
            $sector->updated_at = mysql2date('Y-m-d H:i:s', $sector->updated_at);

            // Get creator info
            if ($sector->created_by) {
                $creator = get_userdata($sector->created_by);
                $sector->created_by_name = $creator ? $creator->display_name : null;
            }

            // Get group statistics
            $stats = $this->model->getGroupStats($id);

            wp_send_json_success([
                'sector' => $sector,
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

            // Create sector
            $id = $this->model->create($data);
            if (!$id) {
                throw new \Exception('Failed to create sector');
            }

            // Clear related caches
            $this->cache->invalidateDataTableCache('sector_list');
            $this->cache->delete('sector_stats');

            // Get created sector
            $sector = $this->model->find($id);
            
            wp_send_json_success([
                'message' => __('Sector created successfully', 'wp-equipment'),
                'sector' => $sector
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
                throw new \Exception('Invalid sector ID');
            }

            $data = [
                'nama' => sanitize_text_field($_POST['nama']),
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

            // Update sector
            $updated = $this->model->update($id, $data);
            if (!$updated) {
                throw new \Exception('Failed to update sector');
            }

            // Clear related caches
            $this->cache->delete('sector_detail', $id);
            $this->cache->invalidateDataTableCache('sector_list');

            // Get updated sector
            $sector = $this->model->find($id);
            
            wp_send_json_success([
                'message' => __('Sector updated successfully', 'wp-equipment'),
                'sector' => $sector
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
                throw new \Exception('Invalid sector ID');
            }

            // Validate deletion
            $errors = $this->validator->validateDelete($id);
            if (!empty($errors)) {
                wp_send_json_error(['message' => implode(', ', $errors)]);
                return;
            }

            // Get sector before deletion for response
            $sector = $this->model->find($id);
            if (!$sector) {
                throw new \Exception('Sector not found');
            }

            // Delete sector
            $deleted = $this->model->delete($id);
            if (!$deleted) {
                throw new \Exception('Failed to delete sector');
            }

            // Clear related caches
            $this->cache->delete('sector_detail', $id);
            $this->cache->invalidateDataTableCache('sector_list');
            $this->cache->delete('sector_stats');

            wp_send_json_success([
                'message' => __('Sector deleted successfully', 'wp-equipment'),
                'sector' => $sector
            ]);

        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    public function getSectorStats() {
        try {
            check_ajax_referer('wp_equipment_nonce', 'nonce');

            if (!current_user_can('manage_options')) {
                throw new \Exception('Insufficient permissions');
            }

            // Try to get from cache first
            $stats = $this->cache->get('sector_stats');
            
            if ($stats === null) {
                $stats = [
                    'total' => $this->model->getTotalCount(),
                    'active' => $this->model->getActiveCount(),
                    'with_groups' => $this->model->getCountWithGroups(),
                    'recent' => $this->model->getRecentSectors(5)
                ];
                
                // Cache stats for 5 minutes
                $this->cache->set('sector_stats', $stats, 300);
            }

            wp_send_json_success($stats);

        } catch (\Exception $e) {
            wp_send_json_error([
                'message' => $e->getMessage()
            ]);
        }
    }

    public function createSector($data) {
        try {
            $sector_id = $this->sectorModel->create($data);
            
            if (!$sector_id) {
                throw new \Exception("Gagal membuat sector");
            }

            // Clear related caches
            $this->cache->invalidateDataTableCache('sector_list');
            
            return $sector_id;
        } catch (\Exception $e) {
            error_log("Error in createSector: " . $e->getMessage());
            return false;
        }
    }

    public function createDemoSector($data) {
        return $this->createSector($data);
    }

}
