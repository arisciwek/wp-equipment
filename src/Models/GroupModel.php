<?php
/**
 * Group Model Class
 *
 * @package     WP_Equipment
 * @subpackage  Models
 * @version     1.0.0
 * @author      arisciwek
 *
 * Path: /wp-equipment/src/Models/GroupModel.php
 *
 * Description: Model untuk operasi database grup.
 *              Fokus pada operasi CRUD dan query.
 *              Tanpa cache management (dipindah ke controller).
 */

namespace WPEquipment\Models;

class GroupModel {
    private $wpdb;
    private $table_name;
    private $upload_dir;

    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table_name = $wpdb->prefix . 'app_groups';
        
        // Set upload directory
        $upload_dir = wp_upload_dir();
        $this->upload_dir = $upload_dir['basedir'] . '/wp-equipment/documents';
        
        // Create documents directory if not exists
        if (!file_exists($this->upload_dir)) {
            wp_mkdir_p($this->upload_dir);
            // Protect directory
            $htaccess = "Order deny,allow\nDeny from all";
            @file_put_contents($this->upload_dir . '/.htaccess', $htaccess);
        }
    }

    /**
     * Dapatkan data untuk DataTable
     */
    public function getDataTableData(int $start, int $length, string $search, string $orderBy, string $orderDir): array {
        // Query dasar
        $select = "SELECT g.*, s.nama as service_nama";
        $from = " FROM {$this->table_name} g";
        $join = " LEFT JOIN {$this->wpdb->prefix}app_services s ON g.service_id = s.id";
        $where = " WHERE 1=1";

        // Tambah kondisi pencarian
        if (!empty($search)) {
            $where .= $this->wpdb->prepare(
                " AND (g.nama LIKE %s OR g.keterangan LIKE %s OR s.nama LIKE %s)",
                '%' . $this->wpdb->esc_like($search) . '%',
                '%' . $this->wpdb->esc_like($search) . '%',
                '%' . $this->wpdb->esc_like($search) . '%'
            );
        }

        // Validasi order column
        $validColumns = ['nama', 'service_nama', 'dokumen_type', 'status'];
        if (!in_array($orderBy, $validColumns)) {
            $orderBy = 'nama';
        }

        // Format order
        $orderBy = "g.{$orderBy}";
        if ($orderBy === 'g.service_nama') {
            $orderBy = 's.nama';
        }
        
        $orderDir = strtoupper($orderDir) === 'DESC' ? 'DESC' : 'ASC';
        $order = " ORDER BY {$orderBy} {$orderDir}";

        // Tambah limit
        $limit = $this->wpdb->prepare(" LIMIT %d, %d", $start, $length);

        // Execute query with SQL_CALC_FOUND_ROWS
        $sql = "SELECT SQL_CALC_FOUND_ROWS " . substr($select . $from . $join . $where . $order . $limit, 8);
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
     * Dapatkan grup berdasar ID
     */
    public function find(int $id): ?object {
        return $this->wpdb->get_row($this->wpdb->prepare("
            SELECT g.*, s.nama as service_nama
            FROM {$this->table_name} g
            LEFT JOIN {$this->wpdb->prefix}app_services s ON g.service_id = s.id
            WHERE g.id = %d
        ", $id));
    }

    /**
     * Buat grup baru
     */
    public function create(array $data): ?int {
        // Handle file upload if present
        if (!empty($_FILES['dokumen']['name'])) {
            $file = $_FILES['dokumen'];
            $filename = sanitize_file_name($file['name']);
            $filepath = $this->upload_dir . '/' . $filename;
            
            // Verify file type
            $file_type = wp_check_filetype($filename);
            if (!in_array($file_type['ext'], ['docx', 'odt'])) {
                return null;
            }
            
            // Move uploaded file
            if (!move_uploaded_file($file['tmp_name'], $filepath)) {
                return null;
            }
            
            $data['dokumen_path'] = 'wp-content/uploads/wp-equipment/documents/' . $filename;
            $data['dokumen_type'] = $file_type['ext'];
        }

        $result = $this->wpdb->insert(
            $this->table_name,
            [
                'service_id' => $data['service_id'],
                'nama' => $data['nama'],
                'keterangan' => $data['keterangan'] ?? null,
                'dokumen_path' => $data['dokumen_path'] ?? null,
                'dokumen_type' => $data['dokumen_type'] ?? null,
                'status' => $data['status'] ?? 'active',
                'created_by' => get_current_user_id(),
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            ],
            [
                '%d', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s'
            ]
        );

        return $result ? $this->wpdb->insert_id : null;
    }

    /**
     * Update grup yang ada
     */
    public function update(int $id, array $data): bool {
        // Get existing data
        $existing = $this->find($id);
        if (!$existing) {
            return false;
        }

        // Handle file upload if present
        if (!empty($_FILES['dokumen']['name'])) {
            $file = $_FILES['dokumen'];
            $filename = sanitize_file_name($file['name']);
            $filepath = $this->upload_dir . '/' . $filename;
            
            // Verify file type
            $file_type = wp_check_filetype($filename);
            if (!in_array($file_type['ext'], ['docx', 'odt'])) {
                return false;
            }
            
            // Delete old file if exists
            if ($existing->dokumen_path) {
                $old_file = ABSPATH . $existing->dokumen_path;
                if (file_exists($old_file)) {
                    @unlink($old_file);
                }
            }
            
            // Move uploaded file
            if (!move_uploaded_file($file['tmp_name'], $filepath)) {
                return false;
            }
            
            $data['dokumen_path'] = 'wp-content/uploads/wp-equipment/documents/' . $filename;
            $data['dokumen_type'] = $file_type['ext'];
        }

        $result = $this->wpdb->update(
            $this->table_name,
            [
                'service_id' => $data['service_id'],
                'nama' => $data['nama'],
                'keterangan' => $data['keterangan'] ?? null,
                'dokumen_path' => $data['dokumen_path'] ?? $existing->dokumen_path,
                'dokumen_type' => $data['dokumen_type'] ?? $existing->dokumen_type,
                'status' => $data['status'] ?? 'active',
                'updated_at' => current_time('mysql')
            ],
            ['id' => $id],
            [
                '%d', '%s', '%s', '%s', '%s', '%s', '%s'
            ],
            ['%d']
        );

        return $result !== false;
    }

    /**
     * Hapus grup
     */
    public function delete(int $id): bool {
        // Get existing data
        $existing = $this->find($id);
        if (!$existing) {
            return false;
        }

        // Delete associated file if exists
        if ($existing->dokumen_path) {
            $file_path = ABSPATH . $existing->dokumen_path;
            if (file_exists($file_path)) {
                @unlink($file_path);
            }
        }

        return $this->wpdb->delete(
            $this->table_name,
            ['id' => $id],
            ['%d']
        ) !== false;
    }

    /**
     * Cek apakah nama sudah ada dalam sektor yang sama
     */
    public function existsByNameInService(string $nama, int $service_id, ?int $excludeId = null): bool {
        $sql = "SELECT EXISTS (
            SELECT 1 FROM {$this->table_name} 
            WHERE nama = %s AND service_id = %d";
        $params = [$nama, $service_id];

        if ($excludeId) {
            $sql .= " AND id != %d";
            $params[] = $excludeId;
        }

        $sql .= ") as result";

        return (bool) $this->wpdb->get_var($this->wpdb->prepare($sql, $params));
    }

    /**
     * Dapatkan total grup
     */
    public function getTotalCount(): int {
        return (int) $this->wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name}");
    }

    /**
     * Dapatkan jumlah grup aktif
     */
    public function getActiveCount(): int {
        return (int) $this->wpdb->get_var($this->wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table_name} WHERE status = %s",
            'active'
        ));
    }

    /**
     * Dapatkan jumlah grup per sektor
     */
    public function getCountByService(int $service_id): int {
        return (int) $this->wpdb->get_var($this->wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table_name} WHERE service_id = %d",
            $service_id
        ));
    }

    /**
     * Dapatkan grup terbaru
     */
    public function getRecentGroups(int $limit = 5): array {
        return $this->wpdb->get_results($this->wpdb->prepare("
            SELECT g.*, s.nama as service_nama
            FROM {$this->table_name} g
            LEFT JOIN {$this->wpdb->prefix}app_services s ON g.service_id = s.id
            ORDER BY g.created_at DESC
            LIMIT %d
        ", $limit));
    }

    /**
     * Dapatkan grup berdasarkan sektor
     */
    public function getByService(int $service_id): array {
        return $this->wpdb->get_results($this->wpdb->prepare("
            SELECT * FROM {$this->table_name}
            WHERE service_id = %d AND status = 'active'
            ORDER BY nama ASC
        ", $service_id));
    }
}
