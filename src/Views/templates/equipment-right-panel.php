
<div class="wp-equipment-panel-header">
    <h2>Detail Equipment: <span id="equipment-header-name"></span></h2>
    <button type="button" class="wp-equipment-close-panel">×</button>
</div>

<div class="wp-equipment-panel-content">
<div class="nav-tab-wrapper">
    <a href="#" class="nav-tab nav-tab-equipment-details nav-tab-active" data-tab="equipment-details">Data Peralatan</a>
    <a href="#" class="nav-tab" data-tab="membership-info">Membership</a>
    <a href="#" class="nav-tab" data-tab="licence-list">Surat Keterangan</a>
</div>

    <?php
    // Include partial templates
    include WP_EQUIPMENT_PATH . 'src/Views/templates/equipment/partials/_equipment_details.php';
    include WP_EQUIPMENT_PATH . 'src/Views/templates/equipment/partials/_equipment_membership.php';
    include WP_EQUIPMENT_PATH . 'src/Views/templates/licence/partials/_licence_list.php';
    ?>
</div>
