<?php
/**
 * Demo Data Generator
 *
 * @package     WP_Equipment
 * @subpackage  Database
 * @version     1.0.0
 * @author      arisciwek
 *
 * Path: /wp-equipment/src/Database/Demo_Data.php
 *
 * Description: Menyediakan data demo untuk testing.
 *              Includes generator untuk equipments, licencees, dan employees.
 *              Menggunakan transaction untuk data consistency.
 *              Generates realistic Indonesian names dan alamat.
 *
 * Generated Data:
 * - 10 Equipment records with unique codes
 * - 30 Branch records (3 per equipment)
 * - 20 Employee records with departments
 *
 * Changelog:
 * 1.0.0 - 2024-01-07
 * - Initial version
 * - Added equipment demo data
 * - Added licence demo data
 * - Added employee demo data
 */
namespace WPEquipment\Database;

defined('ABSPATH') || exit;

class Demo_Data {
    private static function clear_tables() {
        global $wpdb;
        
        // Delete in correct order (child tables first)
        $wpdb->query("DELETE FROM {$wpdb->prefix}app_equipment_employees");
        $wpdb->query("DELETE FROM {$wpdb->prefix}app_licences");
        $wpdb->query("DELETE FROM {$wpdb->prefix}app_equipments");
    }

    public static function load() {
        global $wpdb;

        try {
            // Start transaction
            $wpdb->query('START TRANSACTION');

            // Clear existing data first
            self::clear_tables();

            // Demo equipments (10 records)
            $equipments = [
                ['code' => '01', 'name' => 'PT Maju Bersama', 'created_by' => 1],
                ['code' => '02', 'name' => 'CV Teknologi Nusantara', 'created_by' => 1],
                ['code' => '03', 'name' => 'PT Sinar Abadi', 'created_by' => 1],
                ['code' => '04', 'name' => 'PT Global Teknindo', 'created_by' => 1],
                ['code' => '05', 'name' => 'CV Mitra Solusi', 'created_by' => 1],
                ['code' => '06', 'name' => 'PT Karya Digital', 'created_by' => 1],
                ['code' => '07', 'name' => 'PT Bumi Perkasa', 'created_by' => 1],
                ['code' => '08', 'name' => 'CV Cipta Kreasi', 'created_by' => 1],
                ['code' => '09', 'name' => 'PT Meta Inovasi', 'created_by' => 1],
                ['code' => '10', 'name' => 'PT Delta Sistem', 'created_by' => 1]
            ];

            $equipment_ids = [];
            foreach ($equipments as $equipment) {
                $wpdb->insert($wpdb->prefix . 'app_equipments', $equipment);
                if ($wpdb->last_error) throw new \Exception($wpdb->last_error);
                $equipment_ids[] = $wpdb->insert_id;
            }

            // Demo licencees (30 records)
            $licence_types = ['pertama', 'berkala'];
            $licence_data = [];
            
            foreach ($equipment_ids as $equipment_id) {
                // Each equipment gets 3 licencees
                $equipment_code = str_pad($equipment_id, 2, '0', STR_PAD_LEFT);
                for ($i = 1; $i <= 3; $i++) {
                    $licence_data[] = [
                        'equipment_id' => $equipment_id,
                        'code' => $equipment_code . str_pad($i, 2, '0', STR_PAD_LEFT),
                        'name' => "Surat Keterangan " . self::generateCityName() . " " . $i,
                        'type' => $licence_types[array_rand($licence_types)],
                        'created_by' => 1
                    ];
                }
            }

            foreach ($licence_data as $licence) {
                $wpdb->insert($wpdb->prefix . 'app_licences', $licence);
                if ($wpdb->last_error) throw new \Exception($wpdb->last_error);
            }

            // Demo employees (20 records)
            $positions = ['Manager', 'Supervisor', 'Staff', 'Admin', 'Coordinator'];
            $departments = ['Sales', 'Operations', 'Finance', 'IT', 'HR'];
            $employee_data = [];

            for ($i = 1; $i <= 20; $i++) {
                $random_equipment = $equipment_ids[array_rand($equipment_ids)];
                // Get random licence for this equipment
                $licence_id = $wpdb->get_var($wpdb->prepare(
                    "SELECT id FROM {$wpdb->prefix}app_licences WHERE equipment_id = %d ORDER BY RAND() LIMIT 1",
                    $random_equipment
                ));

                $employee_data[] = [
                    'equipment_id' => $random_equipment,
                    'licence_id' => $licence_id,
                    'name' => self::generatePersonName(),
                    'position' => $positions[array_rand($positions)],
                    'department' => $departments[array_rand($departments)],
                    'email' => "employee{$i}@example.com",
                    'phone' => '08' . rand(100000000, 999999999),
                    'created_by' => 1,
                    'status' => 'active'
                ];
            }

            foreach ($employee_data as $employee) {
                $wpdb->insert($wpdb->prefix . 'app_equipment_employees', $employee);
                if ($wpdb->last_error) throw new \Exception($wpdb->last_error);
            }

            $wpdb->query('COMMIT');
            return true;

        } catch (\Exception $e) {
            $wpdb->query('ROLLBACK');
            error_log('Demo data insertion failed: ' . $e->getMessage());
            return false;
        }
    }

    private static function generateCityName() {
        $cities = [
            'Jakarta', 'Surabaya', 'Bandung', 'Medan', 'Semarang',
            'Makassar', 'Palembang', 'Tangerang', 'Depok', 'Bekasi',
            'Malang', 'Bogor', 'Yogyakarta', 'Solo', 'Manado'
        ];
        return $cities[array_rand($cities)];
    }

    private static function generatePersonName() {
        $firstNames = [
            'Budi', 'Siti', 'Andi', 'Dewi', 'Rudi',
            'Nina', 'Joko', 'Rita', 'Doni', 'Sari',
            'Agus', 'Lina', 'Hadi', 'Maya', 'Eko'
        ];
        $lastNames = [
            'Susanto', 'Wijaya', 'Kusuma', 'Pratama', 'Sanjaya',
            'Hidayat', 'Nugraha', 'Putra', 'Dewi', 'Santoso',
            'Wibowo', 'Saputra', 'Permana', 'Utama', 'Suryadi'
        ];
        return $firstNames[array_rand($firstNames)] . ' ' . $lastNames[array_rand($lastNames)];
    }
}
