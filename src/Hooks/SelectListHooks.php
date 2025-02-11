<?php
/**
* Select List Hooks Class
*
* @package     WP_Equipment
* @subpackage  Hooks
* @version     1.0.0
* @author      arisciwek
*
* Path: /wp-equipment/src/Hooks/SelectListHooks.php
*
* Description: Hooks untuk mengelola select list equipment dan pertama.
*              Menyediakan filter dan action untuk render select lists.
*              Includes dynamic loading untuk pertama berdasarkan equipment.
*              Terintegrasi dengan cache system.
*
* Hooks yang tersedia:
* - wp_equipment_get_equipment_options (filter)
* - wp_equipment_get_licence_options (filter) 
* - wp_equipment_equipment_select (action)
* - wp_equipment_licence_select (action)
*
* Changelog:
* 1.0.0 - 2024-01-06
* - Initial implementation
* - Added equipment options filter
* - Added licence options filter
* - Added select rendering actions
* - Added cache integration
*/


namespace WPEquipment\Hooks;

use WPEquipment\Models\EquipmentModel;
use WPEquipment\Models\Licence\LicenceModel;
use WPEquipment\Cache\WPCache;

class SelectListHooks {
    private $equipment_model;
    private $licence_model;
    private $cache;
    private $debug_mode;

    public function __construct() {
        $this->equipment_model = new EquipmentModel();
        $this->licence_model = new LicenceModel();
        $this->cache = new WPCache();
        $this->debug_mode = apply_filters('wp_equipment_debug_mode', false);
        
        $this->registerHooks();
    }

    private function registerHooks() {
        // Register filters
        add_filter('wp_equipment_get_equipment_options', [$this, 'getEquipmentOptions'], 10, 2);
        add_filter('wp_equipment_get_licence_options', [$this, 'getBranchOptions'], 10, 3);
        
        // Register actions
        add_action('wp_equipment_equipment_select', [$this, 'renderEquipmentSelect'], 10, 2);
        add_action('wp_equipment_licence_select', [$this, 'renderBranchSelect'], 10, 3);
        
        // Register AJAX handlers
        add_action('wp_ajax_get_licence_options', [$this, 'handleAjaxBranchOptions']);
        add_action('wp_ajax_nopriv_get_licence_options', [$this, 'handleAjaxBranchOptions']);
    }

    /**
     * Get equipment options with caching
     */
    public function getEquipmentOptions(array $default_options = [], bool $include_empty = true): array {
        try {
            $cache_key = 'equipment_options_' . md5(serialize($default_options) . $include_empty);
            
            // Try to get from cache first
            $options = $this->cache->get($cache_key);
            if (false !== $options) {
                $this->debugLog('Retrieved equipment options from cache');
                return $options;
            }

            $options = $default_options;
            
            if ($include_empty) {
                $options[''] = __('Pilih Equipment', 'wp-equipment');
            }

            $equipments = $this->equipment_model->getAllEquipments();
            foreach ($equipments as $equipment) {
                $options[$equipment->id] = esc_html($equipment->name);
            }

            // Cache the results
            $this->cache->set($cache_key, $options);
            $this->debugLog('Cached new equipment options');

            return $options;

        } catch (\Exception $e) {
            $this->logError('Error getting equipment options: ' . $e->getMessage());
            return $default_options;
        }
    }

    /**
     * Get licence options with caching
     */
    public function getBranchOptions(array $default_options = [], ?int $equipment_id = null, bool $include_empty = true): array {
        try {
            if ($equipment_id) {
                $cache_key = "licence_options_{$equipment_id}_" . md5(serialize($default_options) . $include_empty);
                
                // Try cache first
                $options = $this->cache->get($cache_key);
                if (false !== $options) {
                    $this->debugLog("Retrieved licence options for equipment {$equipment_id} from cache");
                    return $options;
                }
            }

            $options = $default_options;
            
            if ($include_empty) {
                $options[''] = __('Pilih Surat Keterangan', 'wp-equipment');
            }

            if ($equipment_id) {
                $licencees = $this->licence_model->getByEquipment($equipment_id);
                foreach ($licencees as $licence) {
                    $options[$licence->id] = esc_html($licence->name);
                }

                // Cache the results
                $this->cache->set($cache_key, $options);
                $this->debugLog("Cached new licence options for equipment {$equipment_id}");
            }

            return $options;

        } catch (\Exception $e) {
            $this->logError('Error getting licence options: ' . $e->getMessage());
            return $default_options;
        }
    }

