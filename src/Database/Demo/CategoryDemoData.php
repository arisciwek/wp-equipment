<?php
/**
 * Category Demo Data Generator
 *
 * @package     WP_Equipment
 * @subpackage  Database/Demo
 * @version     1.0.0
 * @author      arisciwek
 *
 * Path: /wp-equipment/src/Database/Demo/CategoryDemoData.php
 * 
 * Description: Generate category demo data dengan:
 *              - Struktur hierarki kategori equipment
 *              - Support multiple level depth
 *              - Tracking parent-child relationships
 *              - PNBP dan unit untuk kategori tertentu
 *              - Kode alfanumerik acak 10 digit
 */

namespace WPEquipment\Database\Demo;

use WPEquipment\Database\Demo\Data\CategoryData;

defined('ABSPATH') || exit;

class CategoryDemoData extends AbstractDemoData {
    private static $category_ids = [];
    protected $categories;
    private $used_codes = [];

    public function __construct() {
        parent::__construct();
        $this->categories = CategoryData::$data;
    }

    public function initModels() {
        parent::initModels();
        // Inisialisasi category model langsung
        if (!isset($this->categoryModel)) {
            $this->categoryModel = new \WPEquipment\Models\CategoryModel();
        }
    }

    /**
     * Generates a random alphanumeric code of specified length
     * Uses only A-Z, a-z, and 1-9 characters (no 0)
     *
     * @param int $length Length of the code
     * @return string Random alphanumeric code
     */
    private function generateAlphanumericCode($length = 10): string {
        // Characters to use (A-Z, a-z, 1-9)
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz123456789';
        $code = '';
        
        // Get the maximum index value
        $max_index = strlen($chars) - 1;
        
        // Generate random characters
        for ($i = 0; $i < $length; $i++) {
            $code .= $chars[mt_rand(0, $max_index)];
        }
        
        // Make sure the code is unique
        if (in_array($code, $this->used_codes) || $this->categoryModel->existsByCode($code)) {
            // If code already exists, generate a new one
            return $this->generateAlphanumericCode($length);
        }
        
        // Add to used codes
        $this->used_codes[] = $code;
        
        return $code;
    }

    protected function validate(): bool {
        try {
            // Validate that we have category data
            if (empty($this->categories)) {
                throw new \Exception('No category data available');
            }

            // Validate parent-child relationships
            foreach ($this->categories as $category) {
                if ($category['parent_id']) {
                    $parent_exists = false;
                    foreach ($this->categories as $potential_parent) {
                        if ($potential_parent['id'] == $category['parent_id']) {
                            $parent_exists = true;
                            break;
                        }
                    }
                    if (!$parent_exists) {
                        throw new \Exception("Parent ID {$category['parent_id']} not found for category {$category['name']}");
                    }
                }
            }

            $this->debug('Category data validation successful');
            return true;

        } catch (\Exception $e) {
            $this->debug('Validation failed: ' . $e->getMessage());
            return false;
        }
    }
    
    private function validateDatabaseStructure(): bool {
        try {
            global $wpdb;
            $table = $this->wpdb->prefix . 'app_categories';
            
            // Periksa apakah tabel exists
            $table_exists = $wpdb->get_var(
                $wpdb->prepare(
                    "SHOW TABLES LIKE %s",
                    $table
                )
            );
            
            if (!$table_exists) {
                throw new \Exception("Tabel kategori belum dibuat");
            }

            // Periksa struktur kolom
            $columns = $wpdb->get_results("DESCRIBE {$table}");
            $required_columns = [
                'id' => 'bigint',
                'code' => 'varchar',
                'name' => 'varchar',
                'description' => 'text',
                'level' => 'int',
                'parent_id' => 'bigint',
                'status' => 'varchar'
            ];

            $missing_columns = [];
            $existing_columns = [];
            
            foreach ($columns as $col) {
                $existing_columns[$col->Field] = strtolower($col->Type);
            }

            foreach ($required_columns as $col_name => $col_type) {
                if (!isset($existing_columns[$col_name])) {
                    $missing_columns[] = $col_name;
                }
            }

            if (!empty($missing_columns)) {
                throw new \Exception(
                    "Kolom yang diperlukan tidak ditemukan: " . 
                    implode(', ', $missing_columns)
                );
            }

            return true;

        } catch (\Exception $e) {
            $this->debug("Validasi struktur database gagal: " . $e->getMessage());
            return false;
        }
    }

    protected function generate(): void {
        if (!$this->isDevelopmentMode()) {
            $this->debug('Tidak dapat generate data - mode development tidak aktif');
            return;
        }

        try {
            // Bersihkan data yang ada jika diperlukan
            if ($this->shouldClearData()) {
                $this->wpdb->query("DELETE FROM {$this->wpdb->prefix}app_categories WHERE id > 0");
                $this->wpdb->query("ALTER TABLE {$this->wpdb->prefix}app_categories AUTO_INCREMENT = 1");
                $this->debug("Data kategori yang ada telah dibersihkan");
            }

            // Urutkan berdasarkan level
            usort($this->categories, function($a, $b) {
                return $a['level'] - $b['level'];
            });

            foreach ($this->categories as $category) {
                // Generate kode alfanumerik acak 10 digit
                $randomCode = $this->generateAlphanumericCode(10);
                
                // Periksa apakah kategori sudah ada
                $existing = $this->categoryModel->existsByCode($randomCode);
                if ($existing) {
                    $this->debug("Kategori dengan kode {$randomCode} sudah ada, mencoba kode baru...");
                    $randomCode = $this->generateAlphanumericCode(10);
                }

                // Siapkan data kategori dengan mempertahankan nama lengkap dan kode acak
                $categoryData = [
                    'code' => $randomCode,
                    'name' => $category['name'],
                    'description' => $category['description'],
                    'level' => $category['level'],
                    'parent_id' => $category['parent_id'],
                    'sort_order' => $category['sort_order'] ?? 0,
                    'unit' => $category['unit'],
                    'pnbp' => $category['pnbp'],
                    'status' => $category['status'] ?? 'active'
                ];

                // Buat kategori menggunakan model
                $inserted_id = $this->categoryModel->create($categoryData);
                
                if (!$inserted_id) {
                    throw new \Exception("Gagal membuat kategori: {$category['name']}");
                }

                self::$category_ids[] = $inserted_id;
                $this->debug("Berhasil membuat kategori: {$category['name']} dengan ID: {$inserted_id} dan kode: {$randomCode}");
            }

        } catch (\Exception $e) {
            $this->debug("Error dalam generate kategori: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Get array of generated category IDs
     */
    public function getCategoryIds(): array {
        return self::$category_ids;
    }
}
