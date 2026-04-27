<?php
/**
 * @var string $attributes
 * @var array $custom_colors
 */

$default_colors = [
    "#7cbae8",
    "#acbefb",
    "#82e4ec",
    "#7cebc1",
    "#abe9a4",
    "#ebe07c",
    "#f3bc7d",
    "#f3aea6",
    "#eb8687",
    "#dfaffe",
    "#e3e3e3"
];
$colors = $custom_colors ?? $default_colors;
?>

<?php section('styles'); ?>

<link rel="stylesheet" type="text/css" href="<?= asset_url('assets/css/components/color_selection.css') ?>">

<?php end_section('styles'); ?>

<label class="form-label"><?= lang('color') ?></label>

<div <?= $attributes ?? '' ?> class="color-selection d-flex justify-content-between mb-4">
    <?php for ($i = 0; $i < count($colors); $i++): ?>
        <button type="button" class="color-selection-option selected" data-value="<?= $colors[$i] ?>">
            <i class="fas fa-check"></i>
        </button>
    <?php endfor; ?>
</div>

<?php section('scripts'); ?>

<script src="<?= asset_url('assets/js/components/color_selection.js') ?>"></script>

<?php end_section('scripts'); ?>
