<?php
/**
 * Equipment Demo Data Generator
 *
 * @package     WP_Equipment
 * @subpackage  Database/Demo
 * @version     1.0.0
 * @author      arisciwek
 *
 * Path: /wp-equipment/src/Database/Demo/Data/EquipmentDemoData.php
 * 
 * Description: Generate equipment demo data dengan:
 *              - Data perusahaan dengan format yang valid
 *              - Integrasi dengan WordPress user
 *              - Data wilayah dari Provinces/Regencies
 *              - Validasi dan tracking data unik
 */

namespace WPEquipment\Database\Demo;

use WPEquipment\Database\Demo\Data\EquipmentUsersData;
use WPEquipment\Database\Demo\Data\BranchUsersData;

defined('ABSPATH') || exit;

class EquipmentDemoData extends AbstractDemoData {
    use EquipmentDemoDataHelperTrait;

    private static $equipment_ids = [];
    private static $user_ids = [];
    private static $used_emails = [];
    public $used_names = [];
    public $used_npwp = [];
    public $used_nib = [];
    protected $equipment_users = [];


    // Data statis equipment
    private static $equipments = [
        ['id' => 1, 'name' => 'PT Maju Bersama', 'provinsi_id' => '16', 'regency_id' => '34'],
        ['id' => 2, 'name' => 'CV Teknologi Nusantara'],
        ['id' => 3, 'name' => 'PT Sinar Abadi'],
        ['id' => 4, 'name' => 'PT Global Teknindo'],
        ['id' => 5, 'name' => 'CV Mitra Solusi'],
        ['id' => 6, 'name' => 'PT Karya Digital'],
        ['id' => 7, 'name' => 'PT Bumi Perkasa'],
        ['id' => 8, 'name' => 'CV Cipta Kreasi'],
        ['id' => 9, 'name' => 'PT Meta Inovasi'],
        ['id' => 10, 'name' => 'PT Delta Sistem']
    ];

    /**
     * Constructor to initialize properties
     */
    public function __construct() {
        parent::__construct();
        $this->equipment_users = EquipmentUsersData::$data;
    }

    /**
     * Validasi sebelum generate data
     */
    protected function validate(): bool {
        try {
            // Validasi tabel provinces & regencies
            $provinces_exist = $this->wpdb->get_var(
                "SHOW TABLES LIKE '{$this->wpdb->prefix}wi_provinces'"
            );
            if (!$provinces_exist) {
                throw new \Exception('Tabel provinces tidak ditemukan');
            }

            // Get equipment users mapping
            if (empty($this->equipment_users)) {
                throw new \Exception('Equipment users not found');
            }

            $regencies_exist = $this->wpdb->get_var(
                "SHOW TABLES LIKE '{$this->wpdb->prefix}wi_regencies'"
            );
            if (!$regencies_exist) {
                throw new \Exception('Tabel regencies tidak ditemukan');
            }

            // Cek data provinces & regencies tersedia
            $province_count = $this->wpdb->get_var(
                "SELECT COUNT(*) FROM {$this->wpdb->prefix}wi_provinces"
            );
            if ($province_count == 0) {
                throw new \Exception('Data provinces kosong');
            }

            $regency_count = $this->wpdb->get_var(
                "SELECT COUNT(*) FROM {$this->wpdb->prefix}wi_regencies"
            );
            if ($regency_count == 0) {
                throw new \Exception('Data regencies kosong');
            }

            return true;

        } catch (\Exception $e) {
            $this->debug('Validation failed: ' . $e->getMessage());
            return false;
        }
    }

