<?php defined('BASEPATH') or exit('No direct script access allowed');

/* ----------------------------------------------------------------------------
 * Easy!Appointments - Online Appointment Scheduler
 *
 * @package     EasyAppointments
 * @author      A.Tselegidis <alextselegidis@gmail.com>
 * @copyright   Copyright (c) Alex Tselegidis
 * @license     https://opensource.org/licenses/GPL-3.0 - GPLv3
 * @link        https://easyappointments.org
 * @since       v1.0.0
 * ---------------------------------------------------------------------------- */

/**
 * Booking controller.
 *
 * Handles the booking related operations.
 *
 * Notice: This file used to have the booking page related code which since v1.5 has now moved to the Booking.php
 * controller for improved consistency.
 *
 * @package Controllers
 */
class Booking extends EA_Controller
{
    public array $allowed_customer_fields = [
        'id',
        'first_name',
        'last_name',
        'email',
        'phone_number',
        'address',
        'city',
        'state',
        'zip_code',
        'timezone',
        'language',
    ];
    public mixed $allowed_provider_fields = ['id', 'first_name', 'last_name', 'services', 'timezone'];
    public array $allowed_appointment_fields = [
        'id',
        'start_datetime',
        'end_datetime',
        'location',
        'notes',
        'color',
        'status',
        'is_unavailability',
        'id_users_provider',
        'id_users_customer',
        'id_services',
        'attached_files',
    ];

    /**
     * Booking constructor.
     */
    public function __construct()
    {
        parent::__construct();

        for ($i = 1; $i <= config('max_custom_fields', 5); $i++) {
            array_push($this->allowed_customer_fields, 'custom_field_' . $i);
        }

        for ($i = 1; $i <= config('max_appt_custom_fields', 5); $i++) {
            array_push($this->allowed_appointment_fields, 'appt_custom_field_' . $i);
        }


        $this->load->model('appointments_model');
        $this->load->model('providers_model');
        $this->load->model('admins_model');
        $this->load->model('secretaries_model');
        $this->load->model('service_categories_model');
        $this->load->model('services_model');
        $this->load->model('customers_model');
        $this->load->model('settings_model');
        $this->load->model('consents_model');

        $this->load->library('timezones');
        $this->load->library('synchronization');
        $this->load->library('notifications');
        $this->load->library('availability');
        $this->load->library('webhooks_client');
    }

    /**
     * Render the booking page and display the selected appointment.
     *
     * This method will call the "index" callback to handle the page rendering.
     *
     * @param string $appointment_hash
     */
    public function reschedule(string $appointment_hash): void
    {
        html_vars(['appointment_hash' => $appointment_hash]);

        $this->index();
    }

