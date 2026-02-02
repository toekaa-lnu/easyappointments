<?php defined('BASEPATH') or exit('No direct script access allowed');

/* ----------------------------------------------------------------------------
 * Easy!Appointments - Open Source Web Scheduler
 *
 * @package     EasyAppointments
 * @author      A.Tselegidis <alextselegidis@gmail.com>
 * @copyright   Copyright (c) 2013 - 2020, Alex Tselegidis
 * @license     http://opensource.org/licenses/GPL-3.0 - GPLv3
 * @link        http://easyappointments.org
 * @since       v1.4.0
 * ---------------------------------------------------------------------------- */

class Migration_Add_appt_custom_field_columns_to_appointments_table extends EA_Migration
{
    /**
     * @var int
     */

    /**
     * Upgrade method.
     */
    public function up(): void
    {
        $max_appt_custom_fields = config('max_appt_custom_fields', 5);
        for ($i = $max_appt_custom_fields; $i > 0; $i--) {
            $field_name = 'appt_custom_field_' . $i;
            if (!$this->db->field_exists($field_name, 'appointments')) {
                $fields = [
                    $field_name => [
                        'type' => 'TEXT',
                        'null' => true,
                        'after' => 'id_caldav_calendar',
                    ],
                ];
                $this->dbforge->add_column('appointments', $fields);
            }
        }
    }

    /**
     * Downgrade method.
     */
    public function down(): void
    {
        $max_appt_custom_fields = config('max_appt_custom_fields', 5);
        for ($i = $max_appt_custom_fields; $i > 0; $i--) {
            $field_name = 'appt_custom_field_' . $i;
            if ($this->db->field_exists($field_name, 'appointments')) {
                $this->dbforge->drop_column('appointments', $field_name);
            }
        }
    }
}
