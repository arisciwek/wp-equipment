# Penggunaan Select List WP Equipment

## Setup Awal

### 1. Dependensi
Sebelum menggunakan select list, pastikan semua dependensi telah terpenuhi:

- jQuery
- WordPress Core
- EquipmentToast untuk notifikasi (opsional)

### 2. Enqueue Scripts dan Styles

```php
// Di file plugin Anda
add_action('admin_enqueue_scripts', function($hook) {
    // Cek apakah sedang di halaman yang membutuhkan select
    if ($hook === 'your-page.php') {
        // Enqueue script
        wp_enqueue_script(
            'wp-equipment-select-handler',
            WP_EQUIPMENT_URL . 'assets/js/components/select-handler.js',
            ['jquery'],
            WP_EQUIPMENT_VERSION,
            true
        );

        // Setup data untuk JavaScript
        wp_localize_script('wp-equipment-select-handler', 'wpEquipmentData', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wp_equipment_select_nonce'),
            'texts' => [
                'select_licence' => __('Pilih Surat Keterangan', 'wp-equipment'),
                'loading' => __('Memuat...', 'wp-equipment'),
                'error' => __('Gagal memuat data', 'wp-equipment')
            ]
        ]);

        // Enqueue EquipmentToast jika digunakan
        wp_enqueue_script('equipment-toast');
        wp_enqueue_style('equipment-toast-style');
    }
});
```

### 3. Integrasi Cache System

```php
// Mengaktifkan cache
add_filter('wp_equipment_enable_cache', '__return_true');

// Konfigurasi durasi cache (dalam detik)
add_filter('wp_equipment_cache_duration', function() {
    return 3600; // 1 jam
});
```

## Penggunaan Hook

### 1. Filter untuk Data Options

```php
// Mendapatkan options equipment dengan cache
$equipment_options = apply_filters('wp_equipment_get_equipment_options', [
    '' => __('Pilih Equipment', 'your-textdomain')
], true); // Parameter kedua untuk include_empty

// Mendapatkan options surat keterangan dengan cache
$licence_options = apply_filters(
    'wp_equipment_get_licence_options',
    [],
    $equipment_id,
    true // Parameter ketiga untuk include_empty
);
```

### 2. Action untuk Render Select

```php
// Render equipment select dengan atribut lengkap
do_action('wp_equipment_equipment_select', [
    'name' => 'my_equipment',
    'id' => 'my_equipment_field',
    'class' => 'my-select-class wp-equipment-equipment-select',
    'data-placeholder' => __('Pilih Equipment', 'your-textdomain'),
    'required' => 'required',
    'aria-label' => __('Pilih Equipment', 'your-textdomain')
], $selected_equipment_id);

// Render licence select dengan loading state
do_action('wp_equipment_licence_select', [
    'name' => 'my_licence',
    'id' => 'my_licence_field',
    'class' => 'my-select-class wp-equipment-licence-select',
    'data-loading-text' => __('Memuat...', 'your-textdomain'),
    'required' => 'required',
    'aria-label' => __('Pilih Surat Keterangan', 'your-textdomain')
], $equipment_id, $selected_licence_id);
```

## Implementasi JavaScript

### 1. Event Handling

```javascript
(function($) {
    'use strict';

    const WPSelect = {
        init() {
            this.bindEvents();
            this.setupLoadingState();
        },

        bindEvents() {
            $(document).on('change', '.wp-equipment-equipment-select', this.handleEquipmentChange.bind(this));
            $(document).on('wilayah:loaded', '.wp-equipment-licence-select', this.handleBranchLoaded.bind(this));
        },

        setupLoadingState() {
            this.$loadingIndicator = $('<span>', {
                class: 'wp-equipment-loading',
                text: wpEquipmentData.texts.loading
            }).hide();
        },

        handleEquipmentChange(e) {
            const $equipment = $(e.target);
            const $licence = $('.wp-equipment-licence-select');
            const equipmentId = $equipment.val();

            // Reset dan disable licence select
            this.resetBranchSelect($licence);

            if (!equipmentId) return;

            // Show loading state
            this.showLoading($licence);

            // Make AJAX call
            $.ajax({
                url: wpEquipmentData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'get_licence_options',
                    equipment_id: equipmentId,
                    nonce: wpEquipmentData.nonce
                },
                success: (response) => {
                    if (response.success) {
                        $licence.html(response.data.html);
                        $licence.trigger('wilayah:loaded');
                    } else {
                        this.handleError(response.data.message);
                    }
                },
                error: (jqXHR, textStatus, errorThrown) => {
                    this.handleError(errorThrown);
                },
                complete: () => {
                    this.hideLoading($licence);
                }
            });
        },

        resetBranchSelect($licence) {
            $licence.prop('disabled', true)
                   .html(`<option value="">${wpEquipmentData.texts.select_licence}</option>`);
        },

        showLoading($element) {
            $element.prop('disabled', true);
            this.$loadingIndicator.insertAfter($element).show();
        },

        hideLoading($element) {
            $element.prop('disabled', false);
            this.$loadingIndicator.hide();
        },

        handleError(message) {
            console.error('WP Select Error:', message);
            if (typeof EquipmentToast !== 'undefined') {
                EquipmentToast.error(message || wpEquipmentData.texts.error);
            }
        },

        handleBranchLoaded(e) {
            const $licence = $(e.target);
            // Custom handling setelah data loaded
        }
    };

    $(document).ready(() => WPSelect.init());

})(jQuery);
```