    /**
     * Render the booking page.
     *
     * This method creates the appointment book wizard.
     */
    public function index(): void
    {
        if (!is_app_installed()) {
            redirect('installation');

            return;
        }

        $company_name = setting('company_name');
        $company_logo = setting('company_logo');
        $company_color = setting('company_color');
        $disable_booking = setting('disable_booking');
        $google_analytics_code = setting('google_analytics_code');
        $matomo_analytics_url = setting('matomo_analytics_url');
        $matomo_analytics_site_id = setting('matomo_analytics_site_id');

        if ($disable_booking) {
            $disable_booking_message = setting('disable_booking_message');

            html_vars([
                'show_message' => true,
                'page_title' => lang('page_title') . ' ' . $company_name,
                'message_title' => lang('booking_is_disabled'),
                'message_text' => $disable_booking_message,
                'message_icon' => base_url('assets/img/error.png'),
                'google_analytics_code' => $google_analytics_code,
                'matomo_analytics_url' => $matomo_analytics_url,
                'matomo_analytics_site_id' => $matomo_analytics_site_id,
            ]);

            $this->load->view('pages/booking_message');

            return;
        }

        $available_services = $this->services_model->get_available_services(true);
        $available_providers = $this->providers_model->get_available_providers(true);

        foreach ($available_providers as &$available_provider) {
            // Only expose the required provider data.

            $this->providers_model->only($available_provider, $this->allowed_provider_fields);
        }

        $date_format = setting('date_format');
        $time_format = setting('time_format');
        $first_weekday = setting('first_weekday');
        $display_first_name = setting('display_first_name');
        $require_first_name = setting('require_first_name');
        $display_last_name = setting('display_last_name');
        $require_last_name = setting('require_last_name');
        $display_email = setting('display_email');
        $require_email = setting('require_email');
        $display_phone_number = setting('display_phone_number');
        $require_phone_number = setting('require_phone_number');
        $display_address = setting('display_address');
        $require_address = setting('require_address');
        $display_city = setting('display_city');
        $require_city = setting('require_city');
        $display_zip_code = setting('display_zip_code');
        $require_zip_code = setting('require_zip_code');
        $display_notes = setting('display_notes');
        $require_notes = setting('require_notes');
        $display_cookie_notice = setting('display_cookie_notice');
        $cookie_notice_content = setting('cookie_notice_content');
        $display_terms_and_conditions = setting('display_terms_and_conditions');
        $terms_and_conditions_content = setting('terms_and_conditions_content');
        $display_privacy_policy = setting('display_privacy_policy');
        $privacy_policy_content = setting('privacy_policy_content');
        $display_any_provider = setting('display_any_provider');
        $display_login_button = setting('display_login_button');
        $display_delete_personal_information = setting('display_delete_personal_information');
        $book_advance_timeout = setting('book_advance_timeout');
        $book_advance_timeout_unit = setting('book_advance_timeout_unit', 'minutes');
        $theme = request('theme', setting('theme', 'default'));

        if (empty($theme) || !file_exists(__DIR__ . '/../../assets/css/themes/' . $theme . '.min.css')) {
            $theme = 'default';
        }

        $timezones = $this->timezones->to_array();
        $grouped_timezones = $this->timezones->to_grouped_array();

        $appointment_hash = html_vars('appointment_hash');

        if (!empty($appointment_hash)) {
            // Load the appointments data and enable the manage mode of the booking page.

            $manage_mode = true;

            $results = $this->appointments_model->get(['hash' => $appointment_hash]);

            if (empty($results)) {
                html_vars([
                    'show_message' => true,
                    'page_title' => lang('page_title') . ' ' . $company_name,
                    'message_title' => lang('appointment_not_found'),
                    'message_text' => lang('appointment_does_not_exist_in_db'),
                    'message_icon' => base_url('assets/img/error.png'),
                    'google_analytics_code' => $google_analytics_code,
                    'matomo_analytics_url' => $matomo_analytics_url,
                    'matomo_analytics_site_id' => $matomo_analytics_site_id,
                ]);

                $this->load->view('pages/booking_message');

                return;
            }

            // Make sure the appointment can still be rescheduled.

            $start_datetime = strtotime($results[0]['start_datetime']);

            $limit = strtotime('+' . $book_advance_timeout . ' ' . $book_advance_timeout_unit, strtotime('now'));

            if ($start_datetime < $limit) {
                html_vars([
                    'show_message' => true,
                    'page_title' => lang('page_title') . ' ' . $company_name,
                    'message_title' => lang('appointment_locked'),
                    'message_text' => strtr(lang('appointment_locked_message'), [
                        '{$limit}' => sprintf('%d %s', $book_advance_timeout, $book_advance_timeout_unit),
                    ]),
                    'message_icon' => base_url('assets/img/error.png'),
                    'google_analytics_code' => $google_analytics_code,
                    'matomo_analytics_url' => $matomo_analytics_url,
                    'matomo_analytics_site_id' => $matomo_analytics_site_id,
                ]);

                $this->load->view('pages/booking_message');

                return;
            }

            $appointment = $results[0];
            $provider = $this->providers_model->find($appointment['id_users_provider']);
            $customer = $this->customers_model->find($appointment['id_users_customer']);
            $customer_token = md5(uniqid(mt_rand(), true));

            // Cache the token for 10 minutes.
            $this->cache->save('customer-token-' . $customer_token, $customer['id'], 600);
        } else {
            $manage_mode = false;
            $customer_token = false;
            $appointment = null;
            $provider = null;
            $customer = null;
        }

        script_vars([
            'manage_mode' => $manage_mode,
            'available_services' => $available_services,
            'available_providers' => $available_providers,
            'date_format' => $date_format,
            'time_format' => $time_format,
            'first_weekday' => $first_weekday,
            'display_cookie_notice' => $display_cookie_notice,
            'display_any_provider' => setting('display_any_provider'),
            'future_booking_limit' => setting('future_booking_limit'),
            'appointment_data' => $appointment,
            'provider_data' => $provider,
            'customer_data' => $customer,
            'customer_token' => $customer_token,
            'default_language' => setting('default_language'),
            'default_timezone' => setting('default_timezone'),
            'hide_customer_timezone' => setting('hide_customer_timezone', 0),
            'hide_provider_selection' => setting('hide_provider_selection'),
            'ANY_PROVIDER' => ANY_PROVIDER,
        ]);

        html_vars([
            'available_services' => $available_services,
            'available_providers' => $available_providers,
            'theme' => $theme,
            'company_name' => $company_name,
            'company_logo' => $company_logo,
            'company_color' => $company_color === '#ffffff' ? '' : $company_color,
            'date_format' => $date_format,
            'time_format' => $time_format,
            'first_weekday' => $first_weekday,
            'display_first_name' => $display_first_name,
            'require_first_name' => $require_first_name,
            'display_last_name' => $display_last_name,
            'require_last_name' => $require_last_name,
            'display_email' => $display_email,
            'require_email' => $require_email,
            'display_phone_number' => $display_phone_number,
            'require_phone_number' => $require_phone_number,
            'display_address' => $display_address,
            'require_address' => $require_address,
            'display_city' => $display_city,
            'require_city' => $require_city,
            'display_zip_code' => $display_zip_code,
            'require_zip_code' => $require_zip_code,
            'display_notes' => $display_notes,
            'require_notes' => $require_notes,
            'display_cookie_notice' => $display_cookie_notice,
            'cookie_notice_content' => $cookie_notice_content,
            'display_terms_and_conditions' => $display_terms_and_conditions,
            'terms_and_conditions_content' => $terms_and_conditions_content,
            'display_privacy_policy' => $display_privacy_policy,
            'privacy_policy_content' => $privacy_policy_content,
            'display_any_provider' => $display_any_provider,
            'display_login_button' => $display_login_button,
            'display_delete_personal_information' => $display_delete_personal_information,
            'google_analytics_code' => $google_analytics_code,
            'matomo_analytics_url' => $matomo_analytics_url,
            'matomo_analytics_site_id' => $matomo_analytics_site_id,
            'timezones' => $timezones,
            'grouped_timezones' => $grouped_timezones,
            'manage_mode' => $manage_mode,
            'appointment_data' => $appointment,
            'provider_data' => $provider,
            'customer_data' => $customer,
        ]);

        $this->load->view('pages/booking');
    }

