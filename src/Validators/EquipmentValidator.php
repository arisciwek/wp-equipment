<?php
/**
* Equipment Validator Class
*
* @package     WP_Equipment
* @subpackage  Validators
* @version     1.0.0
* @author      arisciwek
*
* Path: src/Validators/EquipmentValidator.php
*
* Description: Validator untuk operasi CRUD Equipment.
*              Memastikan semua input data valid sebelum diproses model.
*              Menyediakan validasi untuk create, update, dan delete.
*              Includes validasi permission dan ownership.
*
* Changelog:
* 1.0.1 - 2024-12-08
* - Added view_own_equipment validation in validateView method
* - Updated permission validation messages
* - Enhanced error handling for permission checks
*
* Changelog:
* 1.0.0 - 2024-12-02 15:00:00
* - Initial release
* - Added create validation
* - Added update validation
* - Added delete validation
* - Added permission validation
*
* Dependencies:
* - WPEquipment\Models\EquipmentModel for data checks
* - WordPress sanitization functions
*/

namespace WPEquipment\Validators;

use WPEquipment\Models\EquipmentModel;

class EquipmentValidator {
   private $equipment_model;

   public function __construct() {
       $this->equipment_model = new EquipmentModel();
   }

   /**
    * Validate create operation
    */
    public function validateCreate(array $data): array {
        $errors = [];

        // Permission check
        if (!current_user_can('add_equipment')) {
            $errors['permission'] = __('Anda tidak memiliki izin untuk menambah equipment.', 'wp-equipment');
            return $errors;
        }

        // Code validation
        $code = trim(sanitize_text_field($data['code'] ?? ''));
        if (empty($code)) {
            $errors['code'] = __('Kode equipment wajib diisi.', 'wp-equipment');
        } elseif (!preg_match('/^\d{2}$/', $code)) {
            $errors['code'] = __('Kode equipment harus berupa 2 digit angka.', 'wp-equipment');
        } elseif ($this->equipment_model->existsByCode($code)) {
            $errors['code'] = __('Kode equipment sudah ada.', 'wp-equipment');
        }

        // Name validation
        $name = trim(sanitize_text_field($data['name'] ?? ''));
        if (empty($name)) {
            $errors['name'] = __('Nama equipment wajib diisi.', 'wp-equipment');
        } elseif (mb_strlen($name) > 100) {
            $errors['name'] = __('Nama equipment maksimal 100 karakter.', 'wp-equipment');
        } elseif ($this->equipment_model->existsByName($name)) {
            $errors['name'] = __('Nama equipment sudah ada.', 'wp-equipment');
        }

        return $errors;
    }

   /**
    * Validate update operation
    */

    public function validateUpdate(array $data, int $id): array {
        $errors = [];

        // Check if equipment exists
        $equipment = $this->equipment_model->find($id);
        if (!$equipment) {
            $errors['id'] = __('Equipment tidak ditemukan.', 'wp-equipment');
            return $errors;
        }

        // Permission check
        if (!current_user_can('edit_all_equipments') &&
            (!current_user_can('edit_own_equipment') || $equipment->created_by !== get_current_user_id())) {
            $errors['permission'] = __('Anda tidak memiliki izin untuk mengedit equipment ini.', 'wp-equipment');
            return $errors;
        }

        // Basic validation
        $name = trim(sanitize_text_field($data['name'] ?? ''));
        if (empty($name)) {
            $errors['name'] = __('Nama equipment wajib diisi.', 'wp-equipment');
        }

        // Validate code
        $code = trim(sanitize_text_field($data['code'] ?? ''));
        if (empty($code)) {
            $errors['code'] = __('Kode equipment wajib diisi.', 'wp-equipment');
        } elseif (!preg_match('/^[0-9]{2}$/', $code)) {
            $errors['code'] = __('Kode equipment harus 2 digit angka.', 'wp-equipment');
        }

        // Length check
        if (mb_strlen($name) > 100) {
            $errors['name'] = __('Nama equipment maksimal 100 karakter.', 'wp-equipment');
        }

        // Unique check excluding current ID
        if ($this->equipment_model->existsByName($name, $id)) {
            $errors['name'] = __('Nama equipment sudah ada.', 'wp-equipment');
        }

        // Check if code is unique (excluding current equipment)
        if ($this->equipment_model->existsByCode($code, $id)) {
            $errors['code'] = __('Kode equipment sudah digunakan.', 'wp-equipment');
        }

        return $errors;
    }

   /**
    * Validate delete operation
    */
   public function validateDelete(int $id): array {
       $errors = [];

       // Check if equipment exists
       $equipment = $this->equipment_model->find($id);
       if (!$equipment) {
           $errors['id'] = __('Equipment tidak ditemukan.', 'wp-equipment');
           return $errors;
       }

       // Permission check
       if (!current_user_can('delete_equipment') &&
           (!current_user_can('delete_own_equipment') || $equipment->created_by !== get_current_user_id())) {
           $errors['permission'] = __('Anda tidak memiliki izin untuk menghapus equipment ini.', 'wp-equipment');
           return $errors;
       }

       // Check for existing licencees
       if ($this->equipment_model->getBranchCount($id) > 0) {
           $errors['dependencies'] = __('Equipment tidak dapat dihapus karena masih memiliki surat keterangan.', 'wp-equipment');
       }

       return $errors;
   }

   /**
    * Validate view operation
    */
    public function validateView(int $id): array {
        $errors = [];

        // Check if equipment exists
        $equipment = $this->equipment_model->find($id);
        if (!$equipment) {
            $errors['id'] = __('Equipment tidak ditemukan.', 'wp-equipment');
            return $errors;
        }

        // Permission check - update ini
        if (!current_user_can('view_equipment_detail') &&
            (!current_user_can('view_own_equipment') || $equipment->created_by !== get_current_user_id())) {
            $errors['permission'] = __('Anda tidak memiliki izin untuk melihat detail equipment ini.', 'wp-equipment');
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

       return $sanitized;
   }
}
