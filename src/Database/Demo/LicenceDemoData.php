<?php
/**
 * Licence Demo Data Generator
 *
 * @package     WP_Equipment
 * @subpackage  Database/Demo
 * @version     1.0.0
 * @author      arisciwek
 *
 * Path: /wp-equipment/src/Database/Demo/BranchDemoData.php
 *
 * Description: Generate licence demo data dengan:
 *              - Kantor pusat (type = pusat) untuk setiap equipment
 *              - Licence (type = cabang) dengan lokasi yang berbeda
 *              - Format kode sesuai LicenceModel::generateBranchCode()
 *              - Data lokasi dari wi_provinces dan wi_regencies
 *              - Licence memiliki 1 kantor pusat dan 1-2 cabang
 *              - Location data terintegrasi dengan trait
 *              - Tracking unique values (NITKU, email)
 *              - Error handling dan validasi
 *
 * Dependencies:
 * - AbstractDemoData                : Base class untuk demo data generation
 * - EquipmentDemoDataHelperTrait     : Shared helper methods
 * - EquipmentModel                   : Get equipment data
 * - LicenceModel                     : Generate licence code & save data
 * - WP Database (wi_provinces, wi_regencies)
 * 
 * Database Design:
 * - app_licencees
 *   * id             : Primary key
 *   * equipment_id    : Foreign key ke equipment
 *   * code           : Format Format kode: TTTT-RRXxRRXx-RR (13 karakter)
 *   *                  TTTT-RRXxRRXx adalah kode equipment (12 karakter) 
 *   *                  Tanda hubung '-' (1 karakter)
 *   *                  RR adalah 2 digit random number
 *   * name           : Nama licence
 *   * type           : enum('cabang','pusat')
 *   * nitku          : Nomor Identitas Tempat Kegiatan Usaha
 *   * provinsi_id    : Foreign key ke wi_provinces
 *   * regency_id     : Foreign key ke wi_regencies
 *   * user_id        : Foreign key ke wp_users
 *   * status         : enum('active','inactive')
 *
 * Usage Example:
 * ```php 
 * $licenceDemo = new LicenceDemoData($equipment_ids, $user_ids);
 * $licenceDemo->run();
 * $licence_ids = $licenceDemo->getBranchIds();
 * ```
 *
 * Order of operations:
 * 1. Validate equipment_ids dan user_ids
 * 2. Validate provinces & regencies tables
 * 3. Generate pusat licence setiap equipment
 * 4. Generate cabang licencees (1-2 per equipment)
 * 5. Track generated licence IDs
 *
 * Changelog:
 * 1.0.0 - 2024-01-27
 * - Initial version
 * - Added integration with wi_provinces and wi_regencies
 * - Added location validation and tracking
 * - Added documentation and usage examples
 */

namespace WPEquipment\Database\Demo;

use WPEquipment\Database\Demo\Data\LicenceUsersData;

defined('ABSPATH') || exit;

class LicenceDemoData extends AbstractDemoData {
    use EquipmentDemoDataHelperTrait;

    private $licence_ids = [];
    private $used_nitku = [];
    private $used_emails = [];
    private $equipment_ids;
    private $user_ids;
    protected $licence_users = [];

    // Format nama licence
    private static $licencees = [
        ['id' => 1, 'name' => '%s Kantor Pusat'],       // Kantor Pusat
        ['id' => 2, 'name' => '%s Licence %s'],         // Licence Regional
        ['id' => 3, 'name' => '%s Licence %s']          // Licence Area
    ];

    public function __construct() {
        parent::__construct();
        $this->equipment_ids = [];
        $this->user_ids = [];
        $this->licence_users = LicenceUsersData::$data;
    }

