<?php extend('layouts/message_layout'); ?>

<?php section('content'); ?>

<div>
    <img id="success-icon" class="mt-0 mb-5" src="<?= base_url('assets/img/success.png') ?>" alt="success"/>
</div>

<div class="mb-5">
    <h4 class="mb-5"><?= lang('appointment_registered') ?></h4>

    <p>
        <?= lang('appointment_details_was_sent_to_you') ?>
    </p>

    <p class="mb-5 text-muted">
        <small>
            <?= lang('check_spam_folder') ?>
        </small>
    </p>

    <?php
        $custom_link = setting('booking_custom_message_confirm_link', '');
        $custom_link_text = setting('booking_custom_message_confirm_link_text', '');
        $custom_link_hidden = (setting('booking_custom_messages_enabled', 0) == 0)
            || ($custom_link == '')
            || ($custom_link_text == '')
            || ($custom_link_text == lang($custom_link_text));
    ?>

    <?php if ($custom_link_hidden): ?>
        <a href="<?= setting('booking_confirmation_link') ?: site_url() ?>" class="btn btn-primary btn-large">
            <i class="fas fa-calendar-alt me-2"></i>
            <?= lang('go_to_booking_page') ?>
        </a>
        <a href="<?= vars('add_to_google_url') ?>" id="add-to-google-calendar" class="btn btn-primary" target="_blank">
            <i class="fas fa-plus me-2"></i>
            <?= lang('add_to_google_calendar') ?>
        </a>
    <?php else: ?>
        <a href="<?= lang($custom_link) ?>" id="custom-message-confirm-link">
            <?= lang($custom_link_text) ?>
        </a>
    <?php endif; ?>

</div>

<?php end_section('content'); ?>

<?php section('scripts'); ?>

<?php component('google_analytics_script', ['google_analytics_code' => vars('google_analytics_code')]); ?>
<?php component('matomo_analytics_script', [
    'matomo_analytics_url' => vars('matomo_analytics_url'),
    'matomo_analytics_site_id' => vars('matomo_analytics_site_id'),
]); ?>

<?php end_section('scripts'); ?>