    /**
     * Register the appointment to the database.
     */
    public function register(): void
    {
        try {
            $disable_booking = setting('disable_booking');

            if ($disable_booking) {
                abort(403);
            }

            if (isset($_POST['post_data'])) {
                $post_data = json_decode($_POST['post_data'], true);
                $manage_mode = filter_var($post_data['manage_mode'], FILTER_VALIDATE_BOOLEAN);
                $appointment = $post_data['appointment'];
                $customer = $post_data['customer'];
                $discarded_file_names = isset($post_data['discarded_file_names']) ? $post_data['discarded_file_names'] : '';
            }

            if (isset($_POST['captcha'])) {
                $captcha =$_POST['captcha'];
            }

            if (!array_key_exists('address', $customer)) {
                $customer['address'] = '';
            }

            if (!array_key_exists('city', $customer)) {
                $customer['city'] = '';
            }

            if (!array_key_exists('zip_code', $customer)) {
                $customer['zip_code'] = '';
            }

            if (!array_key_exists('notes', $customer)) {
                $customer['notes'] = '';
            }

            if (!array_key_exists('phone_number', $customer)) {
                $customer['phone_number'] = '';
            }

            // Check appointment availability before registering it to the database.
            $appointment['id_users_provider'] = $this->check_datetime_availability();

            if (!$appointment['id_users_provider']) {
                throw new RuntimeException(lang('requested_hour_is_unavailable'));
            }

            $provider = $this->providers_model->find($appointment['id_users_provider']);

            $service = $this->services_model->find($appointment['id_services']);

            $require_captcha = (bool) setting('require_captcha');

            $captcha_phrase = session('captcha_phrase');

            // Validate the CAPTCHA string.

            if ($require_captcha && strtoupper($captcha_phrase) !== strtoupper($captcha)) {
                json_response([
                    'captcha_verification' => false,
                ]);

                return;
            }

            if ($this->customers_model->exists($customer)) {
                $customer['id'] = $this->customers_model->find_record_id($customer);

                $existing_appointments = $this->appointments_model->get([
                    'id !=' => $manage_mode ? $appointment['id'] : null,
                    'id_users_customer' => $customer['id'],
                    'start_datetime <=' => $appointment['start_datetime'],
                    'end_datetime >=' => $appointment['end_datetime'],
                ]);

                if (count($existing_appointments)) {
                    throw new RuntimeException(lang('customer_is_already_booked'));
                }
            }

            if (empty($appointment['location']) && !empty($service['location'])) {
                $appointment['location'] = $service['location'];
            }

            if (empty($appointment['color']) && !empty($service['color'])) {
                $appointment['color'] = $service['color'];
            }

            $customer_ip = $this->input->ip_address();

            // Create the consents (if needed).
            $consent = [
                'first_name' => $customer['first_name'] ?? '-',
                'last_name' => $customer['last_name'] ?? '-',
                'email' => $customer['email'] ?? '-',
                'ip' => $customer_ip,
            ];

            if (setting('display_terms_and_conditions')) {
                $consent['type'] = 'terms-and-conditions';

                $this->consents_model->save($consent);
            }

            if (setting('display_privacy_policy')) {
                $consent['type'] = 'privacy-policy';

                $this->consents_model->save($consent);
            }

            // Save customer language (the language which is used to render the booking page).
            $customer['language'] = session('language') ?? config('language');

            $this->customers_model->only($customer, $this->allowed_customer_fields);

            $customer_id = $this->customers_model->save($customer);
            $customer = $this->customers_model->find($customer_id);

            $appointment['id_users_customer'] = $customer_id;
            $appointment['is_unavailability'] = false;
            $appointment['color'] = $service['color'];

            $appointment_status_options_json = setting('appointment_status_options', '[]');
            $appointment_status_options = json_decode($appointment_status_options_json, true) ?? [];
            $appointment['status'] = $appointment_status_options[0] ?? null;
            $appointment['end_datetime'] = $this->appointments_model->calculate_end_datetime($appointment);

            // Update the filenames attribute in appointment, upload the files to storage/uploads/
            $attached_files = [];
            if ($manage_mode) {
                $existing_appointment = $this->appointments_model->get(['id' => $appointment['id']])[0];
                $existing_file_names = $existing_appointment['attached_files'];
                $existing_files = strlen($existing_file_names) == 0 ? [] :explode(';', $existing_file_names);
                $discarded_files = strlen($discarded_file_names) == 0 ? [] : explode(';', $discarded_file_names);
                foreach($discarded_files as $discarded_file) {
                    if (strlen($discarded_file) > 0) {
                        $index = array_search($discarded_file, $existing_files);
                        if ($index !== false) {
                            unset($existing_files[$index]);
                        }
                        $discarded_file_path = sprintf('storage/uploads/%s', $discarded_file);
                        if (file_exists($discarded_file_path)) {
                            $delete_result = unlink($discarded_file_path);
                        }
                    }
                }
                $attached_files = $existing_files;
            }
            $max_attached_files = setting('max_attached_files');
            for ($i = 1; $i <= $max_attached_files; $i++) {
                $file_name =  $this->upload_attached_file('attached_file_data_' . $i);
                if ($file_name) {
                    array_push($attached_files, $file_name);
                }
            }
            $appointment['attached_files'] = implode(';', $attached_files);

            $this->appointments_model->only($appointment, $this->allowed_appointment_fields);

            $appointment_id = $this->appointments_model->save($appointment);
            $appointment = $this->appointments_model->find($appointment_id);

            $company_color = setting('company_color');

            $settings = [
                'company_name' => setting('company_name'),
                'company_link' => setting('company_link'),
                'company_email' => setting('company_email'),
                'company_color' =>
                    !empty($company_color) && $company_color != DEFAULT_COMPANY_COLOR ? $company_color : null,
                'date_format' => setting('date_format'),
                'time_format' => setting('time_format'),
            ];

            $this->synchronization->sync_appointment_saved($appointment, $service, $provider, $customer, $settings);

            $this->notifications->notify_appointment_saved(
                $appointment,
                $service,
                $provider,
                $customer,
                $settings,
                $manage_mode,
            );

            $this->webhooks_client->trigger(WEBHOOK_APPOINTMENT_SAVE, $appointment);

            $response = [
                'appointment_id' => $appointment['id'],
                'appointment_hash' => $appointment['hash'],
            ];

            json_response($response);
        } catch (Throwable $e) {
            json_exception($e);
        }
    }

