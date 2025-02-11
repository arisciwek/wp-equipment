<?php
/**
 * Equipment Employee Demo Data Generator
 *
 * @package     WP_Equipment
 * @subpackage  Database/Demo
 * @version     1.0.0
 * @author      arisciwek
 * 
 * Path: /wp-equipment/src/Database/Demo/EquipmentEmployeeDemoData.php
 */

namespace WPEquipment\Database\Demo;

use WPEquipment\Database\Demo\Data\EquipmentEmployeeUsersData;
use WPEquipment\Models\Employee\EquipmentEmployeeModel;

defined('ABSPATH') || exit;

class EquipmentEmployeeDemoData extends AbstractDemoData {
    use EquipmentDemoDataHelperTrait;

    private $employeeModel;
    private $wpUserGenerator;
    private static $employee_users;

    public function __construct() {
        parent::__construct();
        $this->employeeModel = new EquipmentEmployeeModel();
        $this->wpUserGenerator = new WPUserGenerator();
        self::$employee_users = EquipmentEmployeeUsersData::$data;
    }

    protected function validate(): bool {
        try {
            // 1. Validasi table exists
            $table_exists = $this->wpdb->get_var(
                "SHOW TABLES LIKE '{$this->wpdb->prefix}app_equipment_employees'"
            );
            
            if (!$table_exists) {
                throw new \Exception('Employee table does not exist');
            }

            // 2. Validasi equipment & licence data exists
            $equipment_count = $this->wpdb->get_var(
                "SELECT COUNT(*) FROM {$this->wpdb->prefix}app_equipments"
            );
            
            if ($equipment_count == 0) {
                throw new \Exception('No equipments found - please generate equipment data first');
            }

            $licence_count = $this->wpdb->get_var(
                "SELECT COUNT(*) FROM {$this->wpdb->prefix}app_licencees"
            );
            
            if ($licence_count == 0) {
                throw new \Exception('No licencees found - please generate licence data first');
            }

            // 3. Validasi static data untuk employee users
            if (empty(self::$employee_users)) {
                throw new \Exception('Employee users data not found');
            }

            return true;

        } catch (\Exception $e) {
            $this->debug('Validation failed: ' . $e->getMessage());
            return false;
        }
    }

    protected function generate(): void {
        $this->debug('Starting employee data generation');

        try {
            // Clear existing data if in development mode
            if ($this->shouldClearData()) {
                $this->wpdb->query("DELETE FROM {$this->wpdb->prefix}app_equipment_employees");
                $this->debug('Cleared existing employee data');
            }

            // Tahap 1: Generate dari user yang sudah ada (equipment owners & licence admins)
            $this->generateExistingUserEmployees();

            // Tahap 2: Generate dari EquipmentEmployeeUsersData
            $this->generateNewEmployees();

            $this->debug('Employee generation completed');

        } catch (\Exception $e) {
            $this->debug('Error generating employees: ' . $e->getMessage());
            throw $e;
        }
    }

    private function generateExistingUserEmployees(): void {

		// For equipment owners (ID 2-11)
		for ($id = 2; $id <= 11; $id++) {
		    $equipment = $this->wpdb->get_row($this->wpdb->prepare(
		        "SELECT * FROM {$this->wpdb->prefix}app_equipments WHERE user_id = %d",
		        $id
		    ));

		    if (!$equipment) continue;

		    // Ambil licence pusat untuk assign owner
		    $pusat_licence = $this->wpdb->get_row($this->wpdb->prepare(
		        "SELECT * FROM {$this->wpdb->prefix}app_licencees 
		         WHERE equipment_id = %d AND type = 'pusat'",
		        $equipment->id
		    ));

		    if (!$pusat_licence) continue;

		    // Create employee record for owner di licence pusat
		    $this->createEmployeeRecord(
		        $equipment->id,
		        $pusat_licence->id,
		        $equipment->user_id,
		        [
		            'finance' => true,
		            'operation' => true,
		            'legal' => true,
		            'purchase' => true
		        ]
		    );
		} 
		
        // 2. Licence admins (ID 12-41)
        for ($id = 12; $id <= 41; $id++) {
            $licence = $this->wpdb->get_row($this->wpdb->prepare(
                "SELECT * FROM {$this->wpdb->prefix}app_licencees WHERE user_id = %d",
                $id
            ));

            if (!$licence) continue;

            // Licence admin gets all department access for their licence
            $this->createEmployeeRecord(
                $licence->equipment_id,
                $licence->id,
                $licence->user_id,
                [
                    'finance' => true,
                    'operation' => true,
                    'legal' => true,
                    'purchase' => true
                ]
            );
        }
    }

    private function generateNewEmployees(): void {
        foreach (self::$employee_users as $user_data) {
            // Generate WordPress user first
            $user_id = $this->wpUserGenerator->generateUser([
                'id' => $user_data['id'],
                'username' => $user_data['username'],
                'display_name' => $user_data['display_name'],
                'role' => $user_data['role']
            ]);

            if (!$user_id) {
                $this->debug("Failed to create WP user: {$user_data['username']}");
                continue;
            }

            // Create employee record with department assignments
            $this->createEmployeeRecord(
                $user_data['equipment_id'],
                $user_data['licence_id'],
                $user_id,
                $user_data['departments']
            );
        }
    }

private function createEmployeeRecord(
    int $equipment_id, 
    int $licence_id, 
    int $user_id, 
    array $departments
): void {
    try {
        $wp_user = get_userdata($user_id);
        if (!$wp_user) {
            throw new \Exception("WordPress user not found: {$user_id}");
        }

        $keterangan = [];
        if ($user_id >= 2 && $user_id <= 11) $keterangan[] = 'Admin Pusat';
        if ($user_id >= 12 && $user_id <= 41) $keterangan[] = 'Admin Licence';
        if ($departments['finance']) $keterangan[] = 'Finance'; 
        if ($departments['operation']) $keterangan[] = 'Operation';
        if ($departments['legal']) $keterangan[] = 'Legal';
        if ($departments['purchase']) $keterangan[] = 'Purchase';

        $employee_data = [
            'equipment_id' => $equipment_id,
            'licence_id' => $licence_id,
            'user_id' => $user_id,
            'name' => $wp_user->display_name,
            'position' => 'Staff',
            'email' => $wp_user->user_email,
            'phone' => $this->generatePhone(),
            'finance' => $departments['finance'] ?? false,
            'operation' => $departments['operation'] ?? false,
            'legal' => $departments['legal'] ?? false,
            'purchase' => $departments['purchase'] ?? false,
            'keterangan' => implode(', ', $keterangan),
            'created_by' => 1,
            'status' => 'active'
        ];

            $result = $this->wpdb->insert(
                $this->wpdb->prefix . 'app_equipment_employees',
                $employee_data
            );

            if ($result === false) {
                throw new \Exception($this->wpdb->last_error);
            }

            $this->debug("Created employee record for: {$wp_user->display_name}");

        } catch (\Exception $e) {
            $this->debug("Error creating employee record: " . $e->getMessage());
            throw $e;
        }
    }

    private function generatePhone(): string {
        return sprintf('08%d', rand(100000000, 999999999));
    }
}