    /**
     * Validasi data sebelum generate
     */
        protected function validate(): bool {
            try {
                // Get all active equipment IDs from model
                $this->equipment_ids = $this->equipmentModel->getAllEquipmentIds();
                if (empty($this->equipment_ids)) {
                    throw new \Exception('No active equipments found in database');
                }

                // Get licence admin users mapping from WPUserGenerator
                $this->licence_users = LicenceUsersData::$data;
                if (empty($this->licence_users)) {
                    throw new \Exception('Branch admin users not found');
                }

                // 1. Validasi keberadaan tabel
                $provinces_exist = $this->wpdb->get_var(
                    "SHOW TABLES LIKE '{$this->wpdb->prefix}wi_provinces'"
                );
                if (!$provinces_exist) {
                    throw new \Exception('Provinces table not found');
                }

                $regencies_exist = $this->wpdb->get_var(
                    "SHOW TABLES LIKE '{$this->wpdb->prefix}wi_regencies'"
                );
                if (!$regencies_exist) {
                    throw new \Exception('Regencies table not found');
                }

                // 2. Validasi ketersediaan data provinsi & regency
                $province_count = $this->wpdb->get_var("
                    SELECT COUNT(*) 
                    FROM {$this->wpdb->prefix}wi_provinces
                ");
                if ($province_count == 0) {
                    throw new \Exception('No provinces data found');
                }

                $regency_count = $this->wpdb->get_var("
                    SELECT COUNT(*) 
                    FROM {$this->wpdb->prefix}wi_regencies
                ");
                if ($regency_count == 0) {
                    throw new \Exception('No regencies data found');
                }

                // 3. Validasi data wilayah untuk setiap equipment
                foreach ($this->equipment_ids as $equipment_id) {
                    $equipment = $this->equipmentModel->find($equipment_id);
                    if (!$equipment) {
                        throw new \Exception("Equipment not found: {$equipment_id}");
                    }

                    // Jika equipment punya data wilayah, validasi relasinya
                    if ($equipment->provinsi_id && $equipment->regency_id) {
                        // Cek provinsi ada
                        $province = $this->wpdb->get_row($this->wpdb->prepare("
                            SELECT * FROM {$this->wpdb->prefix}wi_provinces 
                            WHERE id = %d",
                            $equipment->provinsi_id
                        ));
                        if (!$province) {
                            throw new \Exception("Invalid province ID for equipment {$equipment_id}: {$equipment->provinsi_id}");
                        }

                        // Cek regency ada dan berelasi dengan provinsi
                        $regency = $this->wpdb->get_row($this->wpdb->prepare("
                            SELECT r.*, p.name as province_name 
                            FROM {$this->wpdb->prefix}wi_regencies r
                            JOIN {$this->wpdb->prefix}wi_provinces p ON r.province_id = p.id
                            WHERE r.id = %d AND r.province_id = %d",
                            $equipment->regency_id,
                            $equipment->provinsi_id
                        ));
                        if (!$regency) {
                            throw new \Exception("Invalid regency ID {$equipment->regency_id} for province {$equipment->provinsi_id}");
                        }

                        $this->debug(sprintf(
                            "Validated location for equipment %d: %s, %s %s",
                            $equipment_id,
                            $province->name,
                            $regency->type,
                            $regency->name
                        ));
                    }
                }

                $this->debug('All location data validated successfully');
                return true;

            } catch (\Exception $e) {
                $this->debug('Validation failed: ' . $e->getMessage());
                return false;
            }
        }

    protected function generate(): void {
        if (!$this->isDevelopmentMode()) {
            $this->debug('Cannot generate data - not in development mode');
            throw new \Exception('Development mode is not enabled. Please enable it in settings first.');
        }
        
        if ($this->shouldClearData()) {
            // Delete existing licencees
            $this->wpdb->query("DELETE FROM {$this->wpdb->prefix}app_licencees WHERE id > 0");
            
            // Reset auto increment
            $this->wpdb->query("ALTER TABLE {$this->wpdb->prefix}app_licencees AUTO_INCREMENT = 1");
            
            $this->debug("Cleared existing licence data");
        }

        // TAMBAHKAN DI SINI
        if (!$this->validate()) {
            throw new \Exception('Pre-generation validation failed');
        }

        $generated_count = 0;

        try {
            // Get all active equipments
            foreach ($this->equipment_ids as $equipment_id) {
                $equipment = $this->equipmentModel->find($equipment_id);
                if (!$equipment) {
                    $this->debug("Equipment not found: {$equipment_id}");
                    continue;
                }

                if (!isset($this->licence_users[$equipment_id])) {
                    $this->debug("No licence admin users found for equipment {$equipment_id}, skipping...");
                    continue;
                }

                // Check for existing pusat licence
                $existing_pusat = $this->wpdb->get_row($this->wpdb->prepare(
                    "SELECT * FROM {$this->wpdb->prefix}app_licencees 
                     WHERE equipment_id = %d AND type = 'pusat'",
                    $equipment_id
                ));

                if ($existing_pusat) {
                    $this->debug("Pusat licence exists for equipment {$equipment_id}, skipping...");
                } else {
                    // Get pusat admin user ID
                    $pusat_user = $this->licence_users[$equipment_id]['pusat'];
                    $this->debug("Using pusat admin user ID: {$pusat_user['id']} for equipment {$equipment_id}");
                    $this->generatePusatBranch($equipment, $pusat_user['id']);
                    $generated_count++;
                }

                // Check for existing cabang licencees
                $existing_cabang_count = $this->wpdb->get_var($this->wpdb->prepare(
                    "SELECT COUNT(*) FROM {$this->wpdb->prefix}app_licencees 
                     WHERE equipment_id = %d AND type = 'cabang'",
                    $equipment_id
                ));

                if ($existing_cabang_count > 0) {
                    $this->debug("Cabang licencees exist for equipment {$equipment_id}, skipping...");
                } else {
                    $this->generateCabangBranches($equipment);
                    $generated_count++;
                }
            }

            if ($generated_count === 0) {
                $this->debug('No new licencees were generated - all licencees already exist');
            } else {
                // Reset auto increment only if we added new data
                $this->wpdb->query(
                    "ALTER TABLE {$this->wpdb->prefix}app_licencees AUTO_INCREMENT = " . 
                    (count($this->licence_ids) + 1)
                );
                $this->debug("Branch generation completed. Total new licencees processed: {$generated_count}");
            }

        } catch (\Exception $e) {
            $this->debug("Error in licence generation: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate kantor pusat
     */

    private function generatePusatBranch($equipment, $licence_user_id): void {
        // Validate location data
        if (!$this->validateLocation($equipment->provinsi_id, $equipment->regency_id)) {
            throw new \Exception("Invalid location for equipment: {$equipment->id}");
        }

        // Generate WordPress user dulu
        $userGenerator = new WPUserGenerator();
        
        // Ambil data user dari licence_users
        $user_data = $this->licence_users[$equipment->id]['pusat'];
        
        // Generate WP User
        $wp_user_id = $userGenerator->generateUser([
            'id' => $user_data['id'],
            'username' => $user_data['username'],
            'display_name' => $user_data['display_name'],
            'role' => 'equipment'  // atau role khusus untuk licence admin
        ]);

        if (!$wp_user_id) {
            throw new \Exception("Failed to create WordPress user for licence admin: {$user_data['display_name']}");
        }

        $regency_name = $this->getRegencyName($equipment->regency_id);
        $location = $this->generateValidLocation();
        
        $licence_data = [
            'equipment_id' => $equipment->id,
            'name' => sprintf('%s Licence %s', 
                            $equipment->name,
                            $regency_name),
            'type' => 'pusat',
            'nitku' => $this->generateNITKU(),
            'postal_code' => $this->generatePostalCode(),
            'latitude' => $location['latitude'],
            'longitude' => $location['longitude'],
            'address' => $this->generateAddress($regency_name),
            'phone' => $this->generatePhone(),
            'email' => $this->generateEmail($equipment->name, 'pusat'),
            'provinsi_id' => $equipment->provinsi_id,
            'regency_id' => $equipment->regency_id,
            'user_id' => $licence_user_id,                  // Licence admin user
            'created_by' => $equipment->user_id,            // Equipment owner user
            'status' => 'active'
        ];

        $licence_id = $this->licenceModel->create($licence_data);
        if (!$licence_id) {
            throw new \Exception("Failed to create pusat licence for equipment: {$equipment->id}");
        }

        $this->licence_ids[] = $licence_id;
        $this->debug("Created pusat licence for equipment {$equipment->name}");
    }

    /**
     * Generate cabang licencees
     */
    private function generateCabangBranches($equipment): void {
        // Generate 1-2 cabang per equipment
        //$cabang_count = rand(1, 2);

        $cabang_count = 2; // Selalu buat 2 cabang karena sudah ada 2 user cabang

        $used_provinces = [$equipment->provinsi_id];
        $userGenerator = new WPUserGenerator();
        
        for ($i = 0; $i < $cabang_count; $i++) {
            // Get cabang admin user ID
            $cabang_key = 'cabang' . ($i + 1);
            if (!isset($this->licence_users[$equipment->id][$cabang_key])) {
                $this->debug("No admin user found for {$cabang_key} of equipment {$equipment->id}, skipping...");
                continue;
            }

            // Generate WordPress user untuk cabang
            $user_data = $this->licence_users[$equipment->id][$cabang_key];
            $wp_user_id = $userGenerator->generateUser([
                'id' => $user_data['id'],
                'username' => $user_data['username'],
                'display_name' => $user_data['display_name'],
                'role' => 'equipment'  // atau role khusus untuk licence admin
            ]);
            
            if (!$wp_user_id) {
                throw new \Exception("Failed to create WordPress user for licence admin: {$user_data['display_name']}");
            }

            // Get random province (different from used provinces)
            $provinsi_id = $this->getRandomProvinceExcept($equipment->provinsi_id);
            while (in_array($provinsi_id, $used_provinces)) {
                $provinsi_id = $this->getRandomProvinceExcept($equipment->provinsi_id);
            }
            $used_provinces[] = $provinsi_id;
            
            // Get random regency from selected province
            $regency_id = $this->getRandomRegencyId($provinsi_id);
            $regency_name = $this->getRegencyName($regency_id);
            $location = $this->generateValidLocation();

            $licence_data = [
                'equipment_id' => $equipment->id,
                'name' => sprintf('%s Licence %s', 
                                $equipment->name, 
                                $regency_name),
                'type' => 'cabang',
                'nitku' => $this->generateNITKU(),
                'postal_code' => $this->generatePostalCode(),
                'latitude' => $location['latitude'],
                'longitude' => $location['longitude'],
                'address' => $this->generateAddress($regency_name),
                'phone' => $this->generatePhone(),
                'email' => $this->generateEmail($equipment->name, $cabang_key),
                'provinsi_id' => $provinsi_id,
                'regency_id' => $regency_id,
                'user_id' => $wp_user_id,  // Gunakan WP user yang baru dibuat
                'created_by' => $equipment->user_id,        // Equipment owner user
                'status' => 'active'
            ];

            $licence_id = $this->licenceModel->create($licence_data);
            if (!$licence_id) {
                throw new \Exception("Failed to create cabang licence for equipment: {$equipment->id}");
            }

            $this->licence_ids[] = $licence_id;
            $this->debug("Created cabang licence for equipment {$equipment->name} in {$regency_name}");
        }
    }

    /**
     * Helper method generators
     */
    private function generateNITKU(): string {
        do {
            $nitku = sprintf("%013d", rand(1000000000000, 9999999999999));
        } while (in_array($nitku, $this->used_nitku));
        
        $this->used_nitku[] = $nitku;
        return $nitku;
    }

    private function generatePostalCode(): string {
        return (string) rand(10000, 99999);
    }

    private function generatePhone(): string {
        $isMobile = rand(0, 1) === 1;
        $prefix = rand(0, 1) ? '+62' : '0';
        
        if ($isMobile) {
            // Mobile format: +62/0 8xx xxxxxxxx
            return $prefix . '8' . rand(1, 9) . str_pad(rand(0, 99999999), 8, '0', STR_PAD_LEFT);
        } else {
            // Landline format: +62/0 xxx xxxxxxx
            $areaCodes = ['21', '22', '24', '31', '711', '61', '411', '911']; // Jakarta, Bandung, Semarang, Surabaya, Palembang, etc
            $areaCode = $areaCodes[array_rand($areaCodes)];
            return $prefix . $areaCode . str_pad(rand(0, 9999999), 7, '0', STR_PAD_LEFT);
        }
    }

    private function generateEmail($equipment_name, $type): string {
        $domains = ['gmail.com', 'yahoo.com', 'hotmail.com'];
        
        do {
            $email = sprintf('%s.%s@%s',
                $type,
                strtolower(str_replace([' ', '.'], '', $equipment_name)),
                $domains[array_rand($domains)]
            );
        } while (in_array($email, $this->used_emails));
        
        $this->used_emails[] = $email;
        return $email;
    }

    /**
     * Get array of generated licence IDs
     */
    public function getBranchIds(): array {
        return $this->licence_ids;
    }

    // Define location bounds untuk wilayah Indonesia
    private const LOCATION_BOUNDS = [
        'LAT_MIN' => -11.0,    // Batas selatan (Pulau Rote)
        'LAT_MAX' => 6.0,      // Batas utara (Sabang)
        'LONG_MIN' => 95.0,    // Batas barat (Pulau Weh)
        'LONG_MAX' => 141.0    // Batas timur (Pulau Merauke)
    ];

    /**
     * Generate random latitude dalam format decimal
     * dengan 8 digit di belakang koma
     */
    private function generateLatitude(): float {
        $min = self::LOCATION_BOUNDS['LAT_MIN'] * 100000000;
        $max = self::LOCATION_BOUNDS['LAT_MAX'] * 100000000;
        $randomInt = rand($min, $max);
        return $randomInt / 100000000;
    }

    /**
     * Generate random longitude dalam format decimal
     * dengan 8 digit di belakang koma
     */
    private function generateLongitude(): float {
        $min = self::LOCATION_BOUNDS['LONG_MIN'] * 100000000;
        $max = self::LOCATION_BOUNDS['LONG_MAX'] * 100000000;
        $randomInt = rand($min, $max);
        return $randomInt / 100000000;
    }

    /**
     * Helper method untuk format koordinat dengan 8 digit decimal
     */
    private function formatCoordinate(float $coordinate): string {
        return number_format($coordinate, 8, '.', '');
    }

    /**
     * Generate dan validasi koordinat
     */
    private function generateValidLocation(): array {
        $latitude = $this->generateLatitude();
        $longitude = $this->generateLongitude();

        return [
            'latitude' => $this->formatCoordinate($latitude),
            'longitude' => $this->formatCoordinate($longitude)
        ];
    }

    /**
     * Debug method untuk test hasil generate
     */
    private function debugLocation(): void {
        $location = $this->generateValidLocation();
        $this->debug(sprintf(
            "Generated location - Lat: %s, Long: %s",
            $location['latitude'],
            $location['longitude']
        ));
    }


}
