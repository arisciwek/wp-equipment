<?php
/**
 * Category Controller Class
 *
 * @package     WP_Equipment
 * @subpackage  Controllers
 * @version     1.0.0
 * @author      arisciwek
 *
 * Path: /wp-equipment/src/Controllers/CategoryController.php
 *
 * Description: Controller untuk mengelola data kategori.
 *              Menangani operasi CRUD dengan integrasi cache.
 *              Includes validasi input, permission checks,
 *              dan response formatting untuk panel kanan.
 *              Menyediakan endpoints untuk DataTables server-side.
 */

namespace WPEquipment\Controllers;

use WPEquipment\Models\CategoryModel;
use WPEquipment\Validators\CategoryValidator;
use WPEquipment\Cache\EquipmentCacheManager;
use WPEquipment\Database\Demo\CategoryDemoData;

class CategoryController {
    private CategoryModel $model;
    private CategoryValidator $validator;
    private EquipmentCacheManager $cache;
    private string $log_file;

    /**
     * Default log file path
     */
    private const DEFAULT_LOG_FILE = 'logs/category.log';

    public function __construct() {
        $this->model = new CategoryModel();
        $this->validator = new CategoryValidator();
        $this->cache = new EquipmentCacheManager();

        // Initialize log file in plugin directory
        $this->log_file = WP_EQUIPMENT_PATH . self::DEFAULT_LOG_FILE;

        // Ensure logs directory exists
        $this->initLogDirectory();

        // Register AJAX handlers
        add_action('wp_ajax_handle_category_datatable', [$this, 'handleDataTableRequest']);
        add_action('wp_ajax_get_category', [$this, 'show']);
        add_action('wp_ajax_create_category', [$this, 'store']);
        add_action('wp_ajax_update_category', [$this, 'update']);
        add_action('wp_ajax_delete_category', [$this, 'delete']);
        add_action('wp_ajax_get_category_tree', [$this, 'getCategoryTree']);

        add_action('wp_ajax_generate_demo_categories', [$this, 'generateDemoDataCategories']);
	    add_action('wp_ajax_get_category_stats', [$this, 'getCategoryStats']);
		add_action('wp_ajax_get_category_parents', [$this, 'getCategoryParents']);
        add_action('wp_ajax_create_category_button', [$this, 'createCategoryButton']);

    }

    public function createCategoryButton() {
        try {
            check_ajax_referer('wp_equipment_nonce', 'nonce');
            
            if (!current_user_can('add_equipment')) {
                wp_send_json_success(['button' => '']);
                return;
            }

            $button = '<button type="button" class="button button-primary" id="add-customer-btn">';
            $button .= '<span class="dashicons dashicons-plus-alt"></span>';
            $button .= __('Tambah Category', 'wp-equipment');
            $button .= '</button>';

            wp_send_json_success([
                'button' => $button
            ]);

        } catch (\Exception $e) {
            wp_send_json_error([
                'message' => $e->getMessage()
            ]);
        }
    }

	/**
	 * Get available parent categories based on child level
	 */
	public function getCategoryParents(): void {
		try {
			// Validasi nonce dan permission
			check_ajax_referer('wp_equipment_nonce', 'nonce');
			if (!current_user_can('manage_options')) {
				throw new \Exception('Insufficient permissions');
			}

			// Validasi child level
			$childLevel = isset($_POST['child_level']) ? intval($_POST['child_level']) : 0;
			if ($childLevel <= 1) {
				throw new \Exception('Invalid child level');
			}

			// Coba ambil dari cache dulu
			$cacheKey = "parent_options_level_{$childLevel}";
			$parentOptions = $this->cache->get('category', $cacheKey);

			if ($parentOptions === null) {
				// Jika tidak ada di cache, query dari database
				$parentLevel = $childLevel - 1;
				$parentOptions = $this->model->getByLevel($parentLevel);
				
				// Simpan ke cache
				$this->cache->set('category', $parentOptions, null, $cacheKey);
			}

			wp_send_json_success($parentOptions);

		} catch (\Exception $e) {
			wp_send_json_error([
				'message' => $e->getMessage()
			]);
		}
	}

