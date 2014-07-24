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

/**
 * HTML for the user bar at the top, always shown if you are logged in.
 */

$current_user = Authentication::get_logged_in_user();

?>

<?php if ($current_user) { ?>
        <div id="userbar">
            <div id="usernamediv">
              <a href="/"><?php echo $current_user->get_name() ?></a>
              <span style="color: red; padding-left: 2em;"><b>WARNING:</b> tasktaste.com will cease to exist <b>2014-10-27</b></span>
            </div>

            <div id="logoutdiv">
                <a id="logout" href="#logout">Log out</a>
            </div>
        </div>
<?php } else { ?>
        <div id="toplink">
            <span style="color: red; padding-right: 2em;"><b>WARNING:</b> tasktaste.com will cease to exist <b>2014-10-27</b></span>
            <a href="/">tasktaste.com</a>
        </div>
<?php } ?>

