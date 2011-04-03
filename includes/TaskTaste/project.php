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
require_once 'TaskTaste/constants.php';

/**
 * Represents a project.
 */
class Project
{
    /**
     * Id in the database
     */
    private $id;

    /**
     * User id of owner.
     */
    private $owner_user_id;

    /**
     * User name of owner.
     */
    private $owner_user_name;

    /**
     * Name used in URLs
     */
    private $url_name;

    /**
     * Name shown in the UI
     */
    private $name;

    /**
     * Description of project.
     */
    private $description;

    /**
     * How much that is worked per week on the project.
     */
    private $worked_per_week;

    /**
     * TIMESTAMP of creation
     */
    private $creation_date;

    /**
     * Constructor.
     */
    public function __construct($unsafe_name=NULL, $url_name=NULL, $id=NULL, $creation_date=NULL) {
        if ($unsafe_name == NULL && $url_name == NULL) {
            throw new Exception('One of $unsafe_name and $url_name must not be NULL');
        }

        $this->creation_date = $creation_date;

        $this->set_id($id);
        $this->set_name($unsafe_name);
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
     * Get owner user id.
     */
    public function get_owner_user_id() {
        return $this->owner_user_id;
    }

    /**
     * Set owner user id.
     */
    public function set_owner_user_id($id) {
        $this->owner_user_id = $id;
    }

    /**
     * Get owner user name.
     */
    public function get_owner_user_name() {
        return $this->owner_user_name;
    }

    /**
     * Set owner user name.
     */
    public function set_owner_user_name($name) {
        $this->owner_user_name = $name;
    }

    /**
     * Get name.
     */
    public function get_url_name() {
        return $this->url_name;
    }

    /**
     * Set safe URL name. Not validate, only pass validated input.
     */
    public function set_safe_url_name($safe_url_name) {
        $this->url_name = $safe_url_name;
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
        // Convert to url name from unsafe name, to get an as good url name as possible
        $this->url_name = Project::url_name_from_name($unsafe_name);

        $this->name = Utils::make_safe_for_display($unsafe_name, TASKTASTE_PROJECT_NAME_MAX_LENGTH);
    }

    /**
     * Set name without making HTML safe. Must only be called if you
     * know the name is safe, like if you get it from the database
     * where the written string has already been made safe.
     */
    public function set_safe_name($name) {
        $this->name = $name;
    }

    /**
     * Set description.
     */
    public function set_description($unsafe_desc) {
        // Convert to url name from unsafe name, to get an as good url name as possible
        $this->description = Project::strip_unwated_description_chars($unsafe_desc);
    }

    /**
     * Set description without processing string.
     */
    public function set_safe_description($safe_desc) {
        // Convert to url name from unsafe name, to get an as good url name as possible
        $this->description = $safe_desc;
    }

    /**
     * Get description.
     */
    public function get_description() {
        return $this->description;
    }

    /**
     * Get how much is worked per week on the project.
     */
    public function get_worked_per_week() {
        return $this->worked_per_week;
    }

    /**
     * Set how much is worked per week on the project.
     */
    public function set_worked_per_week($worked_per_week) {
        $this->worked_per_week = $worked_per_week;
    }

    /**
     * {@inheritdoc}           
     */
    public function to_xml_tag() {
        return "<project id='{$this->get_id()}' project_urlname='{$this->get_url_name()}' project_owner_user_id='{$this->get_owner_user_id()}' project_owner_user_name='{$this->get_owner_user_name()}'>{$this->get_name()}</project>";
    }



    /**
     * Strip unallowed tags from a project description. Result string
     * is displayed on the website as-is and will thus be intepreted
     * as HTML.
     */
    public static function strip_unwated_description_chars($unsafe_desc) {
        return strip_tags($unsafe_desc, TASKTASTE_ALLOWED_TAGS);
    }

    /**
     * Create an url string from an arbitrary project name.
     */
    public static function url_name_from_name($name) {
        if (!$name) {
            return NULL;
        }
            
        // Trim
        $name = trim($name);

        // Remove evil white space
        $name = str_replace(array("\t", "\n", "\r", "\0", "\x0B"), "", $name);

        // Replace things that are not letters and numbers with -
        $property_classes = array('C' /*other*/,
                                  'M' /*mark*/, 
                                  'P' /*punctuation*/, 
                                  'S' /*symbol*/, 
                                  'Z' /*separator*/);
        foreach ($property_classes as $p) {
            $name = preg_replace("/\p{$p}/u", "-", $name);
        }

        // For extra safety, get rid of evil chars again
        $name = str_replace(array("$", "&", "+", ",", "/", ":", ";", "=", "?", "@", "<", ">", "#", "%",
                                  "{", "}", "|", "\\", "^", "~", "[", "]", "`", "'", "´" ),
                            "",
                            $name);

        // Get rid of ugyly '-' duplicates
        $name = preg_replace("/-+/", "-", $name);

        // Unicode compatible lowercase
        $name = mb_strtolower($name, "utf-8");

        // If the string is only a "-", that's silly, set to ""...
        $name = preg_replace("/^-$/", "", $name);

        // Leading and trailing "-" is also silly
        $name = trim($name, "-");

        return strlen($name) > 0 ? $name : NULL;
    }

    /**
     * Helper function to create a project.
     */
    public static function create($unsafe_name=NULL, $url_name=NULL, $id=NULL, $creation_date=NULL) {
        return new Project($unsafe_name, $url_name, $id, $creation_date);
    }
}

?>
