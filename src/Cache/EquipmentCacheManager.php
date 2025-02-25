<?php
/**
 * Equipment Cache Management Class
 *
 * @package     WP_Equipment
 * @subpackage  Cache
 * @version     1.0.0
 * @author      arisciwek
 *
 * Path: /wp-equipment/src/Cache/EquipmentCacheManager.php
 *
 * Description: Manager untuk menangani caching data equipment.
 *              Menggunakan WordPress Object Cache API.
 *              Includes cache untuk:
 *              - Single equipment/licence data
 *              - Lists (equipment/licence)
 *              - Statistics
 *              - Relations
 */

namespace WPEquipment\Cache;

class EquipmentCacheManager {
    // Cache configuration
    private const CACHE_GROUP = 'wp_equipment';
    private const CACHE_EXPIRY = 12 * HOUR_IN_SECONDS;

    // Cache keys for equipment
    private const KEY_EQUIPMENT = 'equipment';
    private const KEY_EQUIPMENT_LIST = 'equipment_list';
    private const KEY_EQUIPMENT_STATS = 'equipment_stats';
    private const KEY_USER_EQUIPMENTS = 'user_equipments';

    // Cache keys for licences
    private const KEY_EQUIPMENT_LICENCE_LIST = 'equipment_licence_list';
    private const KEY_EQUIPMENT_LICENCE = 'equipment_licence';
    private const KEY_LICENCE = 'licence';
    private const KEY_LICENCE_LIST = 'licence_list';
    private const KEY_LICENCE_STATS = 'licence_stats';
    private const KEY_USER_LICENCES = 'user_licences';

    private $cache_manager;

    public function __construct() {
        // For cache diagnostics
        $this->cache_manager = $this; 
        add_action('wp_ajax_wp_equipment_cache_diagnostics', [$this, 'handleDiagnosticsRequest']);
    
    }


    /**
     * Get constant values for external access
     */
    public static function getCacheGroup(): string {
        return self::CACHE_GROUP;
    }

    public static function getCacheExpiry(): int {
        return self::CACHE_EXPIRY;
    }

    public static function getCacheKey(string $type): string {
        $constants = [
            'equipment' => self::KEY_EQUIPMENT,
            'equipment_list' => self::KEY_EQUIPMENT_LIST,
            'equipment_stats' => self::KEY_EQUIPMENT_STATS,
            'user_equipments' => self::KEY_USER_EQUIPMENTS,
            'licence' => self::KEY_LICENCE,
            'licence_list' => self::KEY_LICENCE_LIST,
            'licence_stats' => self::KEY_LICENCE_STATS,
            'user_licences' => self::KEY_USER_LICENCES,
        ];

        return $constants[$type] ?? '';
    }

    /**
     * Generates valid cache key based on components
     */
    private function generateKey(string ...$components): string {
        // Filter out empty components
        $validComponents = array_filter($components, function($component) {
            return !empty($component) && is_string($component);
        });
        
        if (empty($validComponents)) {
            return 'default_' . md5(serialize($components));
        }

        // Join with underscore and ensure valid length
        $key = implode('_', $validComponents);
        
        // WordPress has a key length limit of 172 characters
        if (strlen($key) > 172) {
            $key = substr($key, 0, 140) . '_' . md5($key);
        }
        
        return $key;
    }

