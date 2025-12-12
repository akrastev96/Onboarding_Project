/**
 * Handle AJAX dictionary search.
 */
(function($) {
    'use strict';

    $(document).ready(function() {
        var $form = $('#dictionary-search-form');
        var $input = $('#dictionary-word-input');
        var $results = $('#dictionary-results');
        var $loading = $('#dictionary-loading');

        $form.on('submit', function(e) {
            e.preventDefault();

            var word = $input.val().trim();
            if (!word) {
                alert('Please enter a word to search.');
                return;
            }

            // Show loading, hide results.
            $loading.show();
            $results.html('').hide();

            // Send AJAX request.
            $.ajax({
                url: dictionaryAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'dictionary_search',
                    nonce: dictionaryAdmin.nonce,
                    word: word
                },
                success: function(response) {
                    $loading.hide();

                    if (response.success) {
                        $results.html(response.data.html).show();
                    } else {
                        $results.html(
                            '<div class="notice notice-error"><p>' + 
                            (response.data.message || 'An error occurred.') + 
                            '</p></div>'
                        ).show();
                    }
                },
                error: function() {
                    $loading.hide();
                    $results.html(
                        '<div class="notice notice-error"><p>Network error. Please try again.</p></div>'
                    ).show();
                }
            });
        });

        // Allow Enter key to submit.
        $input.on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                $form.submit();
            }
        });
    });
})(jQuery);

