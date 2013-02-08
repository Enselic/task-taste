<?php
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

// FIXME: Use a host that better supports PHP include paths...
set_include_path(get_include_path() . PATH_SEPARATOR . '/customers/f/9/a/tasktaste.com/httpd.www/phpincludes');
require_once 'TaskTaste/tasktaste.php';

$username = Utils::get_name_from_post(NEW_USERNAME);
$password = Utils::get_name_from_post(NEW_PASSWORD);
$email = Utils::get_name_from_post(NEW_EMAIL);

// Create user
$user = Sql::create_user($username, $password, $email);

// Log him in
$logged_in = FALSE;
if ($user) {
    $logged_in = Authentication::login($username, $password, TRUE /*remeber_login*/);
}

Utils::output_ajax_result($user);

?>
