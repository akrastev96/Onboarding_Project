(function ($) {
    $(function () {
        var $container = $('#student-ajax-settings');
        if (!$container.length || typeof studentAjaxSettings === 'undefined') {
            return;
        }

        var $status = $('.student-ajax-status');

        function setStatus(message, isError) {
            if (!$status.length) {
                return;
            }
            $status.text(message).css({
                color: isError ? '#b32d2e' : '#1d8f36',
                fontWeight: '600'
            });
            setTimeout(function () {
                $status.text('');
            }, 2000);
        }

        $container.on('change', '.student-ajax-setting', function () {
            var $checkbox = $(this);
            var field = $checkbox.data('field');
            var value = $checkbox.is(':checked') ? 1 : 0;

            $.post(studentAjaxSettings.ajaxUrl, {
                action: 'student_update_visibility',
                nonce: studentAjaxSettings.nonce,
                field: field,
                value: value
            })
            .done(function (response) {
                if (response && response.success) {
                    setStatus('Saved', false);
                } else {
                    setStatus('Save failed', true);
                }
            })
            .fail(function () {
                setStatus('Save failed', true);
            });
        });
    });
})(jQuery);

