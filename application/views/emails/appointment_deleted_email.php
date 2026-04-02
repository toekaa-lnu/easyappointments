<?php
/**
 * Local variables.
 *
 * @var array $appointment
 * @var array $service
 * @var array $provider
 * @var array $customer
 * @var array $settings
 * @var array $timezone
 * @var string $reason
 */
$max_custom_fields = config('max_custom_fields', 5);
$max_appt_custom_fields = config('max_appt_custom_fields', 5);
$max_attached_files = intval(setting('attached_files_supported', 0)) === 1 ? setting('max_attached_files', 0) : 0;

// Calculate the duration by subtracting cooldown from the start-end difference
$cooldown = $service['cooldown'];
$start_datetime_object = new DateTime($appointment['start_datetime']);
$end_datetime_object = new DateTime($appointment['end_datetime']);
$end_datetime_object->sub(new DateInterval('PT' . $cooldown . 'M'));
$diff_dateinterval_object = $start_datetime_object->diff($end_datetime_object);
$customer_duration_minutes = $diff_dateinterval_object->days * 24 * 60;
$customer_duration_minutes += $diff_dateinterval_object->h * 60;
$customer_duration_minutes += $diff_dateinterval_object->i;
$customer_duration_text = sprintf('%s %s', $customer_duration_minutes, lang('minutes'));
?>

<html lang="en">
<head>
    <title><?= lang('appointment_cancelled_title') ?> | Easy!Appointments</title>
</head>
<body style="font: 13px arial, helvetica, tahoma;">