	public function getCategoryStats() {
		global $wpdb;
	    try {
	        check_ajax_referer('wp_equipment_nonce', 'nonce');

	        if (!current_user_can('manage_options')) {
	            throw new \Exception('Insufficient permissions');
	        }

	        // Get total count
	        $total = $this->model->getTotalCount();

	        // Get recently added categories (last 5)
	        $recent = $wpdb->get_results(
	            "SELECT id, code, name, created_at 
	             FROM {$wpdb->prefix}app_categories 
	             ORDER BY created_at DESC 
	             LIMIT 5"
	        );

	        if ($recent) {
	            foreach ($recent as &$item) {
	                $item->created_at = mysql2date('Y-m-d H:i:s', $item->created_at);
	            }
	        }

	        wp_send_json_success([
	            'total' => $total,
	            'recentlyAdded' => $recent
	        ]);

	    } catch (\Exception $e) {
	        wp_send_json_error([
	            'message' => $e->getMessage()
	        ]);
	    }
	}
    /**
     * Initialize log directory if it doesn't exist
     */
    private function initLogDirectory(): void {
        $upload_dir = wp_upload_dir();
        $category_base_dir = $upload_dir['basedir'] . '/wp-equipment';
        $category_log_dir = $category_base_dir . '/logs';
        
        $this->log_file = $category_log_dir . '/category-' . date('Y-m') . '.log';

        if (!file_exists($category_base_dir)) {
            wp_mkdir_p($category_base_dir);
            $htaccess_content = "Order deny,allow\nDeny from all";
            @file_put_contents($category_base_dir . '/.htaccess', $htaccess_content);
        }

        if (!file_exists($category_log_dir)) {
            wp_mkdir_p($category_log_dir);
            @file_put_contents($category_log_dir . '/.htaccess', $htaccess_content);
        }

        if (!file_exists($this->log_file)) {
            @touch($this->log_file);
            @chmod($this->log_file, 0644);
        }
    }

    /**
     * Log debug messages
     */
    private function debug_log($message): void {
        if (!defined('WP_DEBUG') || !WP_DEBUG) {
            return;
        }

        $timestamp = current_time('mysql');

        if (is_array($message) || is_object($message)) {
            $message = print_r($message, true);
        }

        $log_message = "[{$timestamp}] {$message}\n";
        error_log($log_message, 3, $this->log_file);
    }

	public function handleDataTableRequest() {
	    try {
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

	        $columns = ['code', 'name', 'level', 'parent_name', 'unit', 'pnbp', 'actions'];
	        $orderBy = isset($columns[$orderColumn]) ? $columns[$orderColumn] : 'code';

	        if ($orderBy === 'actions') {
	            $orderBy = 'code';
	        }

	        try {
	            $result = $this->model->getDataTableData($start, $length, $search, $orderBy, $orderDir);

	            if (!$result) {
	                throw new \Exception('No data returned from model');
	            }

	            // Pastikan data selalu array
	            $data = [];
	            if (!empty($result['data'])) {
	                foreach ($result['data'] as $category) {
	                    $data[] = [
	                        'id' => $category->id,
	                        'code' => esc_html($category->code),
	                        'name' => esc_html($category->name),
	                        'level' => intval($category->level),
	                        'parent_name' => $category->parent_name ? esc_html($category->parent_name) : '-',
	                        'unit' => $category->unit ? esc_html($category->unit) : '-',
	                        'pnbp' => $category->pnbp ? number_format($category->pnbp, 2) : '-',
	                        'actions' => $this->generateActionButtons($category)
	                    ];
	                }
	            }

	            // Format response sesuai spesifikasi DataTables
	            $response = [
	                'draw' => $draw,
	                'recordsTotal' => intval($result['total']),
	                'recordsFiltered' => intval($result['filtered']),
	                'data' => $data
	            ];

	            // Debug log response
	            error_log('DataTable Response: ' . print_r($response, true));

	            wp_send_json($response);
	            return;

	        } catch (\Exception $e) {
	            error_log('DataTable Error: ' . $e->getMessage());
	            throw new \Exception('Database error: ' . $e->getMessage());
	        }

	    } catch (\Exception $e) {
	        error_log('Ajax Handler Error: ' . $e->getMessage());
	        wp_send_json_error([
	            'message' => $e->getMessage(),
	            'code' => $e->getCode()
	        ]);
	    }
	}

	private function generateActionButtons($category) {
	    if (!current_user_can('manage_options')) {
	        return '';
	    }

	    $actions = '';
	    
	    $actions .= sprintf(
	        '<button type="button" class="button view-category" data-id="%d" title="%s"><i class="dashicons dashicons-visibility"></i></button> ',
	        $category->id,
	        __('View', 'wp-equipment')
	    );

	    $actions .= sprintf(
	        '<button type="button" class="button edit-category" data-id="%d" title="%s"><i class="dashicons dashicons-edit"></i></button> ',
	        $category->id,
	        __('Edit', 'wp-equipment')
	    );

	    if (!$this->model->hasChildren($category->id)) {
	        $actions .= sprintf(
	            '<button type="button" class="button delete-category" data-id="%d" title="%s"><i class="dashicons dashicons-trash"></i></button>',
	            $category->id,
	            __('Delete', 'wp-equipment')
	        );
	    }

	    return $actions;
	}


