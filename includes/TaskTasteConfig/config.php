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
 * Site configuration.
 */
class Config {
    /**
     * Get MySQL database host name.
     */
    public static function get_mysql_server() {
        return 'localhost';
    }

    /**
     * Get name of MySQL user.
     */
    public static function get_mysql_user() {
        return 'tasktaste';
    }

    /**
     * Get MySQL user password.
     */
    public static function get_mysql_password() {
        return 'password';
    }

    /**
     * Get MySQL database name. If 'usetestdb' is set in either GET or
     * POST, return name of the test database.
     */
    public static function get_mysql_database() {
        return 'tasktaste';
    }
}

?>