<div class="email-container" style="width: 650px; border: 1px solid #eee; margin: 30px auto;">
    <div id="header"
         style="background-color: <?= $settings['company_color'] ?? '#429a82' ?>; height: 45px; padding: 10px 15px;">
        <strong id="logo" style="color: white; font-size: 20px; margin-top: 10px; display: inline-block">
            <?= e($settings['company_name']) ?>
        </strong>
    </div>

    <div id="content" style="padding: 10px 15px; min-height: 400px;">
        <h2>
            <?= lang('appointment_cancelled_title') ?>
        </h2>

        <p>
            <?= lang('appointment_removed_from_schedule') ?>
        </p>

        <h2>
            <?= lang('appointment_details_title') ?>
        </h2>

        <table id="appointment-details">
            <tr>
                <td class="label" style="padding: 3px;font-weight: bold;">
                    <?= lang('service') ?>
                </td>
                <td style="padding: 3px;">
                    <?= e($service['name']) ?>
                </td>
            </tr>
            <tr>
                <td class="label" style="padding: 3px;font-weight: bold;">
                    <?= lang('provider') ?>
                </td>
                <td style="padding: 3px;">
                    <?= e($provider['first_name'] . ' ' . $provider['last_name']) ?>
                </td>
            </tr>
            <tr>
                <td class="label" style="padding: 3px;font-weight: bold;">
                    <?= lang('start') ?>
                </td>
                <td style="padding: 3px;">
                    <?= format_date_time($appointment['start_datetime']) ?>
                </td>
            </tr>
            <tr>
                <td class="label" style="padding: 3px;font-weight: bold;">
                    <?= lang('duration') ?>
                </td>
                <td style="padding: 3px;">
                    <?= $customer_duration_text ?>

                </td>
            </tr>
            <tr>
                <td class="label" style="padding: 3px;font-weight: bold;">
                    <?= lang('timezone') ?>
                </td>
                <td style="padding: 3px;">
                    <?= format_timezone($timezone) ?>
                </td>
            </tr>

            <?php if (!empty($appointment['status'])): ?>
                <tr>
                    <td class="label" style="padding: 3px;font-weight: bold;">
                        <?= lang('status') ?>
                    </td>
                    <td style="padding: 3px;">
                        <?= e($appointment['status']) ?>
                    </td>
                </tr>
            <?php endif; ?>

            <tr>
                <td class="label" style="padding: 3px;font-weight: bold;">
                    <?= lang('description') ?>
                </td>
                <td style="padding: 3px;">
                    <?= e($service['description']) ?>
                </td>
            </tr>

            <?php if (!empty($appointment['location'])): ?>
                <tr>
                    <td class="label" style="padding: 3px;font-weight: bold;">
                        <?= lang('location') ?>
                    </td>
                    <td style="padding: 3px;">
                        <?= e($appointment['location']) ?>
                    </td>
                </tr>
            <?php endif; ?>

            <?php if (!empty($appointment['notes'])): ?>
                <tr>
                    <td class="label" style="padding: 3px;font-weight: bold;">
                        <?= lang('notes') ?>
                    </td>
                    <td style="padding: 3px;">
                        <?= e($appointment['notes']) ?>
                    </td>
                </tr>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $max_appt_custom_fields; $i++): ?>
            <?php if ((intval(setting('display_appt_custom_field_' . $i)) == 1) && isset($appointment['appt_custom_field_' . $i])): ?>
            <?php $label_data = setting('label_appt_custom_field_' . $i, 'appt_custom_field') ?>
            <?php preg_match('/^(.+)(\s*{.+})*$/U', $label_data, $matches) ?>
            <?php $label_text = sizeof($matches) > 1 ? e(lang($matches[1])) : e(lang($label)) ?>
            <?php $value_text = empty($appointment['appt_custom_field_' . $i]) ? e(lang('no_field_value')) : e(lang($appointment['appt_custom_field_' . $i])) ?>
            <?php $value_text = e(implode('; ', array_map(fn($string): string => lang($string), explode(';', $value_text)))) ?>
            <tr>
                <td class="label" style="padding: 3px;font-weight: bold;">
                    <?= $label_text ?>
                </td>
                <td style="padding: 3px;">
                    <?= $value_text ?>
                </td>
            </tr>
            <?php endif; ?>
            <?php endfor; ?>

            <?php if ($max_attached_files > 0): ?>
                <tr>
                    <td class="label" style="padding: 3px;font-weight: bold;">
                        <?= lang('attached_files') ?>
                    </td>
                    <td style="padding: 3px;">
                        <?= empty($appointment['original_filenames']) ? '-' : e($appointment['original_filenames']) ?>
                    </td>
                </tr>
            <?php endif; ?>

        </table>

        <h2>
            <?= lang('customer_details_title') ?>
        </h2>

        <table id="customer-details">
            <tr>
                <td class="label" style="padding: 3px;font-weight: bold;">
                    <?= lang('name') ?>
                </td>
                <td style="padding: 3px;">
                    <?= e($customer['first_name'] . ' ' . $customer['last_name']) ?>
                </td>
            </tr>
            <tr>
                <td class="label" style="padding: 3px;font-weight: bold;">
                    <?= lang('email') ?>
                </td>
                <td style="padding: 3px;">
                    <?= e($customer['email']) ?>
                </td>
            </tr>
            <tr>
                <td class="label" style="padding: 3px;font-weight: bold;">
                    <?= lang('phone_number') ?>
                </td>
                <td style="padding: 3px;">
                    <?= e($customer['phone_number']) ?>
                </td>
            </tr>
            <tr>
                <td class="label" style="padding: 3px;font-weight: bold;">
                    <?= lang('address') ?>
                </td>
                <td style="padding: 3px;">
                    <?= e($customer['address']) ?>
                </td>
            </tr>

            <?php for ($i = 1; $i <= $max_custom_fields; $i++): ?>
            <?php if ((intval(setting('display_custom_field_' . $i)) == 1) && isset($customer['custom_field_' . $i])): ?>
            <?php $label_data = setting('label_custom_field_' . $i, 'custom_field') ?>
            <?php preg_match('/^(.+)(\s*{.+})*$/U', $label_data, $matches) ?>
            <?php $label_text = sizeof($matches) > 1 ? e(lang($matches[1])) : e(lang($label)) ?>
            <?php $value_text = empty($customer['custom_field_' . $i]) ? e(lang('no_field_value')) : lang($customer['custom_field_' . $i]) ?>
            <?php $value_text = e(implode('; ', array_map(fn($string): string => lang($string), explode(';', $value_text)))) ?>
            <tr>
                <td class="label" style="padding: 3px;font-weight: bold;">
                    <?= $label_text ?>
                </td>
                <td style="padding: 3px;">
                    <?= $value_text ?>
                </td>
            </tr>
            <?php endif; ?>
            <?php endfor; ?>

        </table>

        <h2>
            <?= lang('reason') ?>
        </h2>

        <p>
            <?= e($reason) ?>
        </p>
    </div>

    <div id="footer" style="padding: 10px; text-align: center; margin-top: 10px;
                border-top: 1px solid #EEE; background: #FAFAFA;">
        Powered by
        <a href="https://easyappointments.org" style="text-decoration: none;">
            Easy!Appointments
        </a>
        |
        <a href="<?= e($settings['company_link']) ?>" style="text-decoration: none;">
            <?= e($settings['company_name']) ?>
        </a>
    </div>
</div>

</body>
</html>
