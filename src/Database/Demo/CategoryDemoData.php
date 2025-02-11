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
 *              - Price dan unit untuk kategori tertentu
 */

namespace WPEquipment\Database\Demo;

use WPEquipment\Database\Demo\Data\CategoryData;

defined('ABSPATH') || exit;

class CategoryDemoData extends AbstractDemoData {
    private static $category_ids = [];
    protected $categories;

    public function __construct() {
        parent::__construct();
        $this->categories = CategoryData::$data;
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

            // Validate unique codes
            $codes = array_column($this->categories, 'code');
            if (count($codes) !== count(array_unique($codes))) {
                throw new \Exception('Duplicate category codes found');
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

        // Validasi struktur database terlebih dahulu
        if (!$this->validateDatabaseStructure()) {
            throw new \Exception('Struktur database tidak valid');
        }

        // Bersihkan data yang ada jika diperlukan
        if ($this->shouldClearData()) {
            $this->wpdb->query("DELETE FROM {$this->wpdb->prefix}app_categories WHERE id > 0");
            $this->wpdb->query("ALTER TABLE {$this->wpdb->prefix}app_categories AUTO_INCREMENT = 1");
            $this->debug("Data kategori yang ada telah dibersihkan");
        }

        try {
            // Urutkan kategori berdasarkan level
            usort($this->categories, function($a, $b) {
                return $a['level'] - $b['level'];
            });

            foreach ($this->categories as $category) {
                // Periksa apakah kategori sudah ada
                $existing = $this->wpdb->get_row($this->wpdb->prepare(
                    "SELECT * FROM {$this->wpdb->prefix}app_categories WHERE code = %s",
                    $category['code']
                ));

                if ($existing) {
                    $this->debug("Kategori dengan kode {$category['code']} sudah ada, melewati...");
                    continue;
                }

                // Siapkan data kategori
                $category_data = [
                    'code' => $category['code'],
                    'name' => $category['name'],
                    'description' => $category['description'],
                    'level' => $category['level'],
                    'parent_id' => $category['parent_id'],
                    'group_id' => $category['group_id'] ?? null,
                    'relation_id' => $category['relation_id'] ?? null,
                    'sort_order' => $category['sort_order'] ?? 0,
                    'unit' => $category['unit'],
                    'price' => $category['price'],
                    'status' => $category['status'] ?? 'active',
                    'created_by' => get_current_user_id(),
                    'created_at' => current_time('mysql'),
                    'updated_at' => current_time('mysql')
                ];

                // Log data sebelum insert untuk debugging
                $this->debug("Mencoba insert kategori: " . print_r($category_data, true));

                // Insert kategori dengan error handling yang lebih baik
                $result = $this->wpdb->insert(
                    $this->wpdb->prefix . 'app_categories',
                    $category_data,
                    [
                        '%s', '%s', '%s', '%d', '%d', '%d', '%d', 
                        '%d', '%s', '%f', '%s', '%d', '%s', '%s'
                    ]
                );

                if ($result === false) {
                    throw new \Exception(
                        "Gagal insert kategori {$category['name']}. " .
                        "Error: " . $this->wpdb->last_error
                    );
                }

                $inserted_id = $this->wpdb->insert_id;
                self::$category_ids[] = $inserted_id;
                $this->debug("Berhasil membuat kategori: {$category['name']} dengan ID: {$inserted_id}");
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