	public function store() {
	   try {
	       check_ajax_referer('wp_equipment_nonce', 'nonce');

	       if (!current_user_can('manage_options')) {
	           wp_send_json_error(['message' => __('Insufficient permissions', 'wp-equipment')]);
	           return;
	       }

	       $data = [
	           'code' => sanitize_text_field($_POST['code']),
	           'name' => sanitize_text_field($_POST['name']),
	           'description' => sanitize_textarea_field($_POST['description'] ?? ''),
	           'level' => intval($_POST['level']),
	           'parent_id' => !empty($_POST['parent_id']) ? intval($_POST['parent_id']) : null,
	           'sort_order' => !empty($_POST['sort_order']) ? intval($_POST['sort_order']) : 0,
	           'unit' => !empty($_POST['unit']) ? sanitize_text_field($_POST['unit']) : null,
	           'pnbp' => !empty($_POST['pnbp']) ? floatval($_POST['pnbp']) : null
	       ];

	       $errors = $this->validator->validateCreate($data);
	       if (!empty($errors)) {
	           wp_send_json_error([
	               'message' => is_array($errors) ? implode(', ', $errors) : $errors,
	               'errors' => $errors
	           ]);
	           return;
	       }

	       $id = $this->model->create($data);
	       if (!$id) {
	           wp_send_json_error(['message' => __('Failed to create category', 'wp-equipment')]);
	           return;
	       }

	       $category = $this->model->find($id);
	       if (!$category) {
	           wp_send_json_error(['message' => __('Failed to retrieve created category', 'wp-equipment')]);
	           return;
	       }

	       wp_send_json_success([
	           'id' => $id,
	           'category' => $category,
	           'message' => __('Category created successfully', 'wp-equipment')
	       ]);

	   } catch (\Exception $e) {
	       wp_send_json_error([
	           'message' => $e->getMessage() ?: __('Error creating category', 'wp-equipment'),
	           'error_details' => WP_DEBUG ? $e->getTraceAsString() : null
	       ]);
	   }
	}

	public function update() {
		try {
			check_ajax_referer('wp_equipment_nonce', 'nonce');

			if (!current_user_can('manage_options')) {
				wp_send_json_error(['message' => __('Insufficient permissions', 'wp-equipment')]);
				return;
			}

			$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
			if (!$id) {
				throw new \Exception('Invalid category ID');
			}

			$existing = $this->model->find($id);
			if (!$existing) {
				throw new \Exception('Category not found');
			}

			$data = [
				'code' => sanitize_text_field($_POST['code']),
				'name' => sanitize_text_field($_POST['name']),
				'description' => sanitize_textarea_field($_POST['description'] ?? ''),
				'level' => intval($_POST['level']),
				'parent_id' => !empty($_POST['parent_id']) ? intval($_POST['parent_id']) : null,
				'sort_order' => !empty($_POST['sort_order']) ? intval($_POST['sort_order']) : 0,
				'unit' => !empty($_POST['unit']) ? sanitize_text_field($_POST['unit']) : null,
				'pnbp' => !empty($_POST['pnbp']) ? floatval($_POST['pnbp']) : null
			];

			$errors = $this->validator->validateUpdate($data, $id);
			if (!empty($errors)) {
				wp_send_json_error(['message' => implode(', ', $errors)]);
				return;
			}

			$updated = $this->model->update($id, $data);
			if (!$updated) {
				throw new \Exception('Failed to update category');
			}

			$category = $this->model->find($id);
			if (!$category) {
				throw new \Exception('Failed to retrieve updated category');
			}

			// Send the updated category data in the response
			wp_send_json_success([
				'message' => __('Category updated successfully', 'wp-equipment'),
				'data' => ['category' => $category]
			]);

		} catch (\Exception $e) {
			wp_send_json_error(['message' => $e->getMessage()]);
		}
	}

