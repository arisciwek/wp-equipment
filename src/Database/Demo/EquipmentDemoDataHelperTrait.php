<?php
/**
 * Equipment Demo Data Helper Trait
 *
 * @package     WP_Equipment
 * @subpackage  Database/Demo
 * @version     1.0.0
 * @author      arisciwek
 *
 * Path: /wp-equipment/src/Database/Demo/EquipmentDemoDataHelperTrait.php
 *
 * Description: Helper methods for equipment demo data generation.
 *              Provides reusable functions for:
 *              - Business identifiers (NPWP, NIB) with validation
 *              - Equipment data format standardization
 *              - Location data retrieval from provinces/regencies
 * 
 * Dependencies:
 * - WPEquipment\Models\Equipment\EquipmentModel
 * - WordPress database ($wpdb)
 * - wi_provinces and wi_regencies tables
 *
 * Helper Methods:
 * - generateNPWP()           : Generate valid NPWP number
 * - generateNIB()            : Generate valid NIB number
 * - getRandomProvinceId()    : Get random province from wi_provinces
 * - getRandomRegencyId()     : Get random regency for a province
 * - validateLocationData()   : Ensure required location data exists
 * - clearEquipmentData()      : Clean up equipment records
 *
 * Data Formats:
 * - NPWP Format : XX.XXX.XXX.X-XXX.XXX
 * - NIB Format  : 13 digits sequential
 * - Equipment Code: CUST-TTTTRRRR (T=timestamp, R=random)
 *
 * Usage:
 * ```php
 * class EquipmentDemoData extends AbstractDemoData {
 *     use EquipmentDemoDataHelperTrait;
 *     // ... equipment specific implementation
 * }
 * ```
 *
 * Changelog:
 * 1.0.0 - 2024-01-27
 * - Initial version
 * - Focused on equipment-specific helper methods
 * - Added location data validation
 * - Added data format standardization
 */

namespace WPEquipment\Database\Demo;

defined('ABSPATH') || exit;

trait EquipmentDemoDataHelperTrait {
    /**
     * Generate licence location
     */
    private function generateBranch($equipment_id, $type = 'cabang'): void {
        $postal_codes = ['12760', '13210', '14350', '15310', '16110'];
        $domains = ['gmail.com', 'yahoo.com', 'hotmail.com'];

        // Get random province and regency
        $provinsi_id = $this->getRandomProvinceId();
        $regency_id = $this->getRandomRegencyId($provinsi_id);
        $regency_name = $this->getRegencyName($regency_id);

        $equipment = $this->equipmentModel->getById($equipment_id);
        
        $licence_data = [
            'equipment_id' => $equipment_id,
            'code' => $this->licenceModel->generateBranchCode(),
            'name' => $type === 'pusat' ? 
                     $equipment->name . ' Kantor Pusat' : 
                     $equipment->name . ' Licence ' . $regency_name,
            'type' => $type,
            'nitku' => sprintf("%013d", rand(1000000000000, 9999999999999)),
            'postal_code' => $postal_codes[array_rand($postal_codes)],
            'latitude' => rand(-6000000, -5000000) / 100000,
            'longitude' => rand(106000000, 107000000) / 100000,
            'address' => $this->generateAddress($regency_name),
            'phone' => sprintf('%s%s', 
                      rand(0, 1) ? '021-' : '022-', 
                      rand(1000000, 9999999)),
            'email' => sprintf('%s.%s@%s',
                      $type,
                      strtolower(str_replace([' ', '.'], '', $equipment->name)),
                      $domains[array_rand($domains)]),
            'provinsi_id' => $provinsi_id,
            'regency_id' => $regency_id,
            'user_id' => $this->user_ids[$equipment_id],
            'created_by' => $this->user_ids[$equipment_id],
            'status' => 'active'
        ];

        $licence_id = $this->licenceModel->create($licence_data);
        if (!$licence_id) {
            throw new \Exception("Failed to create licence for equipment: {$equipment_id}");
        }
        
        $this->licence_ids[] = $licence_id;
    }

