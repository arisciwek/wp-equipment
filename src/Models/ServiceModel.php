<?php
/**
 * Service Model Class
 *
 * @package     WP_Equipment
 * @subpackage  Models
 * @version     1.0.0
 * @author      arisciwek
 *
 * Path: /wp-equipment/src/Models/ServiceModel.php
 *
 * Description: Model untuk operasi database sektor.
 *              Fokus pada operasi CRUD dan query.
 *              Tanpa cache management (dipindah ke controller).
 */

namespace WPEquipment\Models;

class ServiceModel {
    private $wpdb;
    private $table_name;

    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table_name = $wpdb->prefix . 'app_services';
    }

    /**
     * Dapatkan data untuk DataTable
     */
    public function getDataTableData(int $start, int $length, string $search, string $orderBy, string $orderDir): array {
        // Base query
        $select = "SELECT SQL_CALC_FOUND_ROWS s.id, s.singkatan, s.nama, s.keterangan, s.status";
        $from = " FROM {$this->table_name} s";
        $where = " WHERE 1=1";
        
        // Search condition
        if (!empty($search)) {
            $where .= $this->wpdb->prepare(
                " AND (s.nama LIKE %s OR s.singkatan LIKE %s)",
                '%' . $this->wpdb->esc_like($search) . '%',
                '%' . $this->wpdb->esc_like($search) . '%'
            );
        }
    
        // Order and limit
        $order = " ORDER BY s.{$orderBy} {$orderDir}";
        $limit = $this->wpdb->prepare(" LIMIT %d, %d", $start, $length);
    
        // Execute main query
        $final_query = $select . $from . $where . $order . $limit;
        $results = $this->wpdb->get_results($final_query);
    
        // Get total records without any filter
        $total_query = "SELECT COUNT(*) FROM {$this->table_name}";
        $total = $this->wpdb->get_var($total_query);
    
        // Get total filtered records
        $filtered_query = "SELECT COUNT(*) FROM {$this->table_name} s WHERE 1=1";
        if (!empty($search)) {
            $filtered_query .= $this->wpdb->prepare(
                " AND (s.nama LIKE %s OR s.singkatan LIKE %s)",
                '%' . $this->wpdb->esc_like($search) . '%',
                '%' . $this->wpdb->esc_like($search) . '%'
            );
        }
        $filtered = $this->wpdb->get_var($filtered_query);
    
        return [
            'data' => $results,
            'total' => (int) $total,
            'filtered' => (int) $filtered
        ];
    }
    
    /**
     * Dapatkan sektor berdasar ID
     */
    public function find(int $id): ?object {
        return $this->wpdb->get_row($this->wpdb->prepare("
            SELECT s.*, COUNT(g.id) as total_groups
            FROM {$this->table_name} s
            LEFT JOIN {$this->wpdb->prefix}app_groups g ON s.id = g.service_id
            WHERE s.id = %d
            GROUP BY s.id
        ", $id));
    }

    /**
     * Buat sektor baru
     */
    public function create(array $data): ?int {
        $result = $this->wpdb->insert(
            $this->table_name,
            [
                'nama' => $data['nama'],
                'singkatan' => $data['singkatan'] ?? null,
                'keterangan' => $data['keterangan'] ?? null,
                'status' => $data['status'] ?? 'active',
                'created_by' => get_current_user_id(),
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            ],
            ['%s', '%s', '%s', '%s', '%d', '%s', '%s']
        );
    
        return $result ? $this->wpdb->insert_id : null;
    }

    /**
     * Update sektor yang ada
     */
    public function update(int $id, array $data): bool {
        $result = $this->wpdb->update(
            $this->table_name,
            [
                'nama' => $data['nama'],
                'singkatan' => $data['singkatan'] ?? null,  // Menambahkan singkatan
                'keterangan' => $data['keterangan'] ?? null,
                'status' => $data['status'] ?? 'active',
                'updated_at' => current_time('mysql')
            ],
            ['id' => $id],
            ['%s', '%s', '%s', '%s', '%s'],  // Menambahkan format untuk singkatan
            ['%d']
        );
    
        return $result !== false;
    }
    /**
     * Hapus sektor
     */
    public function delete(int $id): bool {
        return $this->wpdb->delete(
            $this->table_name,
            ['id' => $id],
            ['%d']
        ) !== false;
    }

    /**
     * Cek apakah nama sudah ada
     */
    public function existsByName(string $nama, ?int $excludeId = null): bool {
        $sql = "SELECT EXISTS (SELECT 1 FROM {$this->table_name} WHERE nama = %s";
        $params = [$nama];

        if ($excludeId) {
            $sql .= " AND id != %d";
            $params[] = $excludeId;
        }

        $sql .= ") as result";

        return (bool) $this->wpdb->get_var($this->wpdb->prepare($sql, $params));
    }

    /**
     * Cek apakah sektor punya groups
     */
    public function hasGroups(int $id): bool {
        return (bool) $this->wpdb->get_var($this->wpdb->prepare("
            SELECT EXISTS (
                SELECT 1 
                FROM {$this->wpdb->prefix}app_groups 
                WHERE service_id = %d
            ) as has_groups",
            $id
        ));
    }

    /**
     * Dapatkan statistik grup
     */
    public function getGroupStats(int $serviceId): array {
        $stats = $this->wpdb->get_row($this->wpdb->prepare("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active
            FROM {$this->wpdb->prefix}app_groups
            WHERE service_id = %d
        ", $serviceId));

        return [
            'total' => (int) ($stats->total ?? 0),
            'active' => (int) ($stats->active ?? 0)
        ];
    }

    /**
     * Dapatkan total sektor
     */
    public function getTotalCount(): int {
        return (int) $this->wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name}");
    }

    /**
     * Dapatkan jumlah sektor aktif
     */
    public function getActiveCount(): int {
        return (int) $this->wpdb->get_var($this->wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table_name} WHERE status = %s",
            'active'
        ));
    }

    /**
     * Dapatkan jumlah sektor dengan grup
     */
    public function getCountWithGroups(): int {
        return (int) $this->wpdb->get_var("
            SELECT COUNT(DISTINCT s.id)
            FROM {$this->table_name} s
            INNER JOIN {$this->wpdb->prefix}app_groups g ON s.id = g.service_id
        ");
    }

    /**
     * Dapatkan sektor terbaru
     */
    public function getRecentServices(int $limit = 5): array {
        return $this->wpdb->get_results($this->wpdb->prepare("
            SELECT s.*, COUNT(g.id) as total_groups
            FROM {$this->table_name} s
            LEFT JOIN {$this->wpdb->prefix}app_groups g ON s.id = g.service_id
            GROUP BY s.id
            ORDER BY s.created_at DESC
            LIMIT %d
        ", $limit));
    }

    // Cache-aware get methods
    public function getAllServices() {
        $services = $this->wpdb->get_results(
            "SELECT * FROM {$this->table_name} WHERE status = 'active' ORDER BY nama ASC"
        );

        return $services;
    }
}
