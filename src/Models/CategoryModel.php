<?php
/**
 * Category Model Class
 *
 * @package     WP_Equipment
 * @subpackage  Models
 * @version     1.0.0
 * @author      arisciwek
 *
 * Path: /wp-equipment/src/Models/CategoryModel.php
 *
 * Description: Model untuk mengelola data kategori di database.
 *              Handles operasi CRUD dengan caching terintegrasi.
 *              Support hierarki parent-child dan level management.
 *              Includes query optimization dan data formatting.
 *              Menyediakan metode untuk DataTables server-side.
 */

namespace WPEquipment\Models;

use WPEquipment\Cache\EquipmentCacheManager;

class CategoryModel {
    private $table;
    private EquipmentCacheManager $cache;

    public function __construct() {
        global $wpdb;
        $this->table = $wpdb->prefix . 'app_categories';
        $this->cache = new EquipmentCacheManager();
    }

    /**
     * Get categories by level
     */
    public function getByLevel(int $level): array {
        global $wpdb;

        return $wpdb->get_results($wpdb->prepare("
            SELECT id, code, name 
            FROM {$this->table}
            WHERE level = %d
            ORDER BY sort_order ASC, name ASC
        ", $level));
    }

    public function create(array $data): ?int {
        global $wpdb;

        // Pre create hook - sebagai filter
        // Plugin lain bisa modifikasi data atau batalkan operasi
        $filtered_data = apply_filters('wp_equipment_pre_create_category', $data);
        
        // Cek jika operasi dibatalkan
        if ($filtered_data === false) {
            return null;
        }
        
        // Gunakan data yang sudah difilter

        $result = $wpdb->insert(
            $this->table,
            [
                'code' => $data['code'],
                'name' => $data['name'],
                'description' => $data['description'] ?? '',
                'level' => $data['level'],
                'parent_id' => $data['parent_id'] ?? null,
                'group_id' => $data['group_id'] ?? null,
                'relation_id' => $data['relation_id'] ?? null,
                'sort_order' => $data['sort_order'] ?? 0,
                'unit' => $data['unit'] ?? null,
                'pnbp' => $data['pnbp'] ?? null,
                'status' => $data['status'] ?? 'active',
                'created_by' => get_current_user_id(),
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            ],
            [
                '%s', '%s', '%s', '%d', '%d', '%d', '%d', 
                '%d', '%s', '%f', '%s', '%d', '%s', '%s'
            ]
        );

        if ($result === false) {
            return null;
        }

        $id = (int) $wpdb->insert_id;
        
        // Clear cache
        $this->clearCategoryCache($id);
        
        // Post create hook - sebagai action
        // Plugin lain bisa jalankan aksi tambahan
        do_action('wp_equipment_post_create_category', $id, $filtered_data);
        
        return $id;
    }

    public function find($id): ?object {
        global $wpdb;

        // Try to get from cache first
        $cached = $this->cache->get('category', $id);
        if ($cached !== null) {
            return $cached;
        }

        $id = (int) $id;
        
        // Get category with parent info
        $result = $wpdb->get_row($wpdb->prepare("
            SELECT c.*,
                   p.name as parent_name,
                   p.code as parent_code
            FROM {$this->table} c
            LEFT JOIN {$this->table} p ON c.parent_id = p.id
            WHERE c.id = %d
        ", $id));

        if ($result === null) {
            return null;
        }

        // Add to cache
        $this->cache->set('category', $result, null, $id);

        return $result;
    }

    public function update(int $id, array $data): bool {
        global $wpdb;

        // Pre update hook - sebagai filter
        // Plugin lain bisa modifikasi data atau batalkan operasi
        $filtered_data = apply_filters('wp_equipment_pre_update_category', $data, $id);
        
        // Cek jika operasi dibatalkan
        if ($filtered_data === false) {
            return false;
        }

        $updateData = array_merge(
            array_intersect_key($filtered_data, [
                'code' => true,
                'name' => true,
                'description' => true,
                'level' => true,
                'parent_id' => true,
                'group_id' => true,
                'relation_id' => true,
                'sort_order' => true,
                'unit' => true,
                'pnbp' => true,
                'status' => true
            ]),
            ['updated_at' => current_time('mysql')]
        );

        // Prepare format array based on data types
        $format = [];
        foreach ($updateData as $key => $value) {
            switch ($key) {
                case 'code':
                case 'name':
                case 'description':
                case 'unit':
                case 'status':
                case 'updated_at':
                    $format[] = '%s';
                    break;
                case 'level':
                case 'parent_id':
                case 'group_id':
                case 'relation_id':
                case 'sort_order':
                    $format[] = '%d';
                    break;
                case 'pnbp':
                    $format[] = '%f';
                    break;
            }
        }

        $result = $wpdb->update(
            $this->table,
            $updateData,
            ['id' => $id],
            $format,
            ['%d']
        );

        if ($result !== false) {
            $this->clearCategoryCache($id);
            
            // Post update hook - sebagai action
            // Plugin lain bisa jalankan aksi tambahan
            $updated_category = $this->find($id);
            do_action('wp_equipment_post_update_category', $id, $updated_category, $filtered_data);
        }

        return $result !== false;
    }

    public function getDataTableData(int $start, int $length, string $search, string $orderColumn, string $orderDir): array {
        global $wpdb;

        // Base query parts
        $select = "SELECT SQL_CALC_FOUND_ROWS c.*, 
                         p.name as parent_name,
                         p.code as parent_code";
        $from = " FROM {$this->table} c";
        $join = " LEFT JOIN {$this->table} p ON c.parent_id = p.id";
        $where = " WHERE c.status = 'active'"; // Menambahkan filter untuk status active

        // Add search if provided
        if (!empty($search)) {
            $where .= $wpdb->prepare(
                " AND (c.name LIKE %s OR c.code LIKE %s OR c.description LIKE %s)",
                '%' . $wpdb->esc_like($search) . '%',
                '%' . $wpdb->esc_like($search) . '%',
                '%' . $wpdb->esc_like($search) . '%'
            );
        }

        // Validate order column
        $validColumns = ['code', 'name', 'level', 'parent_name', 'unit', 'pnbp'];
        if (!in_array($orderColumn, $validColumns)) {
            $orderColumn = 'code';
        }

        // Map frontend column to actual column
        $orderColumnMap = [
            'code' => 'c.code',
            'name' => 'c.name',
            'level' => 'c.level',
            'parent_name' => 'p.name',
            'unit' => 'c.unit',
            'pnbp' => 'c.pnbp'
        ];

        $orderColumn = $orderColumnMap[$orderColumn] ?? 'c.code';
        $orderDir = strtoupper($orderDir) === 'DESC' ? 'DESC' : 'ASC';

        // Build order and limit
        $order = " ORDER BY " . esc_sql($orderColumn) . " " . esc_sql($orderDir);
        $limit = $wpdb->prepare(" LIMIT %d, %d", $start, $length);

        // Complete query
        $sql = $select . $from . $join . $where . $order . $limit;
        
        //error_log("Generated SQL Query:");
        //error_log($sql);

        // Get paginated results
        $results = $wpdb->get_results($sql);
        
        if ($results === null) {
            error_log("Query error: " . $wpdb->last_error);
            throw new \Exception($wpdb->last_error);
        }

        //error_log("Query results count: " . count($results));

        // Get total filtered count
        $filtered = $wpdb->get_var("SELECT FOUND_ROWS()");
        //error_log("Filtered count: " . $filtered);

        // Get total count
        $total = $wpdb->get_var("SELECT COUNT(DISTINCT id) FROM {$this->table}");
        //error_log("Total count: " . $total);

        $return = [
            'data' => $results,
            'total' => (int) $total,
            'filtered' => (int) $filtered
        ];

        //error_log("Returning data:");
        //error_log(print_r($return, true));
        //error_log("=== End Category Model Query ===");

        return $return;
    }

    public function delete(int $id): bool {
        global $wpdb;

        // Check for child categories first
        $hasChildren = $this->hasChildren($id);
        if ($hasChildren) {
            return false;
        }

        // Pre delete hook - sebagai filter
        // Plugin lain bisa membatalkan operasi delete
        $should_delete = apply_filters('wp_equipment_pre_delete_category', true, $id);
        if ($should_delete === false) {
            return false;
        }

        // Ambil data kategori sebelum dihapus untuk post hook
        $category = $this->find($id);
        if (!$category) {
            return false;
        }

        $result = $wpdb->delete(
            $this->table,
            ['id' => $id],
            ['%d']
        );

        if ($result !== false) {
            $this->clearCategoryCache($id);
            
            // Post delete hook - sebagai action
            // Plugin lain bisa jalankan aksi tambahan setelah delete
            do_action('wp_equipment_post_delete_category', $id, $category);
        }

        return $result !== false;
    }
    
    public function hasChildren(int $id): bool {
        global $wpdb;

        return (bool) $wpdb->get_var($wpdb->prepare(
            "SELECT EXISTS (SELECT 1 FROM {$this->table} WHERE parent_id = %d) as has_children",
            $id
        ));
    }

    public function getChildren(int $parentId): array {
        global $wpdb;

        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$this->table} WHERE parent_id = %d ORDER BY sort_order ASC",
            $parentId
        ));
    }

    public function existsByCode(string $code, ?int $excludeId = null): bool {
        global $wpdb;

        $sql = "SELECT EXISTS (SELECT 1 FROM {$this->table} WHERE code = %s";
        $params = [$code];

        if ($excludeId) {
            $sql .= " AND id != %d";
            $params[] = $excludeId;
        }

        $sql .= ") as result";

        return (bool) $wpdb->get_var($wpdb->prepare($sql, $params));
    }

    public function getCategoryTree(?int $parentId = null, int $maxDepth = 10, int $currentDepth = 0): array {
        if ($currentDepth >= $maxDepth) {
            return [];
        }

        global $wpdb;

        $where = $parentId === null ? 
            "WHERE parent_id IS NULL" : 
            $wpdb->prepare("WHERE parent_id = %d", $parentId);

        $categories = $wpdb->get_results("
            SELECT * 
            FROM {$this->table} 
            {$where}
            ORDER BY sort_order ASC, name ASC
        ");

        $tree = [];
        foreach ($categories as $category) {
            $category->children = $this->getCategoryTree(
                $category->id, 
                $maxDepth, 
                $currentDepth + 1
            );
            $tree[] = $category;
        }

        return $tree;
    }

    private function clearCategoryCache(int $id): void {
        // Clear specific category cache
        $this->cache->delete('category', $id);
        
        // Clear category tree cache
        $this->cache->delete('category_tree');
        
        // Clear DataTable cache
        $this->cache->invalidateDataTableCache('category_list');
    }

    public function getTotalCount(): int {
        global $wpdb;
        return (int) $wpdb->get_var("SELECT COUNT(*) FROM {$this->table}");
    }
}