    /**
     * Generate complete address
     */
    private function generateAddress($regency_name): string {
        return sprintf(
            'Jl. %s No. %d, %s',
            $this->generateStreetName(),
            rand(1, 200),
            $regency_name
        );
    }

    /**
     * Generate NPWP number
     */
    private function generateNPWP(): string {
        do {
            $npwp = sprintf("%02d.%03d.%03d.%d-%03d.%03d",
                rand(0, 99),
                rand(0, 999),
                rand(0, 999),
                rand(0, 9),
                rand(0, 999),
                rand(0, 999)
            );
        } while (in_array($npwp, $this->used_npwp));
        
        $this->used_npwp[] = $npwp;
        return $npwp;
    }

    /**
     * Generate NIB number
     */
    private function generateNIB(): string {
        do {
            $nib = sprintf("%013d", rand(1000000000000, 9999999999999));
        } while (in_array($nib, $this->used_nib));
        
        $this->used_nib[] = $nib;
        return $nib;
    }

    /**
     * Generate random Indonesian person name
     */
    private function generatePersonName(): string {
        $firstNames = [
            'Budi', 'Siti', 'Andi', 'Dewi', 'Rudi',
            'Nina', 'Joko', 'Rita', 'Doni', 'Sari',
            'Agus', 'Lina', 'Hadi', 'Maya', 'Eko',
            'Tono', 'Wati', 'Bambang', 'Sri', 'Dedi'
        ];
        
        $lastNames = [
            'Susanto', 'Wijaya', 'Kusuma', 'Pratama', 'Sanjaya',
            'Hidayat', 'Nugraha', 'Putra', 'Santoso', 'Wibowo',
            'Saputra', 'Permana', 'Utama', 'Suryadi', 'Gunawan'
        ];

        do {
            $name = $firstNames[array_rand($firstNames)] . ' ' . 
                   $lastNames[array_rand($lastNames)];
        } while (in_array($name, $this->used_names));
        
        $this->used_names[] = $name;
        return $name;
    }

    /**
     * Generate email from name
     */
    private function generateEmail($name): string {
        $baseEmail = strtolower(str_replace(' ', '.', $name));
        $email = $baseEmail . '@example.com';
        
        $counter = 1;
        while (in_array($email, $this->used_emails)) {
            $email = $baseEmail . $counter . '@example.com';
            $counter++;
        }
        
        $this->used_emails[] = $email;
        return $email;
    }

    /**
     * Generate random street name
     */
    private function generateStreetName(): string {
        $prefixes = ['Jend.', 'Letjen.', 'Dr.', 'Ir.', 'Prof.'];
        $names = [
            'Sudirman', 'Thamrin', 'Gatot Subroto', 'Rasuna Said', 'Kuningan',
            'Asia Afrika', 'Diponegoro', 'Ahmad Yani', 'Imam Bonjol', 'Veteran',
            'Pemuda', 'Merdeka', 'Hayam Wuruk', 'Gajah Mada', 'Wahid Hasyim'
        ];
        $types = ['Raya', 'Besar', ''];
        
        return sprintf(
            '%s %s %s',
            $prefixes[array_rand($prefixes)],
            $names[array_rand($names)],
            $types[array_rand($types)]
        );
    }

    /**
     * Clear existing data
     */
    private function clearExistingData(): void {

        // Check development mode before clearing
        if (!$this->shouldClearData()) {
            $this->debug('Skipping data cleanup - as in settings option');
            return;
        }

        // Delete in correct order (child tables first)
        $this->wpdb->query("DELETE FROM {$this->wpdb->prefix}app_equipment_employees");
        $this->wpdb->query("DELETE FROM {$this->wpdb->prefix}app_licencees");
        $this->wpdb->query("DELETE FROM {$this->wpdb->prefix}app_equipments");
        
        $this->debug('Existing demo data cleared');
    }

