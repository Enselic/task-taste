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

$project_urlname = Utils::get_name_from_get(PROJECT_NAME);
$project_ownername = Utils::get_name_from_get(USERNAME);

$project = NULL;
$project_owner = Sql::get_user_from_name($project_ownername);
if ($project_owner) {
    $project = Sql::get_project_from_url ($project_owner->get_id(), $project_urlname);
}

$tasks = NULL;
$project_name = "&lt;Unknown project&gt;";
$project_owner_name = "&lt;Unknown owner&gt;";
$project_description = "This project does not exist yet.";
$project_id = 0;
$worked_per_week = -1;
$owner_logged_in = FALSE;

if ($project) {
    $project_name = $project->get_name();
    $project_description = $project->get_description();
    $project_id = $project->get_id();
    $tasks = Sql::get_tasks_from_project_id($project_id);
    $project_owner = Sql::get_user_from_id($project->get_owner_user_id());
    $owner_logged_in = Authentication::authenticate_user_by_cookie($project->get_owner_user_id());
    $worked_per_week = $project->get_worked_per_week();
    if ($project_owner) {
        $project_owner_name = $project_owner->get_name();
    }
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title><?php echo $project_owner_name ?>/<?php echo $project_name ?> - Task Taste Project Schedule</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

    <!-- For jqPlot -->
    <!--[if lt IE 9]><script language="javascript" type="text/javascript" src="/externaljs/excanvas.js"></script><![endif]-->

    <script language="javascript" type="text/javascript" src="/externaljs/jquery-1.4.4.min.js"></script>
    <script language="javascript" type="text/javascript" src="/externaljs/jquery.jqplot.js"></script>
    <script language="javascript" type="text/javascript" src="/externaljs/jqplot.dateAxisRenderer.js"></script>

    <script language="javascript" type="text/javascript" src="/js/bottombar.js"></script>
    <script language="javascript" type="text/javascript" src="/js/manage-project.js"></script>

    <link rel="stylesheet" type="text/css" href="/externalcss/jquery.jqplot.css" />

    <link rel="stylesheet" type="text/css" href="/css/tasktaste.css" />
    <link rel="stylesheet" type="text/css" href="/css/manage-project.css" />
</head>
<body>
    <div class="content-container">
        <?php include 'TaskTasteHtml/userbar.php' ?>

        <div id="schedule-head">
            <h1><?php echo $project_name ?></h1>
            <div class="projectdescription"><?php echo $project_description ?></div>
        </div>

        <div id="tracking">
            <p id="you-need-javascript">
                You need to enable JavaScript in order to see a plot
                of remaning work and to get an estimate of project
                completion.
            </p>
            <div id="chartdiv"></div>
        </div>

        <div id="planning">
            <div id="headers">
                <span class="column1 column-header">Tasks left</span>
                <span class="column2 column-header">Size</span>
            </div>
            <div id="tasks">
<?php foreach ((array)$tasks as $task) {
          $include_at_all = $task->get_size() > 0 || $owner_logged_in;
          
          if ($include_at_all) {
              $classes = "task";
              if ($task->get_size() == 0) {
                  $classes .= " completed";
              } ?>
                <div class="<?php echo $classes ?>" id="<?php echo $task->get_id_string(); ?>">
                    <div class="delete-placeholder"></div>
                    <div class="task-text">
                        <h3 class="title"><?php echo $task->get_name(); ?></h3>
                        <h3 class="size"><?php echo $task->get_size(); ?></h3>
                    </div>
                </div>
<?php     }
      }
      if ($project && $owner_logged_in) { ?>
                <div id="add-task-container">
                    <input id="add-task-input-field"/> <button id="add-task-button">Add task</button>
                </div>
<?php } ?>
            </div>
<?php if ($project && $owner_logged_in) { ?>
            <div id="worked-per-week-setting">
                <div id="description">
                    <span class="column-header">Worked per week</span><br/>Defines the <i>Target schedule</i> plot
                </div>
                <h3 class="size"><?php echo $worked_per_week ?></h3>
            </div>
<?php } ?>
        </div>
    </div>

    <?php include 'TaskTasteHtml/bottombar.php' ?>

    <form id="hiddendataform" action="get">
        <input type="hidden" id="projectid" value="<?php echo $project_id ?>" />
        <input type="hidden" id="workedperweek" value="<?php echo $worked_per_week ?>" />
        <input type="hidden" id="ownerloggedin" value="<?php echo $owner_logged_in ? 1 : 0 ?>" />
    </form>
</body>
</html>
