jQuery(document).ready(function($) {

    $('.er-email-form').on('submit', function(e) {
        e.preventDefault();

        var $form = $(this);
        var $message = $form.find('.er-message');
        var $submitBtn = $form.find('.er-submit-btn');
        var email = $form.find('input[name="email"]').val();

        // Disable submit button and show loading state
        $submitBtn.prop('disabled', true).text('Processing...');
        $message.hide();

        $.ajax({
            url: erAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'er_process_email',
                nonce: erAjax.nonce,
                email: email
            },
            success: function(response) {
                if (response.success) {
                    var redirectUrl = response.data.url;

                    // Try to open in new window
                    var newWindow = window.open(redirectUrl, '_blank');

                    // Show success message with link
                    var message = 'Redirecting... ';

                    // Check if popup was blocked
                    if (!newWindow || newWindow.closed || typeof newWindow.closed === 'undefined') {
                        message += 'If the page did not open, <a href="' + redirectUrl + '" target="_blank">click here</a>.';
                    } else {
                        message += '<a href="' + redirectUrl + '" target="_blank">Click here</a> if it did not open automatically.';
                    }

                    $message.html(message).removeClass('er-error').addClass('er-success').show();

                } else {
                    $message.text(response.data.message).removeClass('er-success').addClass('er-error').show();
                }
            },
            error: function() {
                $message.text('An error occurred. Please try again.').removeClass('er-success').addClass('er-error').show();
            },
            complete: function() {
                $submitBtn.prop('disabled', false).text('Submit');
            }
        });
    });
});
