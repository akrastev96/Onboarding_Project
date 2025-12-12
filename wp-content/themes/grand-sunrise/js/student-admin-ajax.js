/**
 * Handle AJAX toggle for student active status in admin list.
 */
(function($) {
    'use strict';

    $(document).ready(function() {
        // Handle checkbox change events.
        $(document).on('change', '.student-active-toggle', function() {
            var $checkbox = $(this);
            var postId = $checkbox.data('post-id');
            var isActive = $checkbox.is(':checked') ? 1 : 0;

            // Disable checkbox during request.
            $checkbox.prop('disabled', true);

            // Send AJAX request.
            $.ajax({
                url: studentAdminAjax.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'student_toggle_active',
                    nonce: studentAdminAjax.nonce,
                    post_id: postId,
                    active: isActive
                },
                success: function(response) {
                    if (response.success) {
                        // Re-enable checkbox.
                        $checkbox.prop('disabled', false);
                        // Optionally show a success message.
                        // You could add a notice here if needed.
                    } else {
                        // Revert checkbox state on error.
                        $checkbox.prop('checked', !isActive);
                        $checkbox.prop('disabled', false);
                        alert(response.data.message || 'An error occurred.');
                    }
                },
                error: function() {
                    // Revert checkbox state on error.
                    $checkbox.prop('checked', !isActive);
                    $checkbox.prop('disabled', false);
                    alert('Network error. Please try again.');
                }
            });
        });
    });
})(jQuery);