    /**
     * Get value from cache with validation
     */
    public function get(string $type, ...$keyComponents) {
        $key = $this->generateKey($type, ...$keyComponents);

        if (defined('WP_DEBUG') && WP_DEBUG) {
            $this->debug_log("Cache attempt - Key: {$key}, Type: {$type}");
        }
        
        $result = wp_cache_get($key, self::CACHE_GROUP);
        
        if ($result === false) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                $this->debug_log("Cache miss - Key: {$key}");
            }
            return null;
        }
        
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $this->debug_log("Cache hit - Key: {$key}");
        }
        
        return $result;
    }

    /**
     * Set value in cache with validation
     */
    public function set(string $type, $value, int $expiry = null, ...$keyComponents): bool {
        try {
            $key = $this->generateKey($type, ...$keyComponents);

            if ($expiry === null) {
                $expiry = self::CACHE_EXPIRY;
            }

            if (defined('WP_DEBUG') && WP_DEBUG) {
                $this->debug_log("Setting cache - Key: {$key}, Type: {$type}, Expiry: {$expiry}s");
            }
            
            return wp_cache_set($key, $value, self::CACHE_GROUP, $expiry);
        } catch (\InvalidArgumentException $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                $this->debug_log("Cache set failed: " . $e->getMessage());
            }
            return false;
        }
    }

    /**
     * Delete value from cache
     */
    public function delete(string $type, ...$keyComponents): bool {
        $key = $this->generateKey($type, ...$keyComponents);
        
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $this->debug_log("Deleting cache - Key: {$key}, Type: {$type}");
        }
        
        return wp_cache_delete($key, self::CACHE_GROUP);
    }

    /**
     * Check if key exists in cache
     */
    public function exists(string $type, ...$keyComponents): bool {
        $key = $this->generateKey($type, ...$keyComponents);
        return wp_cache_get($key, self::CACHE_GROUP) !== false;
    }

    /**
     * Logger method for debugging cache operations
     */
    private function debug_log(string $message, $data = null): void {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log(sprintf(
                '[EquipmentCacheManager] %s %s',
                $message,
                $data ? '| Data: ' . print_r($data, true) : ''
            ));
        }
    }


    /**
     * Get DataTable cache based on parameters
     */
    public function getDataTableCache(
        string $context,      // Example: 'equipment_list', 'equipment_history', 'licence_list'
        int $userId,
        int $start,
        int $length,
        string $search,
        string $orderColumn,
        string $orderDir,
        ?array $additionalParams = null
    ) {
        // Validate required parameters
        if (empty($context) || !$userId || !is_numeric($start) || !is_numeric($length)) {
            $this->debug_log('Invalid parameters in getDataTableCache');
            return null;
        }
        
        try {
            // Build cache key components
            $components = [
                $context,         // specific context
                (string)$userId,
                (string)$start,
                (string)$length,
                md5($search),
                (string)$orderColumn,
                (string)$orderDir
            ];

            // Add additional parameters if provided
            if ($additionalParams) {
                foreach ($additionalParams as $key => $value) {
                    $components[] = $key . '_' . md5(serialize($value));
                }
            }

            // PERBAIKAN: Tambahkan 'datatable' sebagai tipe cache untuk konsistensi dengan setDataTableCache
            return $this->get('datatable', ...$components);


        } catch (\Exception $e) {
            $this->debug_log("Error getting datatable data for context {$context}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Set DataTable cache with parameters
     */
    public function setDataTableCache(
        string $context,
        int $userId,
        int $start,
        int $length,
        string $search,
        string $orderColumn,
        string $orderDir,
        $data,
        ?array $additionalParams = null
    ) {
        // Validate required parameters
        if (empty($context) || !$userId || !is_numeric($start) || !is_numeric($length)) {
            $this->debug_log('Invalid parameters in setDataTableCache');
            return false;
        }

        // Build cache key components
        $components = [
            $context,
            (string)$userId,
            (string)$start,
            (string)$length,
            md5($search),
            (string)$orderColumn,
            (string)$orderDir
        ];

        // Add additional parameters if provided
        if ($additionalParams) {
            foreach ($additionalParams as $key => $value) {
                $components[] = $key . '_' . md5(serialize($value));
            }
        }

        // Use shorter expiry time for DataTable cache
        return $this->set('datatable', $data, 2 * MINUTE_IN_SECONDS, ...$components);
    }

    /**
     * Invalidate DataTable cache for specific context
     */
     public function invalidateDataTableCache(string $context, ?array $filters = null): bool {
        try {
            if (empty($context)) {
                $this->debug_log('Invalid context in invalidateDataTableCache');
                return false;
            }
    
            // Log invalidation attempt
            $this->debug_log(sprintf(
                'Attempting to invalidate DataTable cache - Context: %s, Filters: %s',
                $context,
                $filters ? json_encode($filters) : 'none'
            ));
    
            // Base cache key components
            $components = [
                $context
            ];
    
            // If we have filters, create filter-specific invalidation
            if ($filters) {
                foreach ($filters as $key => $value) {
                    $components[] = sprintf('%s_%s', $key, md5(serialize($value)));
                }
                
                // Delete specific filtered cache
                $result = $this->delete('datatable', ...$components);
                
                $this->debug_log(sprintf(
                    'Invalidated filtered cache for context %s with filters. Result: %s',
                    $context,
                    $result ? 'success' : 'failed'
                ));
                
                return $result;
            }
    
            // If no filters, do a broader invalidation using deleteByPrefix
            $prefix = $this->generateKey('datatable', $context);
            $result = $this->deleteByPrefix($prefix);
    
            $this->debug_log(sprintf(
                'Invalidated all cache entries for context %s. Result: %s',
                $context,
                $result ? 'success' : 'failed'
            ));
    
            return $result;
    
        } catch (\Exception $e) {
            $this->debug_log('Error in invalidateDataTableCache: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete all cache entries that match a prefix
     */
    /**
     * Delete all cache entries that match a prefix
     * 
     * @param string $prefix The prefix to match
     * @return bool Success status
     */
    private function deleteByPrefix(string $prefix): bool {
        try {
            // Log the operation
            $this->debug_log('Attempting to delete cache entries with prefix: ' . $prefix);
            
            // First, try to get all keys for this group if we're using a plugin that supports this
            $all_keys = wp_cache_get($this->getCacheGroup() . '_keys_list', 'wp_equipment_meta');
            
            if (is_array($all_keys) && !empty($all_keys)) {
                // We have a list of keys, try to delete the matching ones
                foreach ($all_keys as $key) {
                    if (strpos($key, $prefix) === 0) {
                        $this->debug_log('Deleting key by prefix match: ' . $key);
                        wp_cache_delete($key, self::CACHE_GROUP);
                    }
                }
                return true;
            }
            
            // If we don't have a key list, use our known key pattern system
            // This is an alternative approach that works even without direct cache access
            $types = ['equipment', 'equipment_list', 'equipment_stats', 'licence', 
                    'licence_list', 'licence_stats', 'datatable', 'category'];
            
            // Extract the prefix components to determine what type of data we're clearing
            $prefix_parts = explode('_', $prefix);
            $target_type = $prefix_parts[0] ?? '';
            
            // If we have a valid type, just clear all caches of that type
            if (in_array($target_type, $types)) {
                $this->debug_log('Clearing all caches of type: ' . $target_type);
                
                // Get known ID patterns for this type (adjust based on your application logic)
                $pattern_ids = $this->getKnownIdsForType($target_type);
                
                foreach ($pattern_ids as $id) {
                    $cache_key = $this->generateKey($target_type, $id);
                    $this->debug_log('Attempting to delete key: ' . $cache_key);
                    wp_cache_delete($cache_key, self::CACHE_GROUP);
                }
            }
            
            // Fallback - always clear datatable caches for the context
            if (strpos($prefix, 'datatable_') === 0) {
                $context = str_replace('datatable_', '', $prefix);
                $this->debug_log('Clearing datatable cache for context: ' . $context);
                
                // Clear for all possible page sizes and sort options
                $page_sizes = [10, 25, 50, 100];
                $sort_dirs = ['asc', 'desc'];
                
                foreach ($page_sizes as $length) {
                    foreach ($sort_dirs as $dir) {
                        $cache_key = $this->generateKey('datatable', $context, 'all', '0', (string)$length, '', 'code', $dir);
                        $this->debug_log('Deleting generated key: ' . $cache_key);
                        wp_cache_delete($cache_key, self::CACHE_GROUP);
                    }
                }
            }
            
            return true;
        } catch (\Exception $e) {
            $this->debug_log('Error in deleteByPrefix: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Helper method to get known IDs for a specific cache type
     * Customize based on your application's needs
     */
    private function getKnownIdsForType(string $type): array {
        global $wpdb;
        
        // Default empty array
        $ids = [];
        
        // Based on type, get relevant IDs from database
        switch ($type) {
            case 'category':
                // Get IDs from categories table
                $results = $wpdb->get_results("SELECT id FROM {$wpdb->prefix}app_categories LIMIT 1000");
                if ($results) {
                    foreach ($results as $row) {
                        $ids[] = $row->id;
                    }
                }
                break;
                
            case 'equipment':
                // Get IDs from equipment table
                $results = $wpdb->get_results("SELECT id FROM {$wpdb->prefix}app_equipments LIMIT 1000");
                if ($results) {
                    foreach ($results as $row) {
                        $ids[] = $row->id;
                    }
                }
                break;
                
            case 'licence':
                // Get IDs from licences table
                $results = $wpdb->get_results("SELECT id FROM {$wpdb->prefix}app_licences LIMIT 1000");
                if ($results) {
                    foreach ($results as $row) {
                        $ids[] = $row->id;
                    }
                }
                break;
        }
        
        // Always include some standard patterns
        $ids[] = 'list';
        $ids[] = 'stats';
        $ids[] = 'all';
        $ids[] = 'tree';
        
        return $ids;
    }

    /**
     * Helper method to generate cache key for DataTable
     * 
     * CATATAN: Method ini tidak lagi dibutuhkan, karena kita menggunakan
     * method get() dan set() yang sudah ada. Namun ditambahkan di sini
     * sebagai referensi jika dibutuhkan di masa depan.
     */
    private function generateDataTableCacheKey(string $context, array $components): string {
        $key_parts = ['datatable', $context];
        
        foreach ($components as $component) {
            if (is_scalar($component)) {
                $key_parts[] = (string)$component;
            } else {
                $key_parts[] = md5(serialize($component));
            }
        }
        
        return implode('_', $key_parts);
    }

    // Method untuk invalidate cache saat ada update equipment
    public function invalidateEquipmentCache(int $id): void {
        $this->delete('equipment_detail', $id);
        $this->delete('licence_count', $id);
        $this->delete('equipment', $id);
        // Clear equipment list cache
        $this->delete('equipment_total_count', get_current_user_id());
    }    

    /**
     * Clear specific cache group
     * @return bool True if cache was cleared successfully
     */
    private function clearCache(): bool {
        try {
            global $wp_object_cache;

            // Check if using default WordPress object cache
            if (isset($wp_object_cache->cache[self::CACHE_GROUP])) {
                if (is_array($wp_object_cache->cache[self::CACHE_GROUP])) {
                    foreach (array_keys($wp_object_cache->cache[self::CACHE_GROUP]) as $key) {
                        wp_cache_delete($key, self::CACHE_GROUP);
                    }
                }
                unset($wp_object_cache->cache[self::CACHE_GROUP]);
                return true;
            }

            // Alternative approach for external cache plugins
            if (function_exists('wp_cache_flush_group')) {
                // Some caching plugins provide group-level flush
                return wp_cache_flush_group(self::CACHE_GROUP);
            }

            // Fallback method - clear known cache keys
            $known_types = [
                'equipment',
                'equipment_list',
                'equipment_total_count',
                'equipment_stats',
                'licence',
                'licence_list',
                'licence_stats',
                'datatable'
            ];

            foreach ($known_types as $type) {
                if ($cached_keys = wp_cache_get($type . '_keys', self::CACHE_GROUP)) {
                    if (is_array($cached_keys)) {
                        foreach ($cached_keys as $key) {
                            wp_cache_delete($key, self::CACHE_GROUP);
                        }
                    }
                }
            }

            // Clear the master key list
            wp_cache_delete('cache_keys', self::CACHE_GROUP);

            return true;

        } catch (\Exception $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Error clearing cache: ' . $e->getMessage());
            }
            return false;
        }
    }

    /**
     * Clear all caches in group with enhanced error handling
     * @return bool True if cache was cleared successfully
     */
    public function clearAll(): bool {
        try {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Attempting to clear all caches in group: ' . self::CACHE_GROUP);
            }

            $result = $this->clearCache();

            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Cache clear result: ' . ($result ? 'success' : 'failed'));
            }

            return $result;
        } catch (\Exception $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Error in clearAll(): ' . $e->getMessage());
            }
            return false;
        }
    }

    /**
     * Clear all caches in group (alias method for backward compatibility)
     * @return bool True if cache was cleared successfully
     */
    public function clearAllCaches(): bool {
        return $this->clearAll();
    }

    /**
     * Clear equipment-specific caches
     * @param int $equipmentId Equipment ID
     * @return bool True if successful
     */
    public function clearEquipmentCaches(int $equipmentId): bool {
        try {
            $types = [
                'equipment',
                'equipment_detail',
                'equipment_stats',
                'licence_count'
            ];

            foreach ($types as $type) {
                $this->delete($type, $equipmentId);
            }

            // Clear related lists
            $this->delete('equipment_list');
            $this->delete('equipment_total_count', get_current_user_id());

            // Clear related DataTable caches
            $this->invalidateDataTableCache('equipment_list');
            $this->invalidateDataTableCache('equipment_licences', ['equipment_id' => $equipmentId]);

            return true;
        } catch (\Exception $e) {
            $this->debug_log('Error clearing equipment caches: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Clear licence-specific caches
     * @param int $licenceId Licence ID
     * @param int|null $equipmentId Optional equipment ID
     * @return bool True if successful
     */
    public function clearLicenceCaches(int $licenceId, ?int $equipmentId = null): bool {
        try {
            $types = [
                'licence',
                'licence_detail',
                'licence_stats'
            ];

            foreach ($types as $type) {
                $this->delete($type, $licenceId);
            }

            // Clear related lists
            $this->delete('licence_list');
            
            // If equipment ID is provided, clear related caches
            if ($equipmentId) {
                $this->delete('licence_count', $equipmentId);
                $this->delete('equipment_stats', $equipmentId);
                $this->invalidateDataTableCache('equipment_licences', ['equipment_id' => $equipmentId]);
            }

            // Clear related DataTable caches
            $this->invalidateDataTableCache('licence_list');

            return true;
        } catch (\Exception $e) {
            $this->debug_log('Error clearing licence caches: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Clear user-specific caches
     * @param int $userId User ID
     * @return bool True if successful
     */
    public function clearUserCaches(int $userId): bool {
        try {
            $types = [
                'user_equipments',
                'user_licences',
                'equipment_total_count'
            ];

            foreach ($types as $type) {
                $this->delete($type, $userId);
            }

            // Clear user-specific DataTable caches
            $this->invalidateDataTableCache('equipment_list', ['user_id' => $userId]);
            $this->invalidateDataTableCache('licence_list', ['user_id' => $userId]);

            return true;
        } catch (\Exception $e) {
            $this->debug_log('Error clearing user caches: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Run diagnostics on the cache system
     * 
     * @return array Diagnostic information
     */
    public function runDiagnostics(): array {
        $diagnostics = [
            'status' => 'unknown',
            'using_external_cache' => wp_using_ext_object_cache(),
            'cache_type' => 'Default WordPress Object Cache',
            'cache_group_exists' => false,
            'cache_test_success' => false,
            'cache_hit_rate' => null,
            'errors' => [],
            'tests' => []
        ];
        
        try {
            // Detect cache implementation type
            global $wp_object_cache;
            if ($wp_object_cache) {
                $cache_class = get_class($wp_object_cache);
                $diagnostics['cache_type'] = $cache_class;
                
                // Check if we're using a well-known external cache
                if (strpos($cache_class, 'Redis') !== false) {
                    $diagnostics['cache_type'] = 'Redis';
                } elseif (strpos($cache_class, 'Memcached') !== false) {
                    $diagnostics['cache_type'] = 'Memcached';
                }
            }
            
            // Test basic cache operations
            $test_key = 'wp_equipment_diagnostics_' . time();
            $test_value = 'test_value_' . rand(1000, 9999);
            
            // Test 1: Set operation
            $set_result = wp_cache_set($test_key, $test_value, self::CACHE_GROUP);
            $diagnostics['tests']['set'] = $set_result;
            
            // Test 2: Get operation
            $get_result = wp_cache_get($test_key, self::CACHE_GROUP);
            $diagnostics['tests']['get'] = ($get_result === $test_value);
            
            // Test 3: Delete operation
            $delete_result = wp_cache_delete($test_key, self::CACHE_GROUP);
            $diagnostics['tests']['delete'] = $delete_result;
            
            // Overall test success
            $diagnostics['cache_test_success'] = 
                $diagnostics['tests']['set'] && 
                $diagnostics['tests']['get'] && 
                $diagnostics['tests']['delete'];
            
            // Check if group exists in the cache object
            if (isset($wp_object_cache->cache) && is_array($wp_object_cache->cache)) {
                $diagnostics['cache_group_exists'] = isset($wp_object_cache->cache[self::CACHE_GROUP]);
            }
            
            // Try to get cache stats if available
            if (method_exists($wp_object_cache, 'getStats')) {
                $stats = $wp_object_cache->getStats();
                if (isset($stats['get_hits']) && isset($stats['get_misses'])) {
                    $hits = $stats['get_hits'];
                    $total = $hits + $stats['get_misses'];
                    if ($total > 0) {
                        $diagnostics['cache_hit_rate'] = round(($hits / $total) * 100, 2) . '%';
                    }
                }
            }
            
            // Set overall status
            if ($diagnostics['cache_test_success']) {
                $diagnostics['status'] = 'healthy';
            } else {
                $diagnostics['status'] = 'failing';
            }
            
        } catch (\Exception $e) {
            $diagnostics['status'] = 'error';
            $diagnostics['errors'][] = $e->getMessage();
        }
        
        // Log the diagnostics
        $this->debug_log('Cache diagnostics results: ' . print_r($diagnostics, true));
        
        return $diagnostics;
    }

    /**
     * Admin endpoint to generate cache diagnostics
     */
    public function handleDiagnosticsRequest() {
        try {
            // Check permissions
            if (!current_user_can('manage_options')) {
                wp_send_json_error(['message' => 'Insufficient permissions']);
                return;
            }
            
            // Run diagnostics
            $diagnostics = $this->runDiagnostics();
            
            // Add some helpful advice based on the results
            $diagnostics['recommendations'] = [];
            
            if (!$diagnostics['using_external_cache']) {
                $diagnostics['recommendations'][] = 'Consider installing a persistent object cache plugin like Redis or Memcached for better performance.';
            }
            
            if (!$diagnostics['cache_test_success']) {
                $diagnostics['recommendations'][] = 'Basic cache operations are failing. Check your cache configuration and wp-config.php settings.';
            }
            
            if ($diagnostics['cache_hit_rate'] !== null && floatval($diagnostics['cache_hit_rate']) < 50) {
                $diagnostics['recommendations'][] = 'Your cache hit rate is low. Consider increasing cache expiry times or reviewing cache invalidation logic.';
            }
            
            wp_send_json_success([
                'diagnostics' => $diagnostics
            ]);
            
        } catch (\Exception $e) {
            wp_send_json_error([
                'message' => $e->getMessage()
            ]);
        }
    }

}
