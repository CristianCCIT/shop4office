/**
 * LoginFocus.js
 *
 * By
 * @author Boris Wintein
 * for ABOservice.
 *
 * @description Focusses the cursor on login-field, or password (auto-fill in) or submits if both filled in.
 * @usage   Include in every page where there is a form with login as name. This script only targets those. Add the class .ignore if you want
 *          the script to ignore the fields. If the ignore class is added to the submit-button, auto-submit is disabled.
 * @depends Depends on jQuery.
 */

// Use jQuery namespace for compatibility. It passes itself as object, so we can capture it in $
jQuery(document).ready(function($) {

    var loginBox = $('[name=login]');

    if (!loginBox.hasClass('ignore')) {

        var mail = loginBox.find('#email_address');
        var password = loginBox.find('#password');

        if (mail.val() == '' || mail.val() == ' '){

            mail.focus();
            mail.select();
        } else {

            if (password.val() == '' || password.val() == ' ') {

                password.focus();
                password.select();
            } else {

                if (!loginBox.find('[type=submit]').hasClass('ignore')) {
                    //loginBox.find('[type=submit]').submit();
                    loginBox.find('[type=submit]').focus();
                    loginBox.find('[type=submit]').select();
                } else {

                    loginBox.find('[type=submit]').focus();
                    loginBox.find('[type=submit]').select();
                }
            }
        }

    }
});


