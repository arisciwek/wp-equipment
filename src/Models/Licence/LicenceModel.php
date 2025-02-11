<?php
/**
 * Licence Model Class
 *
 * @package     WP_Equipment
 * @subpackage  Models/Licence
 * @version     1.0.0
 * @author      arisciwek
 *
 * Path: /wp-equipment/src/Models/Licence/LicenceModel.php
 *
 * Description: Model untuk mengelola data surat keterangan di database.
 *              Handles operasi CRUD dengan caching terintegrasi.
 *              Includes query optimization dan data formatting.
 *              Menyediakan metode untuk DataTables server-side.
 *
 * Changelog:
 * 1.0.0 - 2024-12-10
 * - Initial implementation
 * - Added core CRUD operations
 * - Added DataTables integration
 * - Added cache support
 */

namespace WPEquipment\Models\Licence;

class LicenceModel {
    private $table;
    private $equipment_table;

    public function __construct() {
        global $wpdb;
        $this->table = $wpdb->prefix . 'app_licences';
        $this->equipment_table = $wpdb->prefix . 'app_equipments';
    }

    public function create(array $data): ?int {
        global $wpdb;

        $result = $wpdb->insert(
            $this->table,
            [
                'equipment_id' => $data['equipment_id'],
                'code' => $data['code'],
                'name' => $data['name'],
                'type' => $data['type'],
                'created_by' => get_current_user_id(),
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            ],
            ['%d', '%s', '%s', '%s', '%d', '%s', '%s']
        );

        if ($result === false) {
            return null;
        }

        return (int) $wpdb->insert_id;
    }

    public function find(int $id): ?object {
        global $wpdb;

        return $wpdb->get_row($wpdb->prepare("
            SELECT r.*, p.name as equipment_name
            FROM {$this->table} r
            LEFT JOIN {$this->equipment_table} p ON r.equipment_id = p.id
            WHERE r.id = %d
        ", $id));
    }

    public function update(int $id, array $data): bool {
        global $wpdb;

        $updateData = array_merge($data, ['updated_at' => current_time('mysql')]);
        $format = [];

        // Add format for each field
        if (isset($data['code'])) $format[] = '%d';
        if (isset($data['name'])) $format[] = '%s';
        if (isset($data['type'])) $format[] = '%s';
        $format[] = '%s'; // for updated_at

        $result = $wpdb->update(
            $this->table,
            $updateData,
            ['id' => $id],
            $format,
            ['%d']
        );

        return $result !== false;
    }

    public function delete(int $id): bool {
        global $wpdb;

        return $wpdb->delete(
            $this->table,
            ['id' => $id],
            ['%d']
        ) !== false;
    }
    public function existsByCode(string $code): bool {
        global $wpdb;
        return (bool) $wpdb->get_var($wpdb->prepare(
            "SELECT EXISTS (SELECT 1 FROM {$this->table} WHERE code = %s) as result",
            $code
        ));
    }

    public function existsByNameInEquipment(string $name, int $equipment_id, ?int $excludeId = null): bool {
        global $wpdb;

        $sql = "SELECT EXISTS (SELECT 1 FROM {$this->table}
                WHERE name = %s AND equipment_id = %d";
        $params = [$name, $equipment_id];

        if ($excludeId) {
            $sql .= " AND id != %d";
            $params[] = $excludeId;
        }

        $sql .= ") as result";

        return (bool) $wpdb->get_var($wpdb->prepare($sql, $params));
    }

    public function getDataTableData(int $equipment_id, int $start, int $length, string $search, string $orderColumn, string $orderDir): array {
        global $wpdb;

        // Base query parts
        $select = "SELECT SQL_CALC_FOUND_ROWS r.*, p.name as equipment_name";
        $from = " FROM {$this->table} r";
        $join = " LEFT JOIN {$this->equipment_table} p ON r.equipment_id = p.id";
        $where = " WHERE r.equipment_id = %d";
        $params = [$equipment_id];

        // Add search if provided
        if (!empty($search)) {
            $where .= " AND r.name LIKE %s";
            $params[] = '%' . $wpdb->esc_like($search) . '%';
        }

        // Validate order column
        $validColumns = ['code', 'name', 'type'];
        if (!in_array($orderColumn, $validColumns)) {
            $orderColumn = 'code';
        }

        // Validate order direction
        $orderDir = strtoupper($orderDir) === 'DESC' ? 'DESC' : 'ASC';

        // Build order clause
        $order = " ORDER BY " . esc_sql($orderColumn) . " " . esc_sql($orderDir);

        // Add limit
        $limit = $wpdb->prepare(" LIMIT %d, %d", $start, $length);

        // Complete query
        $sql = $select . $from . $join . $where . $order . $limit;

        // Get paginated results
        $results = $wpdb->get_results($wpdb->prepare($sql, $params));

        if ($results === null) {
            throw new \Exception($wpdb->last_error);
        }

        // Get total filtered count
        $filtered = $wpdb->get_var("SELECT FOUND_ROWS()");

        // Get total count for equipment
        $total = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table} WHERE equipment_id = %d",
            $equipment_id
        ));

        return [
            'data' => $results,
            'total' => (int) $total,
            'filtered' => (int) $filtered
        ];
    }

    public function getTotalCount(): int {
        global $wpdb;
        return (int) $wpdb->get_var("SELECT COUNT(*) FROM {$this->table}");
    }
}
