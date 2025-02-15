<?php
/**
 * Partial Template untuk Hierarki Kategori
 * /wp-equipment/src/Views/templates/category/partials/_category_hierarchy.php
 * @package WP_Equipment
 * @subpackage Views/Templates/Category/Partials
 */

defined('ABSPATH') || exit;

/**
 * @var array $categories Data kategori dalam format hierarki
 */
?>

<div id="category-hierarchy" class="tab-content">

    <div class="category-hierarchy-wrapper">
        <?php if (empty($categories)): ?>
            <p class="no-categories"><?php _e('Belum ada kategori', 'wp-equipment'); ?></p>
        <?php else: ?>
            <ul class="category-tree">
                <?php foreach ($categories as $category): ?>
                    <li class="category-item" data-id="<?php echo esc_attr($category->id); ?>">
                        <div class="category-info">
                            <span class="category-code"><?php echo esc_html($category->code); ?></span>
                            <span class="category-name"><?php echo esc_html($category->name); ?></span>
                            <?php if (!empty($category->unit) || !empty($category->pnbp)): ?>
                                <small class="category-meta">
                                    <?php if (!empty($category->unit)): ?>
                                        <span class="unit"><?php echo esc_html($category->unit); ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($category->pnbp)): ?>
                                        <span class="pnbp">
                                            <?php echo number_format($category->pnbp, 0, ',', '.'); ?>
                                        </span>
                                    <?php endif; ?>
                                </small>
                            <?php endif; ?>
                        </div>
                        
                        <?php if (!empty($category->children)): ?>
                            <ul class="subcategories">
                                <?php foreach ($category->children as $child): ?>
                                    <li class="category-item" data-id="<?php echo esc_attr($child->id); ?>">
                                        <div class="category-info">
                                            <span class="category-code"><?php echo esc_html($child->code); ?></span>
                                            <span class="category-name"><?php echo esc_html($child->name); ?></span>
                                            <?php if (!empty($child->unit) || !empty($child->pnbp)): ?>
                                                <small class="category-meta">
                                                    <?php if (!empty($child->unit)): ?>
                                                        <span class="unit"><?php echo esc_html($child->unit); ?></span>
                                                    <?php endif; ?>
                                                    <?php if (!empty($child->pnbp)): ?>
                                                        <span class="pnbp">
                                                            <?php echo number_format($child->pnbp, 0, ',', '.'); ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>


</div>

<style>
.category-hierarchy-wrapper {
    margin: 15px 0;
}

.category-tree {
    list-style: none;
    padding: 0;
    margin: 0;
}

.category-item {
    margin: 5px 0;
    padding: 5px 0;
}

.subcategories {
    list-style: none;
    padding-left: 20px;
    margin: 5px 0;
    border-left: 1px solid #ddd;
}

.category-info {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 5px;
    background: #f8f9fa;
    border-radius: 4px;
}

.category-code {
    font-weight: bold;
    color: #666;
    min-width: 50px;
}

.category-name {
    flex: 1;
}

.category-meta {
    color: #666;
    font-size: 0.9em;
}

.category-meta .unit {
    margin-right: 10px;
}

.category-meta .pnbp {
    color: #28a745;
}

.no-categories {
    color: #666;
    font-style: italic;
    padding: 10px;
    text-align: center;
}
</style>
