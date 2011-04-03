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

require_once 'TaskTaste/ajax-object.php';

/**
 * Represents a task.
 */
class Task implements AjaxObject
{
    /**
     * Id in the database
     */
    private $id;

    /**
     * Id of name.
     */
    private $name_id;

    /**
     * Name shown in the UI
     */
    private $name;

    /**
     * TIMESTAMP of creation
     */
    private $creation_date;

    /**
     * Project id for project the task belongs to.
     */
    private $project_id;

    /**
     * Size id for current size of the task.
     */
    private $size_id;

    /**
     * Size of task.
     */
    private $size;

    /**
     * Constructor.
     */
    public function __construct($project_id=0, $unsafe_name=NULL, $id=0, $size_id=0, $creation_date=NULL) {
        $this->creation_date = $creation_date;

        $this->set_name($unsafe_name);
        $this->set_project_id($project_id);
        $this->set_id($id);
        $this->set_size_id($size_id);
    }

    /**
     * Get id.
     */
    public function get_id() {
        return $this->id;
    }

    /**
     * Set id.
     */
    public function set_id($id) {
        $this->id = $id;
    }

    /**
     * Get size id.
     */
    public function get_size_id() {
        return $this->size_id;
    }

    /**
     * Set size id.
     */
    public function set_size_id($id) {
        $this->size_id = $id;
    }

    /**
     * Get name id.
     */
    public function get_name_id() {
        return $this->name_id;
    }

    /**
     * Set name id.
     */
    public function set_name_id($id) {
        $this->name_id = $id;
    }

    /**
     * Get size of task.
     */
    public function get_size() {
        return $this->size;
    }

    /**
     * Set size.
     */
    public function set_size($size) {
        $this->size = $size;
    }

    /**
     * Return task id as string, to set on id attribute in HTML tags.
     */
    public function get_id_string() {
      return "task-" . $this->id;
    }

    /**
     * Get name.
     */
    public function get_name() {
        return $this->name;
    }

    /**
     * Set name.
     */
    public function set_name($unsafe_name) {
        $this->name = Utils::make_safe_for_display($unsafe_name, TASKTASTE_TASK_NAME_MAX_LENGTH);
    }

    /**
     * Set name known to be safe for HTML display.
     */
    public function set_safe_name($safe_name) {
        $this->name = $safe_name;
    }

    /**
     * Get project_id.
     */
    public function get_project_id() {
        return $this->project_id;
    }

    /**
     * Set project_id.
     */
    public function set_project_id($id) {
        $this->project_id = $id;
    }

    /**
     * {@inheritdoc}
     */
    public function to_xml_tag() {
        return "<task id='{$this->get_id()}' size='{$this->get_size()}' project_id='{$this->get_project_id()}'>{$this->get_name()}</task>\n";
    }

    /**
     * Return a new Task instance if parameters are valid, else return NULL.
     */
    public static function create($project_id, $id=0, $creation_date=NULL) {
        if (is_int($project_id) &&
            $project_id > 0) {
            return new Task($project_id, $id, $creation_date);
        } else {
            return NULL;
        }
    }
}

?>