    /**
     * Check whether the provider is still available in the selected appointment date.
     *
     * It is possible that two or more customers select the same appointment date and time concurrently. The app won't
     * allow this to happen, so one of the two will eventually get the selected date and the other one will have
     * to choose for another one.
     *
     * Use this method just before the customer confirms the appointment registration. If the selected date was reserved
     * in the meanwhile, the customer must be prompted to select another time.
     *
     * @return int|null Returns the ID of the provider that is available for the appointment.
     *
     * @throws Exception
     */
    protected function check_datetime_availability(): ?int
    {
        $post_data = json_decode($_POST['post_data'], true);

        $appointment = $post_data['appointment'];

        $appointment_start = new DateTime($appointment['start_datetime']);

        $date = $appointment_start->format('Y-m-d');

        $hour = $appointment_start->format('H:i');

        $service = $this->services_model->find($appointment['id_services']);
        $service_id = $service['id'];

        if ($appointment['id_users_provider'] === ANY_PROVIDER) {
            $provider_id = null;
            switch (setting('provider_selection_method')) {
                case 'around_date': 
                    $provider_id = $this->search_furthest_booking_provider_around_booking_date($service_id, $appointment_start);
                    break;
                default:
                    $provider_id = $this->search_any_provider($service_id, $date, $hour);
                    break;
            }
            $appointment['id_users_provider'] = $provider_id;

            return $appointment['id_users_provider'];
        }
        
        $exclude_appointment_id = $appointment['id'] ?? null;

        $provider = $this->providers_model->find($appointment['id_users_provider']);

        $available_hours = $this->availability->get_available_hours(
            $date,
            $service,
            $provider,
            $exclude_appointment_id,
        );

        $is_still_available = false;

        $appointment_hour = date('H:i', strtotime($appointment['start_datetime']));

        foreach ($available_hours as $available_hour) {
            if ($appointment_hour === $available_hour) {
                $is_still_available = true;
                break;
            }
        }

        return $is_still_available ? $appointment['id_users_provider'] : null;
    }

