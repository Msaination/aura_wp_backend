(function ($) {
    'use strict';

    if (typeof window.latepoint_monthly_calendar_load_next_month !== 'function') return;

    const loadNextMonth = window.latepoint_monthly_calendar_load_next_month;

    window.latepoint_monthly_calendar_load_next_month = async function ($bookingFormElement) {
        if (!$bookingFormElement || !$bookingFormElement.length) return false;

        const $calendarElement = $bookingFormElement.find('.os-monthly-calendar-days-w').last();
        const $bookingForm = $bookingFormElement.find('.latepoint-form').first();
        const calendarYear = Number($calendarElement.attr('data-calendar-year'));
        const calendarMonth = Number($calendarElement.attr('data-calendar-month'));

        if (!$calendarElement.length || !$bookingForm.length || !Number.isInteger(calendarYear) || !Number.isInteger(calendarMonth) || calendarMonth < 1 || calendarMonth > 12) {
            $bookingFormElement.removeClass('step-content-loading step-content-mid-loading').addClass('step-content-loaded');
            $bookingFormElement.find('.os-dates-and-times-w').removeClass('is-searching');
            return false;
        }

        if ($bookingFormElement.data('calendar-month-loading')) return false;

        $bookingFormElement.data('calendar-month-loading', true);
        try {
            return await loadNextMonth($bookingFormElement);
        } finally {
            $bookingFormElement.removeData('calendar-month-loading');
            $bookingFormElement.find('.os-month-next-btn').removeClass('os-loading');
        }
    };
})(jQuery);