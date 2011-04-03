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

$current_user_id = Authentication::get_logged_in_user_id();
$current_user = Authentication::get_logged_in_user();
$projects = Sql::get_projects_for_user($current_user_id);

?>
    <div class="content-container">
        <?php include 'TaskTasteHtml/userbar.php' ?>

        <div id="createproject">
            <h2>Create</h2>
            <p>Create a new project.</p>
            <form action="/you-need-to-enable-javascript.php" method="post">
              <p>
                <input name="userid" type="hidden" value="<?php echo $logged_in_user->get_id() ?>" />
                <input id="new-project-name" name="projectname" type="text" title="Name" value="" autocomplete="off" placeholder="Name" />
                <input id="new-project-submit" type="submit" value="Create" /> <br/>
                <span id="status"></span><br/>
              </p>
            </form>
<?php foreach ((array)$projects as $project) { ?>
            <div id="managed-projects">
                <a href='<?php echo "/projects/" . $current_user->get_name() . "/" . $project->get_url_name() ?>'><?php echo $project->get_name() ?></a>
                <p><?php echo $project->get_description() ?></p>
            </div>
<?php } ?>
        </div>
    </div>

    <?php include 'TaskTasteHtml/bottombar.php' ?>
