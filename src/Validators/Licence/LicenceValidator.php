<?php
/**
* Licence Validator Class
*
* @package     WP_Equipment
* @subpackage  Validators/Branch
* @version     1.0.0
* @author      arisciwek
*
* Path: src/Validators/Licence/LicenceValidator.php
*
* Description: Validator untuk operasi CRUD Surat Keterangan.
*              Memastikan semua input data valid sebelum diproses model.
*              Menyediakan validasi untuk create, update, dan delete.
*              Includes validasi permission dan ownership.
*
* Changelog:
* 1.0.0 - 2024-12-10
* - Initial release
* - Added create validation
* - Added update validation
* - Added delete validation
* - Added permission validation
*/

namespace WPEquipment\Validators\Licence;

use WPEquipment\Models\Licence\LicenceModel;
use WPEquipment\Models\EquipmentModel;

class LicenceValidator {
    private $licence_model;
    private $equipment_model;

    public function __construct() {
        $this->licence_model = new LicenceModel();
        $this->equipment_model = new EquipmentModel();
    }

    public function validateCreate(array $data): array {
        $errors = [];

        // Permission check
        if (!current_user_can('add_licence')) {
            $errors['permission'] = __('Anda tidak memiliki izin untuk menambah surat keterangan.', 'wp-equipment');
            return $errors;
        }

        // Equipment exists check
        $equipment_id = intval($data['equipment_id'] ?? 0);
        if (!$equipment_id || !$this->equipment_model->find($equipment_id)) {
            $errors['equipment_id'] = __('Equipment tidak valid.', 'wp-equipment');
            return $errors;
        }
        
        // Code validation
        $code = trim(sanitize_text_field($data['code'] ?? ''));
        if (empty($code)) {
            $errors['code'] = __('Kode surat keterangan wajib diisi.', 'wp-equipment');
        } elseif (!preg_match('/^\d{4}$/', $code)) {
            $errors['code'] = __('Kode surat keterangan harus berupa 4 digit angka.', 'wp-equipment');
        } elseif ($this->licence_model->existsByCode($code)) {
            $errors['code'] = __('Kode surat keterangan sudah ada.', 'wp-equipment');
        }

        // Name validation
        $name = trim(sanitize_text_field($data['name'] ?? ''));
        if (empty($name)) {
            $errors['name'] = __('Nama surat keterangan wajib diisi.', 'wp-equipment');
        } elseif (mb_strlen($name) > 100) {
            $errors['name'] = __('Nama surat keterangan maksimal 100 karakter.', 'wp-equipment');
        } elseif ($this->licence_model->existsByNameInEquipment($name, $equipment_id)) {
            $errors['name'] = __('Nama surat keterangan sudah ada di equipment ini.', 'wp-equipment');
        }

        // Type validation
        $type = trim(sanitize_text_field($data['type'] ?? ''));
        if (empty($type)) {
            $errors['type'] = __('Tipe surat keterangan wajib diisi.', 'wp-equipment');
        } elseif (!in_array($type, ['pertama', 'berkala'])) {
            $errors['type'] = __('Tipe surat keterangan tidak valid.', 'wp-equipment');
        }

        return $errors;
    }

    public function validateUpdate(array $data, int $id): array {
        $errors = [];

        // Check if licence exists
        $licence = $this->licence_model->find($id);
        if (!$licence) {
            $errors['id'] = __('Pertama/berkala tidak ditemukan.', 'wp-equipment');
            return $errors;
        }

        // Permission check
        if (!current_user_can('edit_all_licencees') &&
            (!current_user_can('edit_own_licence') || $licence->created_by !== get_current_user_id())) {
            $errors['permission'] = __('Anda tidak memiliki izin untuk mengedit surat keterangan ini.', 'wp-equipment');
            return $errors;
        }

        // Basic validation
        $name = trim(sanitize_text_field($data['name'] ?? ''));
        if (empty($name)) {
            $errors['name'] = __('Nama surat keterangan wajib diisi.', 'wp-equipment');
        }

        // Length check
        if (mb_strlen($name) > 100) {
            $errors['name'] = __('Nama surat keterangan maksimal 100 karakter.', 'wp-equipment');
        }

        // Unique check excluding current ID
        if ($this->licence_model->existsByNameInEquipment($name, $licence->equipment_id, $id)) {
            $errors['name'] = __('Nama surat keterangan sudah ada di equipment ini.', 'wp-equipment');
        }

        // Type validation if provided
        if (isset($data['type'])) {
            $type = trim(sanitize_text_field($data['type']));
            if (!in_array($type, ['pertama', 'berkala'])) {
                $errors['type'] = __('Tipe surat keterangan tidak valid.', 'wp-equipment');
            }
        }

        return $errors;
    }

    public function validateDelete(int $id): array {
        $errors = [];

        // Check if licence exists
        $licence = $this->licence_model->find($id);
        if (!$licence) {
            $errors['id'] = __('Pertama/berkala tidak ditemukan.', 'wp-equipment');
            return $errors;
        }

        // Permission check
        if (!current_user_can('delete_licence') &&
            (!current_user_can('delete_own_licence') || $licence->created_by !== get_current_user_id())) {
            $errors['permission'] = __('Anda tidak memiliki izin untuk menghapus surat keterangan ini.', 'wp-equipment');
        }

        return $errors;
    }

    /**
     * Validate view operation
     */
    public function validateView(int $id): array {
        $errors = [];

        // Check if licence exists
        $licence = $this->licence_model->find($id);
        if (!$licence) {
            $errors['id'] = __('Pertama/berkala tidak ditemukan.', 'wp-equipment');
            return $errors;
        }

        // Permission check
        if (!current_user_can('view_licence_detail') &&
            (!current_user_can('view_own_licence') || $licence->created_by !== get_current_user_id())) {
            $errors['permission'] = __('Anda tidak memiliki izin untuk melihat detail surat keterangan ini.', 'wp-equipment');
        }

        return $errors;
    }

    /**
     * Helper function to sanitize input data
     */
    public function sanitizeInput(array $data): array {
        $sanitized = [];

        if (isset($data['name'])) {
            $sanitized['name'] = trim(sanitize_text_field($data['name']));
        }

        if (isset($data['type'])) {
            $sanitized['type'] = trim(sanitize_text_field($data['type']));
        }

        if (isset($data['equipment_id'])) {
            $sanitized['equipment_id'] = intval($data['equipment_id']);
        }

        return $sanitized;
    }
}