    /**
     * Search for available service providers on the given date and time.
     *
     * This method will return an array of available providers.
     *
     * @param int $service_id Service ID
     * @param string $date Selected date (Y-m-d).
     * @param string|null $hour Selected hour (H:i).
     *
     * @return array Returns an array of available providers, or empty array.
     *
     * @throws Exception
     */
    protected function get_providers_available_for_service_on_datetime(int $service_id, string $date, string $hour): array
    {
        $available_providers = [];
        $all_providers = $this->providers_model->get_available_providers(true);
        $service = $this->services_model->find($service_id);

        foreach ($all_providers as $provider) {
            $provider_id = $provider['id'];
            foreach ($provider['services'] as $provider_service_id) {
                if ($provider_service_id == $service_id) {
                    // Check if the provider is available for the requested date.
                    $available_hours = $this->availability->get_available_hours($date, $service, $provider);
                    if (empty($hour) || in_array($hour, $available_hours)) {
                        $available_providers[] = $provider['id'];
                    }
                }
            }
        }
        return $available_providers;
    }

    /**
     * Search for a provider whose existing bookings are furthest from the new booking
     * 
     * Steps:
     * 1. Get a list of providers available for the new booking
     * 2. For each of these providers, find their existing booking which is closest in time to the new booking
     * 3. Among these closest bookings, find the one which is furthest in time from the new booking
     * 4. Select the provider having this furthest booking
     *
     * This method will return a list of providers available.
     *
     * @param int $service_id Service ID
     * @param string $date Selected date (Y-m-d).
     * @param string|null $hour Selected hour (H:i).
     *
     * @return array Returns an array of available providers, or empty array.
     *
     * @throws Exception
     */
    protected function search_furthest_booking_provider_around_booking_date(int $service_id, DateTime $appointment_start): ?int
    {
        log_message('debug', 'search_provider_available_around_date()');
        $available_providers = $this->get_providers_available_for_service_on_datetime($service_id, $appointment_start->format('Y-m-d'), $appointment_start->format('H:i'));
        $provider_id = $this->providers_model->get_service_provider_around_date($service_id, $appointment_start, $available_providers);
        log_message('debug', '$provider_id:' . $provider_id);
        return $provider_id;
    }

    /**
     * Search for any provider that can handle the requested service.
     * NB: Only considers availability on the date of the new booking
     *
     * This method will return the database ID of the provider with the most available periods.
     *
     * @param int $service_id Service ID
     * @param string $date Selected date (Y-m-d).
     * @param string|null $hour Selected hour (H:i).
     *
     * @return int|null Returns the ID of the provider that can provide the service at the selected date.
     *
     * @throws Exception
     */
    protected function search_any_provider(int $service_id, string $date, string $hour): ?int
    {
        log_message('debug', 'search_least_bookings_provider_on_booking_date()');
        $available_providers = $this->providers_model->get_available_providers(true);
        $service = $this->services_model->find($service_id);
        $provider_id = null;
        $max_hours_count = 0;

        foreach ($available_providers as $provider) {
            foreach ($provider['services'] as $provider_service_id) {
                if ($provider_service_id == $service_id) {
                    // Check if the provider is available for the requested date.
                    $available_hours = $this->availability->get_available_hours($date, $service, $provider);
                    if (
                        count($available_hours) > $max_hours_count &&
                        (empty($hour) || in_array($hour, $available_hours))
                    ) {
                        $provider_id = $provider['id'];
                        $max_hours_count = count($available_hours);
                    }
                }
            }
        }
        log_message('debug', '$provider_id:' . $provider_id);
        return $provider_id;
    }

    /**
     * Get the available appointment hours for the selected date.
     *
     * This method answers to an AJAX request. It calculates the available hours for the given service, provider and
     * date.
     */
    public function get_available_hours(): void
    {
        try {
            $disable_booking = setting('disable_booking');

            if ($disable_booking) {
                abort(403);
            }

            $provider_id = request('provider_id');
            $service_id = request('service_id');
            $selected_date = request('selected_date');

            // Do not continue if there was no provider selected (more likely there is no provider in the system).

            if (empty($provider_id)) {
                json_response();

                return;
            }

            // If manage mode is TRUE then the following we should not consider the selected appointment when
            // calculating the available time periods of the provider.

            $exclude_appointment_id = request('manage_mode') ? request('appointment_id') : null;

            // If the user has selected the "any-provider" option then we will need to search for an available provider
            // that will provide the requested service.

            $service = $this->services_model->find($service_id);

            if ($provider_id === ANY_PROVIDER) {
                $providers = $this->providers_model->get_available_providers(true);

                $available_hours = [];

                foreach ($providers as $provider) {
                    if (!in_array($service_id, $provider['services'])) {
                        continue;
                    }

                    $provider_available_hours = $this->availability->get_available_hours(
                        $selected_date,
                        $service,
                        $provider,
                        $exclude_appointment_id,
                    );

                    $available_hours = array_merge($available_hours, $provider_available_hours);
                }

                $available_hours = array_unique(array_values($available_hours));

                sort($available_hours);

                $response = $available_hours;
            } else {
                $provider = $this->providers_model->find($provider_id);

                $response = $this->availability->get_available_hours(
                    $selected_date,
                    $service,
                    $provider,
                    $exclude_appointment_id,
                );
            }

            json_response($response);
        } catch (Throwable $e) {
            json_exception($e);
        }
    }