    /**
     * Render equipment select element
     */
    public function renderEquipmentSelect(array $attributes = [], ?int $selected_id = null): void {
        try {
            $default_attributes = [
                'name' => 'equipment_id',
                'id' => 'equipment_id',
                'class' => 'wp-equipment-equipment-select'
            ];

            $attributes = wp_parse_args($attributes, $default_attributes);
            $options = $this->getEquipmentOptions();

            $this->renderSelect($attributes, $options, $selected_id);

        } catch (\Exception $e) {
            $this->logError('Error rendering equipment select: ' . $e->getMessage());
            echo '<p class="error">' . esc_html__('Error loading equipment selection', 'wp-equipment') . '</p>';
        }
    }

    /**
     * Render licence select element
     */
    public function renderBranchSelect(array $attributes = [], ?int $equipment_id = null, ?int $selected_id = null): void {
        try {
            $default_attributes = [
                'name' => 'licence_id',
                'id' => 'licence_id',
                'class' => 'wp-equipment-licence-select'
            ];

            $attributes = wp_parse_args($attributes, $default_attributes);
            $options = $this->getBranchOptions([], $equipment_id);

            $this->renderSelect($attributes, $options, $selected_id);

        } catch (\Exception $e) {
            $this->logError('Error rendering licence select: ' . $e->getMessage());
            echo '<p class="error">' . esc_html__('Error loading licence selection', 'wp-equipment') . '</p>';
        }
    }

    /**
     * Handle AJAX request for licence options
     */
    public function handleAjaxBranchOptions(): void {
        try {
            if (!check_ajax_referer('wp_equipment_select_nonce', 'nonce', false)) {
                throw new \Exception('Invalid security token');
            }

            $equipment_id = isset($_POST['equipment_id']) ? absint($_POST['equipment_id']) : 0;
            if (!$equipment_id) {
                throw new \Exception('Invalid equipment ID');
            }

            $options = $this->getBranchOptions([], $equipment_id);
            $html = $this->generateOptionsHtml($options);

            wp_send_json_success(['html' => $html]);

        } catch (\Exception $e) {
            $this->logError('AJAX Error: ' . $e->getMessage());
            wp_send_json_error([
                'message' => __('Gagal memuat data surat keterangan', 'wp-equipment')
            ]);
        }
    }

    /**
     * Helper method to render select element
     */
    private function renderSelect(array $attributes, array $options, ?int $selected_id): void {
        ?>
        <select <?php echo $this->buildAttributes($attributes); ?>>
            <?php foreach ($options as $value => $label): ?>
                <option value="<?php echo esc_attr($value); ?>" 
                    <?php selected($selected_id, $value); ?>>
                    <?php echo esc_html($label); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php
    }

    /**
     * Generate HTML for select options
     */
    private function generateOptionsHtml(array $options): string {
        $html = '';
        foreach ($options as $value => $label) {
            $html .= sprintf(
                '<option value="%s">%s</option>',
                esc_attr($value),
                esc_html($label)
            );
        }
        return $html;
    }

    /**
     * Build HTML attributes string
     */
    private function buildAttributes(array $attributes): string {
        $html = '';
        foreach ($attributes as $key => $value) {
            if (is_bool($value)) {
                if ($value) {
                    $html .= sprintf(' %s', esc_attr($key));
                }
            } else {
                $html .= sprintf(' %s="%s"', esc_attr($key), esc_attr($value));
            }
        }
        return $html;
    }

    /**
     * Debug logging
     */
    private function debugLog(string $message): void {
        if ($this->debug_mode) {
            error_log('WP Select Debug: ' . $message);
        }
    }

    /**
     * Error logging
     */
    private function logError(string $message): void {
        error_log('WP Select Error: ' . $message);
    }
}
