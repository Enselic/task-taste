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
 * This file contains all SQL queries.
 */

require_once('TaskTaste/tasktaste.php');

/**
 * To reduce copy-paste errors...
 */
define('PROJECT_FIELDS', "project_id, project_urlname, project_name, project_description, project_workedperweek, project_urlname, project_owneruserid");

/**
 * All SQL dependant parts are here.
 */
class Sql {

    /**
     * Creates a project with the given name and owner user ID.
     */
    public static function create_project($unsafe_name, $owner_user_id) {
        if (!$unsafe_name ||
            !$owner_user_id ||
            !Authentication::authenticate_user_by_cookie($owner_user_id)) {
            return NULL;
        }

        $project = Project::create($unsafe_name);

        $query = new SqlQuery(
            "INSERT INTO Projects (project_name, project_urlname, project_owneruserid) VALUES ('%s', '%s', '%s')",
            $project->get_name(),
            $project->get_url_name(),
            $owner_user_id);

        // Now fill in stuff from the new row into the object we
        // created before we return it
        $project->set_id($query->get_insert_id());
        $project->set_owner_user_id($owner_user_id);
        $project->set_owner_user_name(Sql::get_user_name_from_id($owner_user_id));

        return $project;
    }

    /**
     * Get info about a project from the name.
     */
    public static function get_project_from_name($owner_user_id, $unsafe_name) {
        if (!$unsafe_name) {
            return NULL;
        }

        // Note that it is the url_name that is matched against since
        // it doesn't make sense to match against the normal name
        $url_name = Project::url_name_from_name($unsafe_name);
        return Sql::get_project_from_url($owner_user_id, $url_name);
    }

    /**
     * Get info about a project from the URL name.
     */
    public static function get_project_from_url($owner_user_id, $unsafe_url_name) {
        $url_name = Project::url_name_from_name($unsafe_url_name);

        $query = new SqlQuery(
            "SELECT " . PROJECT_FIELDS . " FROM Projects WHERE project_owneruserid='%s' AND project_urlname='%s' LIMIT 1",
            $owner_user_id,
            $url_name);

        return Sql::get_project_from_row($query->get_next_row());
    }

    /**
     * Get a Project object from a project ID.
     */
    private static function get_project_from_id($project_id) {
        $query = new SqlQuery(
            "SELECT " . PROJECT_FIELDS . " FROM Projects WHERE project_id='%s' LIMIT 1",
            $project_id);

        return Sql::get_project_from_row($query->get_next_row());
        
    }

    /**
     * Get a project object from a SQL row result.
     */
    private static function get_project_from_row($row) {
        $project = NULL;

        if ($row) {
            $project = new Project(NULL/*name*/, $row['project_urlname']);
            $project->set_id($row['project_id']);
            $project->set_safe_name($row['project_name']);
            $project->set_safe_url_name($row['project_urlname']);
            $project->set_safe_description($row['project_description']);
            $project->set_worked_per_week($row['project_workedperweek']);
            $project->set_owner_user_id($row['project_owneruserid']);
            $project->set_owner_user_name(Sql::get_user_name_from_id($row['project_owneruserid']));
        }

        return $project;
    }

    /**
     * Returns TRUE if the project owner for the project with the
     * given project id is logged in.
     */
    private static function project_owner_for_id_is_logged_in($project_id) {
        if ($project_id <= 0) {
            return FALSE;
        }

        $authed = FALSE;

        $project = Sql::get_project_from_id($project_id);
        if ($project) {
            $authed = Authentication::authenticate_user_by_cookie($project->get_owner_user_id());
        }

        return $authed;
    }

    /**
     * Sets project description. Returns string that was written to
     * the database.
     */
    public static function set_project_description($project_id, $unsafe_desc) {
        if (!($project_id && is_int($project_id) && $project_id > 0 &&
              $unsafe_desc && is_string($unsafe_desc)) ||
            !Sql::project_owner_for_id_is_logged_in($project_id)) {
            return NULL;
        }

        $desc = Project::strip_unwated_description_chars($unsafe_desc);

        $query = new SqlQuery(
            "UPDATE Projects SET project_description='%s' WHERE project_id='%s' LIMIT 1",
            $desc,
            $project_id);

        if (!$query->was_successful()) {
            $desc = NULL;
        }

        return $desc;
    }

    /**
     * Sets the "worked per week" number. Returns the number that was written to
     * the database, or -1 on failure.
     */
    public static function set_worked_per_week($project_id, $worked_per_week) {
        if (!Sql::project_owner_for_id_is_logged_in($project_id)) {
            return -1;
        }

        $query = new SqlQuery(
            "UPDATE Projects SET project_workedperweek='%s' WHERE project_id='%s' LIMIT 1",
            $worked_per_week,
            $project_id);

        if ($query->was_successful()) {
            $result = $worked_per_week;
        } else {
            $result = -1;
        }

        return $result;
    }