    protected function generate(): void {
        if (!$this->isDevelopmentMode()) {
            $this->debug('Cannot generate data - not in development mode');
            return;
        }

        // Inisialisasi WPUserGenerator dan simpan reference ke static data
        $userGenerator = new WPUserGenerator();

        foreach (self::$equipments as $equipment) {
            try {
                // 1. Cek existing equipment
                $existing_equipment = $this->wpdb->get_row(
                    $this->wpdb->prepare(
                        "SELECT c.* FROM {$this->wpdb->prefix}app_equipments c 
                         INNER JOIN {$this->wpdb->users} u ON c.user_id = u.ID 
                         WHERE c.id = %d",
                        $equipment['id']
                    )
                );

                if ($existing_equipment) {
                    if ($this->shouldClearData()) {
                        // Delete existing equipment if shouldClearData is true
                        $this->wpdb->delete(
                            $this->wpdb->prefix . 'app_equipments',
                            ['id' => $equipment['id']],
                            ['%d']
                        );
                        $this->debug("Deleted existing equipment with ID: {$equipment['id']}");
                    } else {
                        $this->debug("Equipment exists with ID: {$equipment['id']}, skipping...");
                        continue;
                    }
                }

                // 2. Cek dan buat WP User jika belum ada
                $wp_user_id = 1 + $equipment['id'];  // Sesuai dengan indeks di EquipmentUsersData
                
                // Ambil data user dari static array
                $user_data = $this->equipment_users[$equipment['id'] - 1];
                $user_id = $userGenerator->generateUser([
                    'id' => $user_data['id'],
                    'username' => $user_data['username'],
                    'display_name' => $user_data['display_name'],
                    'role' => 'equipment'
                ]);

                if (!$user_id) {
                    throw new \Exception("Failed to create WordPress user for equipment: {$equipment['name']}");
                }

                // Store user_id untuk referensi
                self::$user_ids[$equipment['id']] = $wp_user_id;

                // 3. Generate equipment data baru
                if (isset($equipment['provinsi_id'])) {
                    $provinsi_id = (int)$equipment['provinsi_id'];
                    // Pastikan regency sesuai dengan provinsi ini
                    $regency_id = isset($equipment['regency_id']) ? 
                        (int)$equipment['regency_id'] : 
                        $this->getRandomRegencyId($provinsi_id);
                } else {
                    // Get random valid province-regency pair
                    $provinsi_id = $this->getRandomProvinceId();
                    $regency_id = $this->getRandomRegencyId($provinsi_id);
                }

                // Validate location relationship
                if (!$this->validateLocation($provinsi_id, $regency_id)) {
                    throw new \Exception("Invalid province-regency relationship: Province {$provinsi_id}, Regency {$regency_id}");
                }

                if ($this->shouldClearData()) {
                    // Delete existing equipment if user WP not  exists
                    $this->wpdb->delete(
                        $this->wpdb->prefix . 'app_equipments',
                        ['id' => $equipment['id']],
                        ['%d']
                    );
                    
                    $this->debug("Deleted existing equipment with ID: {$equipment['id']}");
                }

                // Prepare equipment data according to schema
                $equipment_data = [
                    'id' => $equipment['id'],
                    'code' => $this->equipmentModel->generateEquipmentCode(),
                    'name' => $equipment['name'],
                    'npwp' => $this->generateNPWP(),
                    'nib' => $this->generateNIB(),
                    'status' => 'active',
                    'provinsi_id' => $provinsi_id ?: null,
                    'regency_id' => $regency_id ?: null,
                    'user_id' => $wp_user_id,
                    'created_by' => 1,
                    'created_at' => current_time('mysql'),
                    'updated_at' => current_time('mysql')
                ];

                // Use createDemoData instead of create
                if (!$this->equipmentModel->createDemoData($equipment_data)) {
                    throw new \Exception("Failed to create equipment with fixed ID");
                }

                // Track equipment ID
                self::$equipment_ids[] = $equipment['id'];

                $this->debug("Created equipment: {$equipment['name']} with fixed ID: {$equipment['id']} and WP User ID: {$wp_user_id}");

            } catch (\Exception $e) {
                $this->debug("Error processing equipment {$equipment['name']}: " . $e->getMessage());
                throw $e;
            }
        }

        // Add cache handling after bulk generation
        foreach (self::$equipment_ids as $equipment_id) {
            $this->cache->invalidateEquipmentCache($equipment_id);
            $this->cache->delete('equipment_total_count', get_current_user_id());
            $this->cache->invalidateDataTableCache('equipment_list');
        }

        // Reset auto_increment
        $this->wpdb->query(
            "ALTER TABLE {$this->wpdb->prefix}app_equipments AUTO_INCREMENT = 211"
        );
    }

    /**
     * Get array of generated equipment IDs
     */
    public function getEquipmentIds(): array {
        return self::$equipment_ids;
    }

    /**
     * Get array of generated user IDs
     */
    public function getUserIds(): array {
        return self::$user_ids;
    }
}