    /**
     * Get Unavailable Dates
     *
     * Get an array with the available dates of a specific provider, service and month of the year. Provide the
     * "provider_id", "service_id" and "selected_date" as GET parameters to the request. The "selected_date" parameter
     * must have the "Y-m-d" format.
     *
     * Outputs a JSON string with the unavailability dates. that are unavailability.
     */
    public function get_unavailable_dates(): void
    {
        try {
            $disable_booking = setting('disable_booking');

            if ($disable_booking) {
                abort(403);
            }

            $provider_id = request('provider_id');
            $service_id = request('service_id');
            $appointment_id = request('appointment_id');
            $manage_mode = filter_var(request('manage_mode'), FILTER_VALIDATE_BOOLEAN);
            $selected_date_string = request('selected_date');
            $selected_date = new DateTime($selected_date_string);
            $number_of_days_in_month = (int) $selected_date->format('t');
            $unavailable_dates = [];

            $provider_ids =
                $provider_id === ANY_PROVIDER ? $this->search_providers_by_service($service_id) : [$provider_id];

            $exclude_appointment_id = $manage_mode ? $appointment_id : null;

            // Get the service record.
            $service = $this->services_model->find($service_id);

            for ($i = 1; $i <= $number_of_days_in_month; $i++) {
                $current_date = new DateTime($selected_date->format('Y-m') . '-' . $i);

                if ($current_date < new DateTime(date('Y-m-d 00:00:00'))) {
                    // Past dates become immediately unavailability.
                    $unavailable_dates[] = $current_date->format('Y-m-d');
                    continue;
                }

                // Finding at least one slot of availability.
                foreach ($provider_ids as $current_provider_id) {
                    $provider = $this->providers_model->find($current_provider_id);

                    $available_hours = $this->availability->get_available_hours(
                        $current_date->format('Y-m-d'),
                        $service,
                        $provider,
                        $exclude_appointment_id,
                    );

                    if (!empty($available_hours)) {
                        break;
                    }
                }

                // No availability amongst all the provider.
                if (empty($available_hours)) {
                    $unavailable_dates[] = $current_date->format('Y-m-d');
                }
            }

            if (count($unavailable_dates) === $number_of_days_in_month) {
                json_response([
                    'is_month_unavailable' => true,
                ]);

                return;
            }

            json_response($unavailable_dates);
        } catch (Throwable $e) {
            json_exception($e);
        }
    }

    /**
     * Search for any provider that can handle the requested service.
     *
     * This method will return the database ID of the providers affected to the requested service.
     *
     * @param int $service_id The requested service ID.
     *
     * @return array Returns the ID of the provider that can provide the requested service.
     */
    protected function search_providers_by_service(int $service_id): array
    {
        $available_providers = $this->providers_model->get_available_providers(true);
        $provider_list = [];

        foreach ($available_providers as $provider) {
            foreach ($provider['services'] as $provider_service_id) {
                if ($provider_service_id === $service_id) {
                    // Check if the provider is affected to the selected service.
                    $provider_list[] = $provider['id'];
                }
            }
        }

        return $provider_list;
    }