    /**
     * Get a task object from a SQL result row.
     */
    private static function get_task_from_row($row) {
        if (!$row) {
            return NULL;
        }

        $task = new Task();
        $task->set_name_id($row['task_nameid']);
        $task->set_safe_name(Sql::get_name($row['task_nameid']));
        $task->set_project_id($row['task_projectid']);
        $task->set_id($row['task_id']);
        $task->set_size_id($row['task_sizeid']);
        $task->set_size(Sql::get_size($row['task_sizeid']));

        return $task;
    }

    public static function get_task_from_id($task_id) {
        $query = new SqlQuery(
            "SELECT task_id, task_nameid, task_projectid, task_sizeid FROM Tasks WHERE task_id='%s' LIMIT 1",
            $task_id);

        $row = $query->get_next_row();

        return Sql::get_task_from_row($row);
    }

    /**
     * Create a new task.
     */
    public static function create_task($project_id) {
        if (!Sql::project_owner_for_id_is_logged_in($project_id)) {
            return NULL;
        }

        $task = Task::create($project_id);

        $query = new SqlQuery(
            "INSERT INTO Tasks (task_projectid) VALUES ('%s')",
            $task->get_project_id());

        if ($query->was_successful()) {
            $task->set_id($query->get_insert_id());
        } else {
            $task = NULL;
        }

        return $task;
    }

    /**
     * Create a size. User must already be authenticated.
     */
    public static function create_size_authed($size, $prev_size_id) {
        if ($size < 0.0) {
            return 0;
        }

        $query = new SqlQuery(
            "INSERT INTO Sizes (size_size, size_previd) VALUES ('%s', '%s')",
            $size,
            $prev_size_id);

        return $query->get_insert_id();
    }

    /**
     * Create a name. User must already be authenticated.
     */
    private static function create_name_authed($name, $prev_name_id) {
        if ($name == NULL || strlen($name) <= 0) {
            return 0;
        }

        $query = new SqlQuery(
            "INSERT INTO Names (name_name, name_previd) VALUES ('%s', '%s')",
            $name,
            $prev_name_id);

        return $query->get_insert_id();
    }

    /**
     * Create a user, with the given username, password and
     * email. Returns a User object.
     */
    public static function create_user($username, $password, $email) {
        $user = User::create($username, $password, $email);
        if ($user == NULL) {
          return NULL;
        }

        $email = strlen($email) > 0 ? $email : NULL;

        $query = NULL;
        try {
            if ($email) {
                $query = new SqlQuery(
                    "INSERT INTO Users (user_name, user_email, user_salt, user_passwordhash) VALUES ('%s', '%s', '%s', '%s')",
                    $user->get_name(),
                    $user->get_email(),
                    $user->get_salt(),
                    $user->get_password_hash());
            } else {
                $query = new SqlQuery(
                    "INSERT INTO Users (user_name, user_salt, user_passwordhash) VALUES ('%s', '%s', '%s')",
                    $user->get_name(),
                    $user->get_salt(),
                    $user->get_password_hash());
            }
        } catch (Exception $e) {
            $user = NULL;
        }

        if ($query && $query->was_successful()) {
            $user->set_id($query->get_insert_id());
        } else {
            $user = NULL;
        }

        return $user;
    }

    /**
     * Returns the user id for the user with the given user name.
     */
    public static function get_user_id_from_name($username) {
        $user = Sql::get_user_from_name($username);

        $user_id = -1;
        if ($user) {
            $user_id = $user->get_id();
        }

        return $user_id;
    }

    /**
     * Returns the user name from the user with the given user id.
     */
    public static function get_user_name_from_id($userid) {
        $username = NULL;

        $query = new SqlQuery(
            "SELECT user_name FROM Users WHERE user_id='%s' LIMIT 1",
            $userid);

        $row = $query->get_next_row();
        if ($row) {
            $username = $row['user_name'];
        }

        return $username;
    }

    /**
     * Add a login token. The user must already have been authorized
     * through other means, such as a password.
     */
    public static function add_login_token_authed($username, $token) {
        $user = Sql::get_user_from_name($username);

        $query = new SqlQuery(
            "INSERT INTO LoginTokens (logintoken_userid, logintoken_token) VALUES ('%s', '%s')",
            $user->get_id(),
            $token);

        return $query->was_successful();
    }

    /**
     * Get all login tokes for the user with the given ID.
     */
    public static function get_user_login_tokens($user_id) {
        if ($user_id <= 0) {
            return NULL;
        }

        $query = new SqlQuery(
            "SELECT logintoken_token FROM LoginTokens WHERE logintoken_userid='%s'",
            $user_id);

        if ($query->was_successful()) {
            $tokens = array();
        } else {
            $tokens = NULL;
        }

        while ($row = $query->get_next_row()) {
            $tokens[] = $row['logintoken_token'];
        }

        return $tokens;
    }

