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
 * HTML for the bottombar from which the user can login for
 * example. Is on every page.
 */

$current_user = Authentication::get_logged_in_user();

?>

        <div id="bottom">
            <div id="bottomcontent">
<?php if (!$current_user) { ?>
                <div id="loginform">
                    <form action="/you-need-to-enable-javascript.php" method="post">
                        <table>
                            <tr>
                                <td>Username:</td>
                                <td><input name="username" type="text" value="" /></td>
                            </tr>
                            <tr>
                                <td>Password:</td>
                                <td><input name="password" type="password" value="" /></td>
                            </tr>
                            <tr>
                                <td>&nbsp;</td>
                                <td><input type="submit" value="Log in" /></td>
                            </tr>
                        </table>
                    </form>
                </div>
<?php } ?>

                <div id="opensource">
                    <p>Website source code under<br/>Apache Licence
                    2.0 on <a href="https://github.com/Enselic/task-taste">github</a></p>
                </div>

                <div id="runby">
                    <p>Website written and run by<br/><a href="http://www.chromecode.com/">Martin
                    Nordholts</a></p>
                </div>

            </div>
        </div>