    /*
     * Upload attached file
     *
     * This method will upload the attached file given a file ID.
     * File will be stored in storage/uploads/ folder
     * 
     * @param int $file_id The attached file id.
     *
     * @return string Returns a unique filename for the attached file.
     */
    protected function upload_attached_file($file_id)
    {
        $file_origname = null;
        $file_finalname = null;
        if (isset($_FILES[$file_id])) {
            if (!isset($_FILES[$file_id]['error']) || is_array($_FILES[$file_id]['error'])) {
                throw new RuntimeException(lang('invalid_parameters'));
            }
            switch ($_FILES[$file_id]['error']) {
                case UPLOAD_ERR_OK:
                    break;
                case UPLOAD_ERR_NO_FILE:
                    throw new RuntimeException(lang('no_file_found'));
                    break;
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    throw new RuntimeException(lang('system_file_size_exceeded'));
                    break;
                default:
                    throw new RuntimeException(lang('unknown_error'));
            }

            $file_origname = $_FILES[$file_id]['name'];
            $file_tmpname = $_FILES[$file_id]['tmp_name'];
            $file_size = $_FILES[$file_id]['size'];
            $file_giventype = $_FILES[$file_id]['type'];

            $file_rand = strval(rand(10000, 99999));

            // Check allowed file types from settings
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $file_mimetype = $finfo->file($file_tmpname);
            $allowed_filetypes = explode(",", setting('attached_files_allowed_types'));
            $mimetypes = get_mimes();
            $allowed_mimetypes = [];

            foreach ($allowed_filetypes as $filetype) {
                $trimmed_filetype = trim($filetype,". ");
                if (array_key_exists($trimmed_filetype, $mimetypes)) {
                    // Allowed type is extension --> add all related mime types
                    if (is_array($mimetypes[$trimmed_filetype])) {
                        $allowed_mimetypes = array_merge($allowed_mimetypes, $mimetypes[$trimmed_filetype]);
                    } else {
                        $allowed_mimetypes[] = $mimetypes[$trimmed_filetype];
                    }
                } else if (array_search($trimmed_filetype, $mimetypes)) {
                    // Allowed type is mime type --> add it as it is
                    $allowed_mimetypes[] = $mimetypes[$trimmed_filetype];
                }
            }

            $file_extn = array_search($file_mimetype, $allowed_mimetypes, true);
            if (false === $file_extn) {
                $error_message = sprintf(lang('attached_files_invalid_format'), lang('attached_files_user_allowed_types_hint'));
                throw new RuntimeException($error_message);
            }
        
            $max_size = setting('attached_files_max_size');
            if ($file_size > $max_size) {
                if ((int)($max_size / 10000000000) > 0) {
                    $max_size_text = sprintf(lang('gigabytes'), (int)($max_size / 10000000000));
                } else if ((int)($max_size / 10000000) > 0) {
                    $max_size_text = sprintf(lang('megabytes'), (int)($max_size / 10000000));
                } else if ((int)($max_size / 1000) > 0) {
                    $max_size_text = sprintf(lang('kilobytes'), (int)($max_size / 1000));
                } else {
                    $max_size_text = sprintf(lang('bytes'), (int)$max_size);
                }
                $error_message = sprintf(lang('file_max_size_exceeded'), $max_size_text);
                throw new RuntimeException($error_message);
            }

            $file_finalname = sprintf('%s-%s', $file_rand, $file_origname);
            $file_finalpath = sprintf('storage/uploads/%s', $file_finalname);
            if (!move_uploaded_file($file_tmpname, $file_finalpath)) {
                throw new RuntimeException(lang('failed_to_move_file'));
            }
        }
        return $file_finalname;
    }

