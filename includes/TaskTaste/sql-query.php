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

require_once('TaskTaste/tasktaste.php');


/**
 * Class so we can apply RAII to SQL queries.
 */
class SqlQuery {

    /**
     * SQL connection.
     */
    private $con;

    /**
     * Query string to run.
     */
    private $query_string;

    /**
     * Result of query.
     */
    private $result;

    /**
     * Constructor. printf() behavior.
     */
    public function __construct() {
        $argv = func_get_args();
        $format = array_shift($argv);

        SqlQuery::construct($format, $argv);
    }

    /**
     * Implementation of printf() behavior for constructor.
     */
    private function construct($format, $argv) {
        $this->con = SqlQuery::mysql_connect();

        // Apply mysql_real_escape_string() on all format args
        foreach ($argv as &$arg) {
            $arg = mysql_real_escape_string($arg, $this->con);
        }

        $this->query_string = vsprintf($format, $argv);
        $this->result = SqlQuery::mysql_query($this->query_string, $this->con);
    }

    /**
     * Destructor, meant to be automatically invoked as the SqlQuery
     * object goes out of scope.
     */
    public function __destruct() {
        SqlQuery::mysql_close($this->con);
    }

    /**
     * Check if the query was successful (NOTE: Does not apply to
     * SELECT-queries)
     */
    public function was_successful() {
        return $this->result;
    }

    /**
     * Check if a SELECT query was successful.
     */
    public function select_was_successful() {
        return $this->result && mysql_num_rows($this->result);
    }

    /**
     * Get the (MySQL) result of the query.
     */
    public function get_result() {
        return $this->result;
    }

    /**
     * Get the next result row.
     */
    public function get_next_row() {
        return mysql_fetch_array($this->result);
    }

    /**
     * Get the insert ID from the query.
     */
    public function get_insert_id() {
        return mysql_insert_id($this->con);
    }

    /**
     * Dump the query using var_dump().
     */
    public function dump() {
        var_dump($this->query_string);
    }

    /**
     * Get the next row, and return the value of the given column.
     */
    public function get_next_row_column($field_name) {
        $result = NULL;

        $row = $this->get_next_row();
        if ($row) {
            $result = $row[$field_name];
        }

        return $result;
    }



    /**
     * Helper function to setup MySQL connection.
     */
    public static function mysql_connect() {
        // Make sure to always create a new link, since each
        // mysql_connect() is matched with an mysql_close() through
        // our RAII-enabling SqlQuery class
        $con = mysql_connect(Config::get_mysql_server(),
                             Config::get_mysql_user(),
                             Config::get_mysql_password(),
                             TRUE /*new_link*/);
        if (!$con) {
            throw new Exception("mysql_connect() failed: " . mysql_error());
        }

        mysql_select_db(Config::get_mysql_database(), $con);
        if (mysql_errno($con)) {
            throw new Exception("mysql_select_db() failed: " . mysql_error($con));
        }

        return $con;
    }

    /**
     * MySQL query helper.
     */
    public static function mysql_query($query_string, $con) {
        $result = mysql_query($query_string, $con);
        if (mysql_errno($con)) {
            throw new Exception("mysql_query() `$query_string` failed: " . mysql_error($con));
        }
        return $result;
    }

    /**
     * MySQL close helper.
     */
    public static function mysql_close($con) {
        if (!mysql_close($con)) {
            throw new Exception("mysql_close() failed: " . mysql_error($con));
        }
    }
}
