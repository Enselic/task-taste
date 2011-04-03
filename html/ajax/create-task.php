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

$sequence_number = Utils::get_id_from_post(TASK_SEQUENCE_NUMBER);
$project_id = Utils::get_id_from_post(PROJECT_ID);

$task = Sql::create_task($project_id);
if ($task) {
    $new_name = TASKTASTE_NEW_TASK_NAME_PART1 . $sequence_number . TASKTASTE_NEW_TASK_NAME_PART2;
    $task = Sql::update_task_name($task->get_id(), $new_name);
}
if ($task) {
    $task = Sql::update_task_size($task->get_id(), TASKTASTE_NEW_TASK_SIZE);
}

Utils::output_ajax_result($task);

?>