    /**
     * Check customer restrictions to make the booking before the registration.
     */
    public function check_customer_booking_limits(): void
    {
        try {
            $customer_email = $this->input->get('customer_email');
            $service_id = $this->input->get('service_id');
            $booking_date = $this->input->get('booking_date');
            $hash_booked_service = $this->input->get('appointment_hash');

            // Assume that customer has no appointments, until proven otherwise
            $existing_appointments = [];
            $next_service_booking_date = null;
            $num_service_bookings = 0;

            $target_date = (new DateTime($booking_date))->format('Y-m-d');
            $customer = [];
            $customer['email'] = $customer_email;
            $customer_exists = $this->customers_model->exists($customer);

            if ($customer_exists) {
                $customer_id = $this->customers_model->find_record_id($customer);
                $existing_appointments = $this->appointments_model->get_customer_appointments($customer_id);

                $booking_datetime = new DateTimeImmutable($booking_date);
                $booking_year = $booking_datetime->format('Y');
                $booking_month = $booking_datetime->format('m');
                $booking_day = $booking_datetime->format('d');
                $booking_weekday_id = $booking_datetime->format('N');
                $start_time = ' 00:00:00';
                $end_time = ' 23:59:59';

                // Limit the existing appointments to the selected time period
                $limit_period = setting('max_customer_appointments_period');
                switch ($limit_period) {
                    default:
                    case 'day':
                        $start_period = $booking_date . $start_time; // start of today
                        $end_period = $booking_date . $end_time; // end of today
                        break;
                    case 'week':
                        $day_names = array('sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday');
                        $first_weekday_id = array_search(setting('first_weekday'), $day_names);
                        $weekday_diff = (($first_weekday_id - $booking_weekday_id + 6) % 7) - 6;
                        $week_start = $booking_datetime->modify("{$weekday_diff} days");
                        $week_end = $week_start->modify("+6 days");
                        $start_period = $week_start->format('Y-m-d') . $start_time;
                        $end_period = $week_end->format('Y-m-d') . $end_time;
                        break;
                    case 'month':
                        $days_in_month = cal_days_in_month(CAL_GREGORIAN, $booking_month, $booking_year);
                        $start_period = "{$booking_year}-{$booking_month}-1" . $start_time;
                        $end_period = "{$booking_year}-{$booking_month}-{$days_in_month}" . $end_time;
                        break;
                    case 'half-year':
                        $start_month = $booking_month <= 6 ? '01' : '07';
                        $end_month = ($start_month == 1) ? '06' : '12';
                        $start_period = "{$booking_year}-{$start_month}-1" . $start_time;
                        $end_period = "{$booking_year}-{$end_month}-31" . $end_time;
                        break;
                    case 'calendar_year':
                        $start_period = "{$booking_year}-01-01" . $start_time;
                        $end_period = "{$booking_year}-12-31" . $end_time;
                        break;
                    case 'school_year':
                        $start_year = $booking_month <= 6 ? $booking_year - 1 : $booking_year;
                        $end_year = $start_year + 1;
                        $start_period = "{$start_year}-07-01" . $start_time;
                        $end_period = "{$end_year}-06-30" . $end_time;
                        break;
                }

                $period_appointments = [];
                foreach ($existing_appointments as $appointment) {
                    if ($appointment['start_datetime'] > $start_period && $appointment['end_datetime'] < $end_period) {
                        $period_appointments[] = $appointment;
                    }
                }

                // Find a datetime for another booked appointment for the same service
                $now = (new DateTime())->format('Y-m-d H:i:s');
                foreach ($existing_appointments as $appointment) {
                    if ($appointment['id_services'] == $service_id && $appointment['start_datetime'] > $now) {
                        $num_service_bookings++;
                        if ($appointment['id_services'] != $hash_booked_service) {
                            $next_service_booking_date = $appointment['start_datetime'];
                        }
                    }
                }
            }

            $num_appointments = count($period_appointments);
            $max_appointments = setting('max_customer_appointments');
            $nth_appointment = $hash_booked_service ? $num_appointments : $num_appointments+1;

            $max_service_bookings = setting('max_customer_service_bookings');
            $nth_service_booking = $hash_booked_service ? $num_service_bookings : $num_service_bookings+1;

            $is_testing_email = in_array($customer_email, explode(';', config('test_email_addresses', '')));

            // Compose the validation message for the bookings confirmation UI
            $message = '';
            $cardinals = array('0', lang('one'),lang('two'), lang('three'), lang('four'), lang('five'), '6', '7', '8', '9', '10'); 
            $ordinals = array('0', lang('first'), lang('second'), lang('third'), lang('fourth'), lang('fifth'), '6.', '7.', '8.', '9.', '10.'); 

            $max_appointments_string = $max_appointments >= count($cardinals) ? strval($max_appointments) : ($max_appointments > 0 ? $cardinals[$max_appointments] : '0');
            $num_appointments_string = $nth_appointment >= count($ordinals) ? strval($nth_appointment) . '.' : $ordinals[$nth_appointment];
            $appointments_string = $max_appointments == 1 ? lang('appointment') : lang('appointments');
            $period_string = lang('each_' . $limit_period);
            $andLastString = ($max_appointments == $nth_appointment) ? lang('and_last') : '';
            $appointmentsPolicyString = sprintf(lang('allowed_bookings_policy'), $max_appointments_string, $appointments_string, $period_string, $num_appointments_string, $andLastString);

            $max_appointments_exceeded = ($max_appointments > 0) && ($nth_appointment > $max_appointments);
            $max_service_bookings_exceeded = ($max_service_bookings > 0) && ($nth_service_booking > $max_service_bookings);

            if ($max_appointments_exceeded) {
                // Number of allowed appointments exceeded
                $message =  sprintf(lang('disallowed_booking'), $appointmentsPolicyString);
            } else if ($max_service_bookings_exceeded) {
                // Number of upcoming bookings for a service exceeded
                $max_service_bookings_string = $max_service_bookings >= count($cardinals) ? strval($max_service_bookings) : ($max_service_bookings > 0 ? $cardinals[$max_service_bookings] : '0');
                $active_bookings_string = $max_service_bookings == 1 ? lang('active_booking') : lang('active_bookings');
                $num_service_bookings_string = $nth_service_booking >= count($ordinals) ? strval($nth_service_booking) . '.' : $ordinals[$nth_service_booking];
                $andLastString = ($max_service_bookings == $nth_service_booking) ? lang('and_last') : '';
                $date = date_create($next_service_booking_date);
                $activeBookingsPolicyString = sprintf(lang('active_bookings_policy'), $max_service_bookings_string, $active_bookings_string, $num_service_bookings_string, $andLastString, $date->format('Y/m/d'), $date->format('H:i'));
                $message = sprintf(lang('disallowed_booking'), $activeBookingsPolicyString);
            } else {
                // Booking allowed, just show appointments status (or nothing, if unlimited appointments)
                $message = $max_appointments > 0 ? $appointmentsPolicyString : '';
            }

            $response = [
                'allowed' => $is_testing_email || (!$max_appointments_exceeded && !$max_service_bookings_exceeded),
                'message' => $message,
            ];
            json_response($response);

        } catch (Throwable $e) {
            json_exception($e);
        }
    }
}
