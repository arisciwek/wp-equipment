<?php
/**
* Category Validator Class
*
* @package     WP_Equipment
* @subpackage  Validators
* @version     1.0.0
* @author      arisciwek
*
* Path: /wp-equipment/src/Validators/CategoryValidator.php
*
* Description: Validator untuk data kategori.
*              Handles validasi input untuk operasi CRUD.
*              Includes validasi untuk:
*              - Unique code checking
*              - Required fields
*              - Parent-child relationships
*              - Level validation
*              - PNBP format
*
* Changelog:
* v1.0.0 - 2024-02-10
* - Initial version
* - Added CRUD validation methods
* - Added parent category validation
* - Added code uniqueness check
*/

namespace WPEquipment\Validators;
namespace WPEquipment\Validators;

use WPEquipment\Models\CategoryModel;

class CategoryValidator {
    private CategoryModel $model;

    public function __construct() {
        $this->model = new CategoryModel();
    }

    public function validateCreate(array $data): array {
        $errors = [];

        if (empty($data['code'])) {
            $errors[] = __('Code is required', 'wp-equipment');
        } elseif ($this->model->existsByCode($data['code'])) {
            $errors[] = __('Code already exists', 'wp-equipment');
        }

        if (empty($data['name'])) {
            $errors[] = __('Name is required', 'wp-equipment');
        }

        if (!isset($data['level']) || $data['level'] < 1) {
            $errors[] = __('Valid level is required', 'wp-equipment');
        }

        if (!empty($data['parent_id'])) {
            if (!$this->validateParentCategory($data['parent_id'], $data['level'])) {
                $errors[] = __('Invalid parent category', 'wp-equipment');
            }
        }

        if (!empty($data['pnbp']) && (!is_numeric($data['pnbp']) || $data['pnbp'] < 0)) {
            $errors[] = __('PNBP must be a non-negative number', 'wp-equipment');
        }

        return $errors;
    }

    public function validateUpdate(array $data, int $id): array {
        $errors = [];

        if (empty($data['code'])) {
            $errors[] = __('Code is required', 'wp-equipment');
        } elseif ($this->model->existsByCode($data['code'], $id)) {
            $errors[] = __('Code already exists', 'wp-equipment');
        }

        if (empty($data['name'])) {
            $errors[] = __('Name is required', 'wp-equipment');
        }

        if (!isset($data['level']) || $data['level'] < 1) {
            $errors[] = __('Valid level is required', 'wp-equipment');
        }

        if (!empty($data['parent_id'])) {
            if ($data['parent_id'] === $id) {
                $errors[] = __('Category cannot be its own parent', 'wp-equipment');
            } elseif (!$this->validateParentCategory($data['parent_id'], $data['level'])) {
                $errors[] = __('Invalid parent category', 'wp-equipment');
            }
        }

        if (!empty($data['pnbp']) && (!is_numeric($data['pnbp']) || $data['pnbp'] < 0)) {
            $errors[] = __('PNBP must be a non-negative number', 'wp-equipment');
        }

        return $errors;
    }

    private function validateParentCategory(int $parentId, int $childLevel): bool {
        $parent = $this->model->find($parentId);
        if (!$parent) {
            return false;
        }

        return $parent->level < $childLevel;
    }
}
