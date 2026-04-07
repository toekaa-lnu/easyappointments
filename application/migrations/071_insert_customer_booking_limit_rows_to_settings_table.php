<?php defined('BASEPATH') or exit('No direct script access allowed');

/* ----------------------------------------------------------------------------
 * Easy!Appointments - Online Appointment Scheduler
 *
 * @package     EasyAppointments
 * @author      A.Tselegidis <alextselegidis@gmail.com>
 * @copyright   Copyright (c) Alex Tselegidis
 * @license     https://opensource.org/licenses/GPL-3.0 - GPLv3
 * @link        https://easyappointments.org
 * @since       v1.4.0
 * ---------------------------------------------------------------------------- */

class Migration_Insert_customer_booking_limit_rows_to_settings_table extends EA_Migration
{
    /**
     * Upgrade method.
     */
    public function up(): void
    {
        $field_name = 'max_customer_appointments';
        if (!$this->db->get_where('settings', ['name' => $field_name])->num_rows()) {
            $this->db->insert('settings', [
                'name' => $field_name,
                'value' => 0,
            ]);
        }
        $field_name = 'max_customer_appointments_period';
        if (!$this->db->get_where('settings', ['name' => $field_name])->num_rows()) {
            $this->db->insert('settings', [
                'name' => $field_name,
                'value' => 'day',
            ]);
        }
        $field_name = 'max_customer_service_bookings';
        if (!$this->db->get_where('settings', ['name' => $field_name])->num_rows()) {
            $this->db->insert('settings', [
                'name' => $field_name,
                'value' => 0,
            ]);
        }
    }

    /**
     * Downgrade method.
     */
    public function down(): void
    {
        $field_name = 'max_customer_appointments';
        if ($this->db->get_where('settings', ['name' => $field_name])->num_rows()) {
            $this->db->delete('settings', ['name' => $field_name]);
        }
        $field_name = 'max_customer_appointments_period';
        if ($this->db->get_where('settings', ['name' => $field_name])->num_rows()) {
            $this->db->delete('settings', ['name' => $field_name]);
        }
        $field_name = 'max_customer_service_bookings';
        if ($this->db->get_where('settings', ['name' => $field_name])->num_rows()) {
            $this->db->delete('settings', ['name' => $field_name]);
        }
    }
}
