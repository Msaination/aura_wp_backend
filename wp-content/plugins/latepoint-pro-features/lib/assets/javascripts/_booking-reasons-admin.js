/*
 * Only one reason applies at a time and the order form is not reloaded on a status change, so swap the
 * fields here. Each carries the status it applies on, or the one it is excluded by, so none is hardcoded.
 * The one that does not apply is disabled as well as hidden, keeping a stale value out of the submit.
 */
jQuery(function ($) {
  $('body').on('change', '.order-quick-edit-form select[name*="[bookings]["][name$="[status]"]', function () {
    const status = $(this).val();
    const $fields = $(this).closest('.order-item-booking-data-form-wrapper')
      .find('[data-reason-active-status], [data-reason-except-status]');

    const $active = $fields
      .filter('[data-reason-active-status="' + status + '"], [data-reason-except-status]')
      .not('[data-reason-except-status="' + status + '"]');

    $fields.not($active).hide().find('textarea').prop('disabled', true);
    $active.show().find('textarea').prop('disabled', false);
  });
});
