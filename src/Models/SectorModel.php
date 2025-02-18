<?php
/**
 * Sector Model Class
 *
 * @package     WP_Equipment
 * @subpackage  Models
 * @version     1.0.0
 * @author      arisciwek
 *
 * Path: /wp-equipment/src/Models/SectorModel.php
 *
 * Description: Model untuk operasi database sektor.
 *              Fokus pada operasi CRUD dan query.
 *              Tanpa cache management (dipindah ke controller).
 */

namespace WPEquipment\Models;

class SectorModel {
    private $wpdb;
    private $table_name;

    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table_name = $wpdb->prefix . 'app_sectors';
    }

    /**
     * Dapatkan data untuk DataTable
     */
    public function getDataTableData(int $start, int $length, string $search, string $orderBy, string $orderDir): array {
        // Query dasar
        $select = "SELECT s.*, COUNT(g.id) as total_groups";
        $from = " FROM {$this->table_name} s";
        $join = " LEFT JOIN {$this->wpdb->prefix}app_groups g ON s.id = g.sector_id";
        $where = " WHERE 1=1";
        $group = " GROUP BY s.id";

        // Tambah kondisi pencarian
        if (!empty($search)) {
            $where .= $this->wpdb->prepare(
                " AND (s.nama LIKE %s OR s.keterangan LIKE %s)",
                '%' . $this->wpdb->esc_like($search) . '%',
                '%' . $this->wpdb->esc_like($search) . '%'
            );
        }

        // Validasi order column
        $validColumns = ['nama', 'keterangan', 'status', 'total_groups'];
        if (!in_array($orderBy, $validColumns)) {
            $orderBy = 'nama';
        }

        // Format order
        $orderBy = "s.{$orderBy}";
        if ($orderBy === 's.total_groups') {
            $orderBy = 'total_groups';
        }
        
        $orderDir = strtoupper($orderDir) === 'DESC' ? 'DESC' : 'ASC';
        $order = " ORDER BY {$orderBy} {$orderDir}";

        // Tambah limit
        $limit = $this->wpdb->prepare(" LIMIT %d, %d", $start, $length);

        // Execute query with SQL_CALC_FOUND_ROWS
        $sql = "SELECT SQL_CALC_FOUND_ROWS " . substr($select . $from . $join . $where . $group . $order . $limit, 8);
        $results = $this->wpdb->get_results($sql);

        // Get total dan filtered counts
        $total = $this->wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name}");
        $filtered = $this->wpdb->get_var("SELECT FOUND_ROWS()");

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
            LEFT JOIN {$this->wpdb->prefix}app_groups g ON s.id = g.sector_id
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
                'keterangan' => $data['keterangan'] ?? null,
                'status' => $data['status'] ?? 'active',
                'created_by' => get_current_user_id(),
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            ],
            ['%s', '%s', '%s', '%d', '%s', '%s']
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
                'keterangan' => $data['keterangan'] ?? null,
                'status' => $data['status'] ?? 'active',
                'updated_at' => current_time('mysql')
            ],
            ['id' => $id],
            ['%s', '%s', '%s', '%s'],
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
                WHERE sector_id = %d
            ) as has_groups",
            $id
        ));
    }

    /**
     * Dapatkan statistik grup
     */
    public function getGroupStats(int $sectorId): array {
        $stats = $this->wpdb->get_row($this->wpdb->prepare("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active
            FROM {$this->wpdb->prefix}app_groups
            WHERE sector_id = %d
        ", $sectorId));

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
            INNER JOIN {$this->wpdb->prefix}app_groups g ON s.id = g.sector_id
        ");
    }

    /**
     * Dapatkan sektor terbaru
     */
    public function getRecentSectors(int $limit = 5): array {
        return $this->wpdb->get_results($this->wpdb->prepare("
            SELECT s.*, COUNT(g.id) as total_groups
            FROM {$this->table_name} s
            LEFT JOIN {$this->wpdb->prefix}app_groups g ON s.id = g.sector_id
            GROUP BY s.id
            ORDER BY s.created_at DESC
            LIMIT %d
        ", $limit));
    }

    // Cache-aware get methods
    public function getAllSectors() {
        $sectors = $this->wpdb->get_results(
            "SELECT * FROM {$this->table_name} WHERE status = 'active' ORDER BY nama ASC"
        );

        return $sectors;
    }
}
