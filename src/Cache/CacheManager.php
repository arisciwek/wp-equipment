<?php
/**
 * Cache Management Class
 *
 * @package     WP_Equipment
 * @subpackage  Cache
 * @version     1.0.0 
 * @author      arisciwek
 *
 * Path: /wp-equipment/src/Cache/CacheManager.php
 */

namespace WPEquipment\Cache;

class CacheManager {
    private const CACHE_GROUP = 'wp_equipment';
    private const CACHE_EXPIRY = 12 * HOUR_IN_SECONDS;
    
    // Cache keys
    private const KEY_EQUIPMENT = 'equipment_';
    private const KEY_EQUIPMENT_LIST = 'equipment_list';

    public function getEquipment(int $id): ?object {
        return wp_cache_get(self::KEY_EQUIPMENT . $id, self::CACHE_GROUP);
    }

    public function setEquipment(int $id, object $data): bool {
        return wp_cache_set(
            self::KEY_EQUIPMENT . $id, 
            $data, 
            self::CACHE_GROUP, 
            self::CACHE_EXPIRY
        );
    }

    public function invalidateEquipmentCache(int $id): void {
        wp_cache_delete(self::KEY_EQUIPMENT . $id, self::CACHE_GROUP);
        wp_cache_delete(self::KEY_EQUIPMENT_LIST, self::CACHE_GROUP);
    }

    public function getEquipmentList(): ?array {
        return wp_cache_get(self::KEY_EQUIPMENT_LIST, self::CACHE_GROUP);
    }

    public function setEquipmentList(array $data): bool {
        return wp_cache_set(
            self::KEY_EQUIPMENT_LIST,
            $data,
            self::CACHE_GROUP,
            self::CACHE_EXPIRY
        );
    }
}