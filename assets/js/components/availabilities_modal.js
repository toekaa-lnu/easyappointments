/* ----------------------------------------------------------------------------
 * Easy!Appointments - Online Appointment Scheduler
 *
 * @package     EasyAppointments
 * @author      A.Tselegidis <alextselegidis@gmail.com>
 * @copyright   Copyright (c) Alex Tselegidis
 * @license     https://opensource.org/licenses/GPL-3.0 - GPLv3
 * @link        https://easyappointments.org
 * @since       v1.5.0
 * ---------------------------------------------------------------------------- */

/**
 * Availabilities modal component.
 *
 * This module implements the availabilities modal functionality.
 *
 * Old Name: -
 */
App.Components.AvailabilitiesModal = (function () {
    const $availabilitiesModal = $('#availabilities-modal');
    const $startDatetime = $('#availability-start');
    const $endDatetime = $('#availability-end');
    const $selectProvider = $('#availability-provider');
    const $saveAvailability = $('#save-availability');
    const $insertAvailability = $('#insert-availability');
    const $reloadAppointments = $('#reload-appointments');
    const $selectFilterItem = $('#select-filter-item');

    const moment = window.moment;

    /**
     * Update the displayed timezone.
     */
    function updateTimezone() {
        const providerId = $selectProvider.val();

        const provider = vars('available_providers').find(
            (availableProvider) => Number(availableProvider.id) === Number(providerId),
        );

        console.log("update provider timezone")

        if (Boolean(Number(vars('fixed_timezone')))) {
            $availabilitiesModal.find('.provider-timezone').text(vars('default_timezone'));
        } else if (provider && provider.timezone) {
            $availabilitiesModal.find('.provider-timezone').text(vars('timezones')[provider.timezone]);
        }
    }

    /**
     * Add the component event listeners.
     */
    function addEventListeners() {
        /**
         * Event: Provider "Change"
         */
        $selectProvider.on('change', () => {
            updateTimezone();
        });

        /**
         * Event: Manage Availability Dialog Save Button "Click"
         *
         * Stores the unavailability period changes or inserts a new record.
         */
        $saveAvailability.on('click', async () => {
            console.log("availabilities_modal::saveAvailability.click()")
            $availabilitiesModal.find('.modal-message').addClass('d-none');
            $availabilitiesModal.find('.is-invalid').removeClass('is-invalid');

            if (!$selectProvider.val()) {
                $selectProvider.addClass('is-invalid');
                return;
            }

            const startDateTimeMoment = moment(App.Utils.UI.getDateTimePickerValue($startDatetime));

            if (!startDateTimeMoment.isValid()) {
                $startDatetime.addClass('is-invalid');
                return;
            }

            const endDateTimeMoment = moment(App.Utils.UI.getDateTimePickerValue($endDatetime));

            if (!endDateTimeMoment.isValid()) {
                $endDatetime.addClass('is-invalid');

                return;
            }

            if (startDateTimeMoment.isAfter(endDateTimeMoment)) {
                // Start time is after end time - display message to user.
                $availabilitiesModal
                    .find('.modal-message')
                    .text(lang('start_date_before_end_error'))
                    .addClass('alert-danger')
                    .removeClass('d-none');

                $startDatetime.addClass('is-invalid');

                $endDatetime.addClass('is-invalid');

                return;
            }

            // Create/update working plan exception
            const date = startDateTimeMoment.format('YYYY-MM-DD');
            const startTime = startDateTimeMoment.format('HH:mm');
            const endTime = endDateTimeMoment.format('HH:mm');
            const providerId = $selectProvider.val();
            console.log(date);
            console.log(startTime);
            console.log(endTime);
            console.log(providerId);
            const provider = vars('available_providers').find((availableProvider) => Number(availableProvider.id) == providerId);
            const workingPlanExceptions = JSON.parse(provider ? provider.settings.working_plan_exceptions : '{}');
            console.log('workingPlanExceptions: '); console.log(workingPlanExceptions);
            let workingPlanException = workingPlanExceptions[date];
            if (workingPlanException) {
                console.log("current working plan exception");
                console.log(workingPlanException);
            } else {
                weekdayNumber = parseInt(startDateTimeMoment.format('d'));
                weekdayName = App.Utils.Date.getWeekdayName(weekdayNumber);
                const workingPlan = JSON.parse(provider ? provider.settings.working_plan : vars('company_working_plan'));
                console.log('workingPlan: '); console.log(workingPlan);
                const dayWorkingPlan = workingPlan[weekdayName];
                if (dayWorkingPlan) {
                    workingPlanException = {
                        "date": date,
                        "start": dayWorkingPlan.start,
                        "end": dayWorkingPlan.end,
                        "breaks": dayWorkingPlan.breaks,
                        "provider_id": providerId,
                        "original_date": date
                    }
                    console.log("working plan exception from working plan");
                    console.log(workingPlanException);
                } else {
                    workingPlanException = {
                        "date": date,
                        "start": startTime,
                        "end": endTime,
                        "breaks": [],
                        "provider_id": providerId,
                        "original_date": date
                    };
                    console.log("working plan exception from new timeslot");
                    console.log(workingPlanException);
                }
            }

            // Apply new availability to the working plan exception

            // New availability ends before previous start => add break in between
            if (endTime < workingPlanException.start) {
                workingPlanException.breaks.push({ "start": endTime, "end": workingPlanException.start });
            }
            // New availability starts before previous start => adjust start accordingly
            if (startTime < workingPlanException.start) {
                workingPlanException.start = startTime;
            }

            // New availability starts after previous end => add break in between
            if (startTime > workingPlanException.end) {
                workingPlanException.breaks.push({ "start": workingPlanException.end, "end": startTime });
            }
            // New availability ends after previous end => adjust end accordingly
            if (endTime > workingPlanException.end) {
                workingPlanException.end = endTime;
            }

            // Adjust breaks
            let breakCount = workingPlanException.breaks.length;
            for (let i = breakCount-1; i >= 0; i--) {
                // New availability covers entire break => remove break
                if (startTime <= workingPlanException.breaks[i].start && endTime >= workingPlanException.breaks[i].end) {
                    workingPlanException.breaks.splice(i, 1);
                } else {
                    // New availability overlaps the end of the break => adjust break end accordingly
                    if (startTime < workingPlanException.breaks[i].end && endTime >= workingPlanException.breaks[i].end ) {
                        workingPlanException.breaks[i].end = startTime;
                    }
                    // New availability overlaps the start of the break => adjust break start accordingly
                    if (endTime > workingPlanException.breaks[i].start && startTime <= workingPlanException.breaks[i].start ) {
                        workingPlanException.breaks[i].start = endTime;
                    }
                    // New availability is wholly inside existing break => adjust break end accordingly and add new break
                    if (startTime > workingPlanException.breaks[i].start && endTime < workingPlanException.breaks[i].end ) {
                        workingPlanException.breaks.push({ "start": endTime, "end": workingPlanException.breaks[i].end });
                        workingPlanException.breaks[i].end = startTime;
                    }
                }
            }

            // Callback to run after saving working plan exception to database has succeeded
            const successCallback = () => {
                // Update working plan exceptions to provider settings in memory
                workingPlanExceptions[date] = workingPlanException;
                provider.settings.working_plan_exceptions = JSON.stringify(workingPlanExceptions);

                // Update working plan exceptions from provider settings to UI
                $reloadAppointments.trigger('click');

                // Display success message to the user
                App.Layouts.Backend.displayNotification(lang('availability_saved'));

                // Close the modal dialog
                $availabilitiesModal.find('.alert').addClass('d-none');
                $availabilitiesModal.modal('hide');
                };

            // Save working plan axception to database
            App.Http.Calendar.saveWorkingPlanException(
                date,
                workingPlanException,
                providerId,
                successCallback,
                null,
                date,
            );
        });

        /**
         * Event : Insert Unavailability Time Period Button "Click"
         *
         * When the user clicks this button a popup dialog appears and the use can set a time period where
         * he cannot accept any appointments.
         */
        $insertAvailability.on('click', () => {
            console.log("availabilities_modal::insertAvailability.click()")
            resetModal();

            // TODO: DateTimePicker or Date and start/end values?

            const $dialog = $('#availabilities-modal');

            // Set the default datetime values.
            const startMoment = moment();

            const currentMin = parseInt(startMoment.format('mm'));

            if (currentMin > 0 && currentMin < 15) {
                startMoment.set({minutes: 15});
            } else if (currentMin > 15 && currentMin < 30) {
                startMoment.set({minutes: 30});
            } else if (currentMin > 30 && currentMin < 45) {
                startMoment.set({minutes: 45});
            } else {
                startMoment.add(1, 'hour').set({minutes: 0});
            }

            if ($('.calendar-view').length === 0) {
                $selectProvider.val($selectFilterItem.val()).closest('.form-group').hide();
            }

            App.Utils.UI.setDateTimePickerValue($startDatetime, startMoment.toDate());
            App.Utils.UI.setDateTimePickerValue($endDatetime, startMoment.add(1, 'hour').toDate());

            $dialog.find('.modal-header h3').text(lang('new_availability_title'));
            $dialog.modal('show');
        });
    }

    /**
     * Reset availability dialog form.
     *
     * Reset the "#unavailabilities-modal" dialog. Use this method to bring the dialog to the initial state
     * before it becomes visible to the user.
     */
    function resetModal() {

        // Set default time values
        const start = App.Utils.Date.format(moment().toDate(), vars('date_format'), vars('time_format'), true);

        const end = App.Utils.Date.format(
            moment().add(1, 'hour').toDate(),
            vars('date_format'),
            vars('time_format'),
            true,
        );

        App.Utils.UI.initializeDateTimePicker($startDatetime);

        $startDatetime.val(start);

        App.Utils.UI.initializeDateTimePicker($endDatetime);

        $endDatetime.val(end);
    }

    /**
     * Initialize the module.
     */
    function initialize() {
        for (const index in vars('available_providers')) {
            const provider = vars('available_providers')[index];

            $selectProvider.append(new Option(provider.first_name + ' ' + provider.last_name, provider.id));
        }

        addEventListeners();
    }

    document.addEventListener('DOMContentLoaded', initialize);

    return {
        resetModal,
    };
})();
