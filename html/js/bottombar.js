/*
 * Copyright 2011 Martin Nordholts <martin@chromecode.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * Contains JS for the bottom bar on all pages.
 */

loginFormSelector = '#loginform > form';

/**
 * User log in result callback.
 */
function loginResult(data) {
    user = $('authentication-result', data);
    if (user.attr('success') == 'yes') {
        location.reload();
    } else {
        alert("Failed to log in, don't know why :(");
    }
}

/**
 * Logs in user.
 */
function login(event) {
    event.preventDefault();
    $.post(
        '/ajax/authenticate-user.php',
        $(loginFormSelector).serialize(),
        loginResult,
        "xml");
}

/**
 * Log out result callback.
 */
function logoutResult(data) {
    user = $('authentication-result', data);
    if (user.attr('success') == 'yes') {
        location.reload();
    } else {
        alert("Failed to log out, don't know why :(");
    }
}

/**
 * Log out.
 */
function logout(event) {
    event.preventDefault();
    $.post(
        '/ajax/logout.php',
        null,
        logoutResult);
}

$(function(){
    $('#logout').bind('click', logout);
    $(loginFormSelector).bind('submit', login);

    // We use this handler for all AJAX errors on all pages (the
    // bottom bar code is included on all pages)
    $('#bottom').ajaxError(function(event, request, settings, e){
        alert("There was an error when doing an AJAX request with '" + settings.url + "'\n" +
              "Please let the site owner know about this. Exception: " + e);
    });
});
