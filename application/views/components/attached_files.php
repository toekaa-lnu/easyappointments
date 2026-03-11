<?php
/**
 * Local variables.
 *
 * @var bool $disabled (false)
 */
$disabled = $disabled ?? false;
$max_attached_files = boolval(setting('attached_files_supported', 0)) ? setting('max_attached_files', 0) : 0;
$max_attached_files_text = intval($max_attached_files) === 1 ? lang('attached_files_one') : sprintf(lang('attached_files_multiple'), $max_attached_files);
$attached_files_allowed_types_hint = lang(setting('attached_files_allowed_types_hint',''));
$attached_files_hint = sprintf(lang('attached_files_user_hint'), $max_attached_files_text) . ' ' . $attached_files_allowed_types_hint;
$allowed_attached_file_types = setting('attached_files_allowed_types') ?? '';
?>

<?php if ($max_attached_files > 0): ?>
    <div class="mb-3">
        <label for="attached-file-names" class="form-label">
            <?= lang('attached_files') ?>
        </label>
        <?php if (!$disabled): ?>
            <input type="text" id="attached-file-names" style="display: none;">
            <input type="text" id="existing-file-names" style="display: none;">
            <input type="text" id="discarded-file-names" style="display: none;">
            <div class="form-text text-muted">
                <small><?= $attached_files_hint ?></small>
            </div>

            <?php for ($f = 1; $f <= $max_attached_files; $f++): ?>
                <div class="existing-file-name-row" id="existing-file-name-row-<?= $f ?>"  file-index="<?= $f ?>">
                    <small id="existing-file-name-<?= $f ?>">Filename <?= $f ?></small>
                    <small>(<?= e(lang('previously_attached')) ?>)</small>
                    <span onclick="var elem = $('#existing-file-name-row-<?= $f ?>'); elem.trigger('change');">
                        <i class="fas fa-times-circle discard-file-button"></i>
                        <!--
                        <i class="fas fa-cloud discard-file-button"></i><i class="fas fa-times discard-file-button"></i>
                        -->
                    </span>
                </div>
            <?php endfor; ?>

            <?php for ($f = 1; $f <= $max_attached_files; $f++): ?>
                <input type="file" class="form-control attached-file-input" style="display: none;" file-index="<?= $f ?>" id="attached-file-input-<?= $f ?>" accept=<?=$allowed_attached_file_types?>>
            <?php endfor; ?>
            <button class="file-button btn btn-sm btn-outline-dark" id="attach-file-button" type="button"><?= lang('attach_file_button') ?></button>
            <?php for ($f = 1; $f <= $max_attached_files; $f++): ?>
                <div class="attached-file-name-row" id="attached-file-name-row-<?= $f ?>">
                    <span id="attached-file-name-<?= $f ?>">Attached filename <?= $f ?></span>
                    <span onclick="var elem = $('#attached-file-input-<?= $f ?>'); elem.val(null); elem.trigger('change');">
                        <i class="fas fa-times-circle discard-file-button"></i>
                    </span>
                </div>
            <?php endfor; ?>
        <?php else: ?>
            <?php for ($f = 1; $f <= $max_attached_files; $f++): ?>
                <div class="attached-file-name-row" id="attached-file-name-row-<?= $f ?>">
                    <span id="attached-file-name-<?= $f ?>">Filename <?= $f ?></span>
                </div>
            <?php endfor; ?>
        <?php endif; ?>
    </div>
<?php endif; ?>