	public function show() {
	    try {
	        check_ajax_referer('wp_equipment_nonce', 'nonce');

	        if (!current_user_can('manage_options')) {
	            wp_send_json_error(['message' => __('Insufficient permissions', 'wp-equipment')]);
	            return;
	        }

	        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
	        if (!$id) {
	            throw new \Exception('Invalid category ID');
	        }

	        // Ambil data kategori dengan informasi lengkap
	        $category = $this->model->find($id);
	        if (!$category) {
	            throw new \Exception('Category not found');
	        }

	        // Tambahkan informasi tambahan yang diperlukan
	        $parent_info = null;
	        if ($category->parent_id) {
	            $parent_info = $this->model->find($category->parent_id);
	            $category->parent_name = $parent_info ? $parent_info->name : null;
	        }

	        // Format data timestamp
	        $category->created_at = mysql2date('Y-m-d H:i:s', $category->created_at);
	        $category->updated_at = mysql2date('Y-m-d H:i:s', $category->updated_at);

	        // Format pnbp jika ada
	        if ($category->pnbp) {
	            $category->formatted_pnbp = number_format($category->pnbp, 2, ',', '.');
	        }

	        // Ambil data creator jika ada
	        if ($category->created_by) {
	            $creator = get_userdata($category->created_by);
	            $category->created_by_name = $creator ? $creator->display_name : null;
	        }

	        // Siapkan data lengkap untuk response
	        $response_data = [
	            'category' => $category,
	            'children' => $this->model->getChildren($id),
	            'meta' => [
	                'can_edit' => current_user_can('manage_options'),
	                'can_delete' => current_user_can('manage_options') && !$this->model->hasChildren($id)
	            ]
	        ];

	        // Cache response untuk optimasi
	        $cache_key = "category_details_{$id}";
	        wp_cache_set($cache_key, $response_data, '', 300);

	        wp_send_json_success($response_data);

	    } catch (\Exception $e) {
	        wp_send_json_error([
	            'message' => $e->getMessage(),
	            'code' => $e->getCode()
	        ]);
	    }
	}

	public function delete() {
	   try {
	       check_ajax_referer('wp_equipment_nonce', 'nonce');

	       if (!current_user_can('manage_options')) {
	           wp_send_json_error(['message' => __('Insufficient permissions', 'wp-equipment')]);
	           return;
	       }

	       $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
	       if (!$id) {
	           throw new \Exception('Invalid category ID');
	       }

	       if ($this->model->hasChildren($id)) {
	           throw new \Exception(__('Cannot delete category with child categories', 'wp-equipment'));
	       }

	       $deleted = $this->model->delete($id);
	       if (!$deleted) {
	           throw new \Exception('Failed to delete category');
	       }

	       wp_send_json_success([
	           'message' => __('Category deleted successfully', 'wp-equipment')
	       ]);

	   } catch (\Exception $e) {
	       wp_send_json_error(['message' => $e->getMessage()]);
	   }
	}

	public function getCategoryTree() {
	   try {
	       check_ajax_referer('wp_equipment_nonce', 'nonce');

	       if (!current_user_can('manage_options')) {
	           wp_send_json_error(['message' => __('Insufficient permissions', 'wp-equipment')]);
	           return;
	       }

	       $tree = $this->model->getCategoryTree();
	       wp_send_json_success(['tree' => $tree]);

	   } catch (\Exception $e) {
	       wp_send_json_error(['message' => $e->getMessage()]);
	   }
	}

	public function generateDemoDataCategories() {
        try {
            // Validate permissions
            if (!current_user_can('manage_options')) {
                throw new \Exception('Permission denied');
            }

            // Verify nonce
            if (!check_ajax_referer('generate_demo_category', 'nonce', false)) {
                throw new \Exception('Invalid security token');
            }

            // Check development mode
            $dev_settings = get_option('wp_equipment_development_settings', []);
            if (empty($dev_settings['enable_development'])) {
                wp_send_json_error([
                    'message' => 'Development mode is not enabled. Please enable it in settings first.',
                    'type' => 'dev_mode_off'
                ]);
                return;
            }

            // Generate category demo data
            $generator = new CategoryDemoData();
            if ($generator->run()) {
                // Clear relevant caches
                $cache = new \WPEquipment\Cache\EquipmentCacheManager();
                $cache->invalidateDataTableCache('category_list');
                $cache->delete('category_tree');

                wp_send_json_success([
                    'message' => 'Category demo data generated successfully.',
                    'type' => 'success'
                ]);
            } else {
                wp_send_json_error([
                    'message' => 'Failed to generate category demo data.',
                    'type' => 'error'
                ]);
            }

        } catch (\Exception $e) {
            error_log('Category demo data generation failed: ' . $e->getMessage());
            wp_send_json_error([
                'message' => $e->getMessage(),
                'type' => 'error'
            ]);
        }
    }

    public function renderCategoriesPage() {
        // Render template
        require_once WP_EQUIPMENT_PATH . 'src/Views/templates/category/category-dashboard.php';
    }   
}

