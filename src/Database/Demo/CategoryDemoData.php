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

    protected function generate(): void {
        if (!$this->isDevelopmentMode()) {
            $this->debug('Cannot generate data - not in development mode');
            return;
        }

        // Clear existing data if needed
        if ($this->shouldClearData()) {
            $this->wpdb->query("DELETE FROM {$this->wpdb->prefix}app_categories WHERE id > 0");
            $this->wpdb->query("ALTER TABLE {$this->wpdb->prefix}app_categories AUTO_INCREMENT = 1");
            $this->debug("Cleared existing category data");
        }

        try {
            // Sort categories by level to ensure parents are created first
            usort($this->categories, function($a, $b) {
                return $a['level'] - $b['level'];
            });

            foreach ($this->categories as $category) {
                // Check if category already exists
                $existing = $this->wpdb->get_row($this->wpdb->prepare(
                    "SELECT * FROM {$this->wpdb->prefix}app_categories WHERE id = %d",
                    $category['id']
                ));

                if ($existing) {
                    if ($this->shouldClearData()) {
                        $this->wpdb->delete(
                            $this->wpdb->prefix . 'app_categories',
                            ['id' => $category['id']],
                            ['%d']
                        );
                        $this->debug("Deleted existing category: {$category['name']}");
                    } else {
                        $this->debug("Category exists: {$category['name']}, skipping...");
                        continue;
                    }
                }

                // Prepare category data
                $category_data = [
                    'id' => $category['id'],
                    'code' => $category['code'],
                    'name' => $category['name'],
                    'description' => $category['description'],
                    'level' => $category['level'],
                    'parent_id' => $category['parent_id'],
                    'group_id' => $category['group_id'],
                    'relation_id' => $category['relation_id'],
                    'sort_order' => $category['sort_order'],
                    'unit' => $category['unit'],
                    'price' => $category['price'],
                    'status' => $category['status'],
                    'created_by' => 1,
                    'created_at' => current_time('mysql'),
                    'updated_at' => current_time('mysql')
                ];

                // Insert category
                $result = $this->wpdb->insert(
                    $this->wpdb->prefix . 'app_categories',
                    $category_data,
                    [
                        '%d', '%s', '%s', '%s', '%d', '%d', '%d', '%d', 
                        '%d', '%s', '%f', '%s', '%d', '%s', '%s'
                    ]
                );

                if ($result === false) {
                    throw new \Exception("Failed to insert category: {$category['name']}");
                }

                self::$category_ids[] = $category['id'];
                $this->debug("Created category: {$category['name']} with ID: {$category['id']}");
            }

            // Reset auto increment after all insertions
            $this->wpdb->query(
                "ALTER TABLE {$this->wpdb->prefix}app_categories AUTO_INCREMENT = " . 
                (max(self::$category_ids) + 1)
            );

        } catch (\Exception $e) {
            $this->debug("Error in category generation: " . $e->getMessage());
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
