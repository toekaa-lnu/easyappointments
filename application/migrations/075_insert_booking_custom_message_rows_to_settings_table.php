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

class Migration_Insert_booking_custom_message_rows_to_settings_table extends EA_Migration
{
    /**
     * Upgrade method.
     */
    public function up(): void
    {
        $field_name = 'booking_custom_messages_enabled';
        if (!$this->db->get_where('settings', ['name' => $field_name])->num_rows()) {
            $this->db->insert('settings', [
                'name' => $field_name,
                'value' => 0,
            ]);
        }
        $field_name = 'booking_custom_message_service_page';
        if (!$this->db->get_where('settings', ['name' => $field_name])->num_rows()) {
            $this->db->insert('settings', [
                'name' => $field_name,
                'value' => 'booking_custom_message_special_teacher',
            ]);
        }
        $field_name = 'booking_custom_message_time_unavailable';
        if (!$this->db->get_where('settings', ['name' => $field_name])->num_rows()) {
            $this->db->insert('settings', [
                'name' => $field_name,
                'value' => 'booking_custom_message_no_available_slots',
            ]);
        }
        $field_name = 'booking_custom_message_confirm_link';
        if (!$this->db->get_where('settings', ['name' => $field_name])->num_rows()) {
            $this->db->insert('settings', [
                'name' => $field_name,
                'value' => 'booking_custom_message_easyappointments_link',
            ]);
        }
        $field_name = 'booking_custom_message_confirm_link_text';
        if (!$this->db->get_where('settings', ['name' => $field_name])->num_rows()) {
            $this->db->insert('settings', [
                'name' => $field_name,
                'value' => 'booking_custom_message_easyappointments_link_text',
            ]);
        }
    }

    /**
     * Downgrade method.
     */
    public function down(): void
    {
        $field_name = 'booking_custom_messages_enabled';
        if ($this->db->get_where('settings', ['name' => $field_name])->num_rows()) {
            $this->db->delete('settings', ['name' => $field_name]);
        }
        $field_name = 'booking_custom_message_service_page';
        if ($this->db->get_where('settings', ['name' => $field_name])->num_rows()) {
            $this->db->delete('settings', ['name' => $field_name]);
        }
        $field_name = 'booking_custom_message_time_unavailable';
        if ($this->db->get_where('settings', ['name' => $field_name])->num_rows()) {
            $this->db->delete('settings', ['name' => $field_name]);
        }
        $field_name = 'booking_custom_message_confirm_link';
        if ($this->db->get_where('settings', ['name' => $field_name])->num_rows()) {
            $this->db->delete('settings', ['name' => $field_name]);
        }
        $field_name = 'booking_custom_message_confirm_link_text';
        if ($this->db->get_where('settings', ['name' => $field_name])->num_rows()) {
            $this->db->delete('settings', ['name' => $field_name]);
        }
    }
}