    /**
     * Get a User object for the user with the given user ID.
     */
    public static function get_user_from_id($userid) {
        $query = new SqlQuery(
            "SELECT * FROM Users WHERE user_id='%s' LIMIT 1",
            $userid);

        return Sql::get_user_from_row($query->get_next_row());
    }

    /**
     * Get a User object for the user with the given user name.
     */
    public static function get_user_from_name($username) {
        if (!$username) {
            return NULL;
        }

        $query = new SqlQuery(
            "SELECT * FROM Users WHERE user_name='%s' LIMIT 1",
            $username);

        return Sql::get_user_from_row($query->get_next_row());
    }

    /**
     * Get a user object from a SQL row result.
     */
    public static function get_user_from_row($row) {
        if (!$row) {
            return NULL;
        }

        $user = new User();
        $user->set_id($row['user_id']);
        $user->set_name($row['user_name']);
        $user->set_email($row['user_email']);
        $user->set_salt($row['user_salt']);
        $user->set_password_hash($row['user_passwordhash']);

        return $user;
    }

    /**
     * Authenticate a user using a password. Returns the user id of
     * the user if authentication was successful or -1 otherwise.
     */
    public static function authenticate_user($username, $password) {
        $userid = -1;

        $user = Sql::get_user_from_name($username);
        if ($user && $password && User::authenticate($user, $password)) {
            $userid = $user->get_id();
        }

        return $userid;
    }

    /**
     * Logout a user with a given id. Authentication must have been
     * made first.
     */
    public static function logout_user_authed($userid) {
        $query = new SqlQuery(
            "DELETE FROM LoginTokens WHERE logintoken_userid='%s'",
            $userid);

        return $query->was_successful();
    }

    /**
     * Return the size identifeid by the given size ID.
     */
    public static function get_size($size_id) {
        if ($size_id <= 0) {
            return 0.0;
        }

        $query = new SqlQuery(
            "SELECT size_size FROM Sizes WHERE size_id='%s'",
            $size_id);

        return $query->get_next_row_column('size_size');
    }

    /**
     * Return the name identifeid by the given name ID.
     */
    public static function get_name($name_id) {
        if ($name_id <= 0) {
            return NULL;
        }

        $query = new SqlQuery(
            "SELECT name_name FROM Names WHERE name_id='%s'",
            $name_id);

        return $query->get_next_row_column('name_name');
    }

    /**
     * Update the name for a task.
     */
    public static function update_task_name($task_id, $unsafe_task_name) {
        if (!$unsafe_task_name || strlen($unsafe_task_name) <= 0) {
            return NULL;
        }
        $task = Sql::get_task_from_id($task_id);

        if ($task && Sql::project_owner_for_id_is_logged_in($task->get_project_id())) {
            $name = Utils::make_safe_for_display($unsafe_task_name, 300);

            $old_name_id = $task->get_name_id();

            $name_id = Sql::create_name_authed($name, $old_name_id);

            if ($name_id > 0) {
                $query = new SqlQuery(
                    "UPDATE Tasks SET task_nameid='%s' WHERE task_id='%s' LIMIT 1",
                    $name_id,
                    $task_id);

                if ($query->was_successful()) {
                    $task->set_name($name);
                }
            }
        } else {
            // Failure...
            $task = NULL;
        }

        return $task;
    }

    /**
     * Deletes a task. Returns the info for the task that was deleted,
     * or NULL if there was a problem.
     */
    public static function delete_task($task_id) {
        $task = Sql::get_task_from_id($task_id);

        if ($task && Sql::project_owner_for_id_is_logged_in($task->get_project_id())) {
            $query = new SqlQuery(
                "UPDATE Tasks SET task_deleted='1' WHERE task_id='%s' LIMIT 1",
                $task_id);
            
            if (!$query->was_successful()) {
                $task = NULL;
            }
        } else {
            $task = NULL;
        }

        return $task;
    }

    /**
     * Update size of task. Old task sizes are kept as a linked list
     * inside the database, so no information is lost when updating.
     *
     * Returns task object or NULL on failure.
     */
    public static function update_task_size($task_id, $task_size) {
        if ($task_size < 0.0) return NULL;

        $task = Sql::get_task_from_id($task_id);

        if ($task && Sql::project_owner_for_id_is_logged_in($task->get_project_id())) {
            $old_size_id = $task->get_size_id();

            $old_size = Sql::get_size($old_size_id);

            $size_id = Sql::create_size_authed($task_size, $old_size_id);

            if ($size_id > 0) {
                $query = new SqlQuery(
                    "UPDATE Tasks SET task_sizeid='%s' WHERE task_id='%s' LIMIT 1",
                    $size_id,
                    $task_id);

                if ($query->was_successful()) {
                    $size_change = $task_size - $old_size;
                    Sql::update_todays_remaining_work_authed($task->get_project_id(), $size_change);
                    $task->set_size($task_size);
                }
            }
        }

        return $task;
    }

