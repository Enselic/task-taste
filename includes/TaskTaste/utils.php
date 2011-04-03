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
 * Various utility functions.
 */
class Utils {

    /**
     * Generates entire HTML output for an AJAX response.
     *
     * @param array $ajax_object An AjaxObject to output, or NULL.
     */
    public static function output_ajax_result($ajax_object) {
        $ajax_objects = NULL;

        if ($ajax_object) {
            $ajax_objects = array($ajax_object);
        }

        Utils::output_ajax_result_array($ajax_objects);
    }

    /**
     * Generates entire HTML output for an AJAX response.
     *
     * @param array $ajax_objects An array of the AjaxObjects to
     * output, or NULL.
     */
    public static function output_ajax_result_array($ajax_objects) {
        include 'TaskTasteHtml/ajax-header.php';

        // We want to iterate a zero-element array if $ajax_objects is
        // NULL
        $ajax_objects = (array)$ajax_objects;

        foreach ($ajax_objects as $ajax_object) {
            echo $ajax_object->to_xml_tag();
        }

        include 'TaskTasteHtml/ajax-footer.php';
    }

    /**
     * Make a string safe to display in HTML. Note that before inserted
     * into a database, it needs to be escaped.
     */
    public static function make_safe_for_display($unsafe_name, $max_length) {
    
        // Remove evil chars
        $trimmed = trim($unsafe_name);
        $nonewlines = str_replace(array('\n\r', '\n', '\r'), ' ', $trimmed);
        $notags = htmlspecialchars($trimmed);
    
        // Limit length
        $nottoolong = $notags;
        if (strlen($nottoolong) > $max_length) {
            $nottoolong = substr($notags, 0, $max_length);
        }
    
        return $nottoolong;
    }
    
    /**
     * Return an id, i.e. an int > 0, from $key in $_POST. Otherwise 0.
     */
    public static function get_id_from_post($key) {
        return Utils::get_id_from_array($key, $_POST);
    }
    
    /**
     * Return a size, i.e. a float >= 0.0, from $key in $_POST. Otherwise
     * -1.
     */
    public static function get_size_from_post($key) {
        return Utils::get_size_from_array($key, $_POST);
    }
    
    /**
     * Return a name, i.e. a string length > 0, from $key in
     * $_POST. Otherwise NULL.
     */
    public static function get_name_from_post($key) {
        return Utils::get_name_from_array($key, $_POST);
    }
    
    /**
     * Return an id, i.e. an int > 0, from $key in $_GET. Otherwise 0.
     */
    public static function get_id_from_get($key) {
        return Utils::get_id_from_array($key, $_GET);
    }
    
    /**
     * Return a size, i.e. a float >= 0.0, from $key in $_GET. Otherwise
     * -1.
     */
    public static function get_size_from_get($key) {
        return Utils::get_size_from_array($key, $_GET);
    }
    
    /**
     * Return a name, i.e. a string length > 0, from $key in $_GET. Otherwise NULL.
     */
    public static function get_name_from_get($key) {
        return Utils::get_name_from_array($key, $_GET);
    }
    
    /**
     * Helper function.
     */
    private static function get_id_from_array($key, $array) {
        if (array_key_exists($key, $array) &&
            ctype_digit($array[$key]) &&
            intval($array[$key]) > 0) {
            return intval($array[$key]);
        } else {
            return 0;
        }
    }
    
    /**
     * Helper function.
     */
    private static function get_size_from_array($key, $array) {
        if (array_key_exists($key, $array) &&
            is_numeric($array[$key]) &&
            ((float)$array[$key]) >= 0.0) {
            return (float)$array[$key];
        } else {
            return -1.0;
        }
    }
    
    /**
     * Helper function.
     */
    private static function get_name_from_array($key, $array) {
        if (array_key_exists($key, $array) &&
            is_string($array[$key]) &&
            strlen($array[$key]) > 0) {
            return $array[$key];
        } else {
            return NULL;
        }
    }
}

?>