    /**
     * Get random province ID that has regencies
     */
    private function getRandomProvinceId(): int {
        $province = $this->wpdb->get_row("
            SELECT DISTINCT p.id 
            FROM {$this->wpdb->prefix}wi_provinces p
            INNER JOIN {$this->wpdb->prefix}wi_regencies r ON r.province_id = p.id 
            ORDER BY RAND() 
            LIMIT 1
        ");
        
        if (!$province) {
            throw new \Exception('Failed to get random province with regencies');
        }

        return (int) $province->id;
    }

    /**
     * Get random province except specified ID
     */
      protected function getRandomProvinceExcept(int $exclude_id): int {
        $province = $this->wpdb->get_row($this->wpdb->prepare("
            SELECT DISTINCT p.id 
            FROM {$this->wpdb->prefix}wi_provinces p
            INNER JOIN {$this->wpdb->prefix}wi_regencies r ON r.province_id = p.id 
            WHERE p.id != %d 
            ORDER BY RAND() 
            LIMIT 1",
            $exclude_id
        ));

        if (!$province) {
            throw new \Exception('No other provinces found');
        }
        
        return (int) $province->id;
    }

    /**
     * Get random regency ID for a province
     */
    protected function getRandomRegencyId(int $province_id): int {
        $regency = $this->wpdb->get_row($this->wpdb->prepare(
            "SELECT id FROM {$this->wpdb->prefix}wi_regencies 
             WHERE province_id = %d ORDER BY RAND() LIMIT 1",
            $province_id
        ));
        
        if (!$regency) {
            throw new \Exception("No regencies found for province: {$province_id}");
        }
        
        return (int) $regency->id;
    }

    /**
     * Get regency name by ID with type (Kota/Kabupaten)
     */
    protected function getRegencyName(int $regency_id): string {
        $regency = $this->wpdb->get_row($this->wpdb->prepare("
            SELECT r.name, r.type
            FROM {$this->wpdb->prefix}wi_regencies r
            WHERE r.id = %d",
            $regency_id
        ));
        
        if (!$regency) {
            throw new \Exception("Failed to get regency name for ID {$regency_id}");
        }

        // Format: "Kota Bandung" atau "Kabupaten Bogor"
        return $regency->name;
    }

    /**
     * Get province name by ID
     */
    protected function getProvinceName(int $province_id): string {
        $province = $this->wpdb->get_var($this->wpdb->prepare(
            "SELECT name FROM {$this->wpdb->prefix}wi_provinces 
             WHERE id = %d",
            $province_id
        ));
        
        if (!$province) {
            throw new \Exception("Province not found: {$province_id}");
        }
        
        return $province;
    }

    /**
     * Validate location exists
     */
    protected function validateLocation(int $province_id, int $regency_id): bool {
        return (bool) $this->wpdb->get_var($this->wpdb->prepare(
            "SELECT EXISTS (
                SELECT 1 FROM {$this->wpdb->prefix}wi_regencies 
                WHERE id = %d AND province_id = %d
            ) as result",
            $regency_id,
            $province_id
        ));
    }

    /**
     * Check if development mode is enabled
     * This can be enabled via settings or WP_EQUIPMENT_DEVELOPMENT constant
     * 
     * @return bool True if development mode is enabled
     */
    public function isDevelopmentMode(): bool {
        $dev_settings = get_option('wp_equipment_development_settings');
        return (isset($dev_settings['enable_development']) && $dev_settings['enable_development']) 
               || (defined('WP_EQUIPMENT_DEVELOPMENT') && WP_EQUIPMENT_DEVELOPMENT);
    }

    public function shouldClearData(): bool {
        $dev_settings = get_option('wp_equipment_development_settings');
        return isset($dev_settings['clear_data_on_deactivate']) && 
               $dev_settings['clear_data_on_deactivate'];
    }
}