    /**
     * Update todays date with the given work change. If todays date
     * is 2011-01-23 and the remaining work size left that day is
     * 10.0, and this function is called with $work_change set to 2.0,
     * then after this function call the stored remaining work would
     * be changed to 12.0.
     *
     * The user must be authenticated.
     */
    public static function update_todays_remaining_work_authed($project_id, $work_change) {
        // Our dates are always UTC
        $todays_date = date('Y-m-d');

        $query = new SqlQuery(
            "SELECT remaining_size FROM Remaining WHERE remaining_projectid='%s' AND remaining_date='%s' LIMIT 1",
            $project_id,
            $todays_date);

        if ($query->select_was_successful()) {
            // We've updated todays date before
            $update_mode = 'update';

            $last_size = $query->get_next_row_column('remaining_size');
        } else {
            // First time we update the time for this day, use size
            // from yesterday (or rather, last day we made an update)
            $update_mode = 'insert';

            $query = new SqlQuery(
                "SELECT remaining_size FROM Remaining WHERE remaining_projectid='%s' LIMIT 1",
                $project_id);

            if ($query->select_was_successful()) {
                $last_size = $query->get_next_row_column('remaining_size');
            } else {
                // First update ever for the project, start at zero
                $last_size = 0.0;
            }
        }

        $new_size = 0;
        $tasks = Sql::get_tasks_from_project_id($project_id);
        foreach ((array)$tasks as $task) {
            $new_size += $task->get_size();
        } 

        if ($update_mode == 'update') {
            $query = new SqlQuery(
                "UPDATE Remaining SET remaining_size='%s' WHERE remaining_projectid='%s' AND remaining_date='%s' LIMIT 1",
                $new_size,
                $project_id,
                $todays_date);
        } else {
            $query = new SqlQuery(
                "INSERT INTO Remaining (remaining_projectid, remaining_size, remaining_date) VALUES ('%s', '%s', '%s')",
                $project_id,
                $new_size,
                $todays_date);
        }
    }

    /**
     * Returns the tasks for the project with the given ID. Includes
     * remaining and completed tasks, not deleted tasks.
     */
    public static function get_tasks_from_project_id($project_id) {
        if (!$project_id) {
            return NULL;
        }

        $tasks = NULL;
        $project_id = intval($project_id);

        $query = new SqlQuery(
            "SELECT task_id, task_nameid, task_projectid, task_sizeid FROM Tasks WHERE task_projectid='%s' AND task_deleted='0'",
            $project_id);

        if ($query->select_was_successful()) {
            $tasks = array();
        }

        while ($row = $query->get_next_row()) {
            $tasks[] = Sql::get_task_from_row($row);
        }

        return $tasks;
    }

    /**
     * Get projects for the user with the given user ID. Returns an
     * array of Project objects.
     */
    public static function get_projects_for_user($user_id) {
        $query = new SqlQuery(
            "SELECT " . PROJECT_FIELDS . " FROM Projects WHERE project_owneruserid='%s'",
            $user_id);

        if ($query->select_was_successful()) {
            $projects = array();
            while ($row = $query->get_next_row()) {
                $projects[] = Sql::get_project_from_row($row);
            }
        } else {
            $projects = NULL;
        }

        return $projects;
    }

    /**
     * Return plot data for the progress tracking plot, ordered by
     * date in ascending order.
     */
    public static function get_plot_data($project_id) {
        $data_points = NULL;

        $query = new SqlQuery(
            "SELECT remaining_date, remaining_size FROM Remaining WHERE remaining_projectid='%s' ORDER BY remaining_date ASC",
            $project_id);

        if ($query->select_was_successful()) {
            $data_points = array();
        }

        while ($row = $query->get_next_row()) {
            $data_points[] = new DataPoint(strtotime($row['remaining_date']), $row['remaining_size']);
        }

        return $data_points;
    }

    /**
     * Removes all data from the database. Meant to be called before
     * each time we run automatic tests on the site so we get a
     * predictable test fixture.
     */
    public static function purge_test_database() {
        $con = SqlQuery::mysql_connect();

        $all_tables = array(
            "Users",
            "LoginTokens",
            "Projects",
            "Tasks",
            "Sizes",
            "Names",
            "Remaining");

        foreach ($all_tables as $table) {
            SqlQuery::mysql_query("DELETE FROM $table", $con);
            $affected_rows_count = mysql_affected_rows($con);
            echo "Deleted $affected_rows_count from $table\n";
        }

        SqlQuery::mysql_close($con);
    }
}
