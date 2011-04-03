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

timeoutId = 0;

/**
 * Account created result callback.
 */
function accountCreated(data) {
    user = $(data).find('user');
    if (user.size() > 0) {
        location.reload();
    } else {
        alert("Failed to create account, try a different username.");
    }
}

/**
 * Create account.
 */
function createAccount(event) {
    event.preventDefault();
    $.post(
        '/ajax/create-account.php',
        $('#create-account > form').serialize(),
        accountCreated,
        "xml");
}

$(function(){
    $('#create-account > form').bind('submit', createAccount);
    $('[name="username"]').focus();
    $('#create-account [name="new-username"]').attr('autocomplete', 'off');
});