## Integrasi Cache System

Plugin ini menggunakan sistem cache WordPress untuk optimasi performa:

### 1. Cache Implementation

```php
class WPCache {
    private $cache_enabled;
    private $cache_duration;
    
    public function __construct() {
        $this->cache_enabled = apply_filters('wp_equipment_enable_cache', true);
        $this->cache_duration = apply_filters('wp_equipment_cache_duration', 3600);
    }
    
    public function get($key) {
        if (!$this->cache_enabled) return false;
        return wp_cache_get($key, 'wp_equipment');
    }
    
    public function set($key, $data) {
        if (!$this->cache_enabled) return false;
        return wp_cache_set($key, $data, 'wp_equipment', $this->cache_duration);
    }
    
    public function delete($key) {
        return wp_cache_delete($key, 'wp_equipment');
    }
}
```

### 2. Penggunaan Cache

```php
// Di SelectListHooks.php
public function getEquipmentOptions(array $default_options = [], bool $include_empty = true): array {
    $cache = new WPCache();
    $cache_key = 'equipment_options_' . md5(serialize($default_options) . $include_empty);
    
    $options = $cache->get($cache_key);
    if (false !== $options) {
        return $options;
    }
    
    $options = $this->buildEquipmentOptions($default_options, $include_empty);
    $cache->set($cache_key, $options);
    
    return $options;
}
```

## Error Handling & Debugging

### 1. PHP Error Handling

```php
try {
    // Operasi database atau file
} catch (\Exception $e) {
    error_log('WP Equipment Plugin Error: ' . $e->getMessage());
    wp_send_json_error([
        'message' => __('Terjadi kesalahan saat memproses data', 'wp-equipment')
    ]);
}
```

### 2. JavaScript Debugging

```javascript
// Aktifkan mode debug
add_filter('wp_equipment_debug_mode', '__return_true');

// Di JavaScript
if (wpEquipmentData.debug) {
    console.log('Equipment changed:', equipmentId);
    console.log('AJAX response:', response);
}
```

## Testing & Troubleshooting

### 1. Unit Testing

```php
class WPSelectTest extends WP_UnitTestCase {
    public function test_equipment_options() {
        $hooks = new SelectListHooks();
        $options = $hooks->getEquipmentOptions();
        
        $this->assertIsArray($options);
        $this->assertArrayHasKey('', $options);
    }
}
```

### 2. Common Issues & Solutions

1. **Select Pertama Tidak Update**
   - Periksa Console Browser
   - Validasi nonce
   - Pastikan hook AJAX terdaftar

2. **Cache Tidak Bekerja**
   - Periksa Object Cache aktif
   - Validasi cache key
   - Cek durasi cache

3. **Loading State Tidak Muncul**
   - Periksa CSS terload
   - Validasi selector JavaScript
   - Cek konflik jQuery

## Support & Maintenance

### 1. Reporting Issues
- Gunakan GitHub Issues
- Sertakan error log
- Berikan langkah reproduksi

### 2. Development Workflow
1. Fork repository
2. Buat licence fitur
3. Submit pull request
4. Tunggu review

### 3. Kontribusi
- Ikuti coding standards
- Dokumentasikan perubahan
- Sertakan unit test

## Changelog

### Version 1.1.0 (2024-01-07)
- Implementasi loading state
- Perbaikan error handling
- Optimasi cache system
- Update dokumentasi

### Version 1.0.0 (2024-01-06)
- Initial release
- Basic select functionality
- Equipment-licence relation
