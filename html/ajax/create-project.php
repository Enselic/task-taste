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
set_include_path(get_include_path() . PATH_SEPARATOR . '/customers/tasktaste.com/tasktaste.com/httpd.www/phpincludes');
require_once 'TaskTaste/tasktaste.php';

// Create the project
$unsafe_name = Utils::get_name_from_post(PROJECT_NAME);
$userid = Utils::get_id_from_post(USERID);

$username = Sql::get_user_name_from_id($userid);
if ($username) {

    // Create project
    $project = Sql::create_project($unsafe_name, $userid);

    if ($project) {
        $project_id = $project->get_id();

        // Setup default settings
        Sql::set_worked_per_week($project_id, 1.0);
        Sql::set_project_description($project_id, TASKTASTE_DEFAULT_PROJECT_DESCRIPTION);

        // Create default tasks
        // Task 1
        $task1 = Sql::create_task($project_id);
        if ($task1) {
            Sql::update_task_name($task1->get_id(), TASKTASTE_FIRST_TASK_NAME);
            Sql::update_task_size($task1->get_id(), TASKTASTE_FIRST_TASKS_SIZE);
        }

        // Task 2
        $task2 = Sql::create_task($project_id);
        if ($task2) {
            Sql::update_task_name($task2->get_id(), TASKTASTE_SECOND_TASK_NAME);
            Sql::update_task_size($task2->get_id(), TASKTASTE_FIRST_TASKS_SIZE);
        }
    }
}

Utils::output_ajax_result($project);

?>
