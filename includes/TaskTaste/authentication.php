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


require_once 'TaskTaste/authentication-result.php';

/**
 * Authentication utilities.
 */
class Authentication {

    /**
     * Login a user. Remember a successful login with a cookie.
     */
    public static function login($username, $password, $remeber_login) {
        $userid = Sql::authenticate_user($username, $password);

        $success = FALSE;
        if ($userid > 0) {
            $token = base64_encode(mcrypt_create_iv(96, MCRYPT_DEV_URANDOM));
            $five_years = 60 * 60 * 24 * 356 * 5;

            $domain = ($_SERVER['HTTP_HOST'] != 'localhost') ? $_SERVER['HTTP_HOST'] : FALSE;
            $ttl = $remeber_login ? (time() + $five_years) : 0;
            setcookie('token', $userid.':'.$token, $ttl, '/', $domain, FALSE /*secure*/, TRUE /*httponly*/);

            $success = Sql::add_login_token_authed($username, $token);
        }

        return new AuthenticationResult($username, $success);
    }

    /**
     * Logout the current user.
     */
    public static function logout() {
        $current_user_id = Authentication::get_current_user_id();
        if (Authentication::authenticate_user_by_cookie($current_user_id)) {
            $success = Sql::logout_user_authed($current_user_id);
        } else {
            $success = FALSE;
        }

        return new AuthenticationResult($current_user_id, $success);
    }

    /**
     * Get the user name of the currently logged in user, or NULL.
     */
    public static function get_logged_in_user() {
        $current_user_id = Authentication::get_current_user_id();
        if (Authentication::authenticate_user_by_cookie($current_user_id)) {
            $logged_in_user = Sql::get_user_from_id($current_user_id);
        } else {
            $logged_in_user = NULL;
        }
            
        return $logged_in_user;
    }

    /**
     * Get the user id of the currently logged in user, or -1.
     */
    public static function get_logged_in_user_id() {
        $current_user_id = Authentication::get_current_user_id();
        if (!Authentication::authenticate_user_by_cookie($current_user_id)) {
            $current_user_id = -1;
        }
            
        return $current_user_id;
    }

    /**
     * Authenticate a user with the given user ID, using the cookie
     * that was set when the user logged in with a password.
     */
    public static function authenticate_user_by_cookie($userid) {
        $cookie_token = Authentication::get_current_user_token();
        $valid_tokens = Sql::get_user_login_tokens($userid);

        return $valid_tokens && in_array($cookie_token, $valid_tokens);
    }

    /**
     * Helper function to get the user ID from a cookie if any, else
     * returns -1.
     */
    private static function get_current_user_id() {
        if (!isset($_COOKIE['token'])) {
            return -1;
        }

        $exploded = explode(':', $_COOKIE['token']);
        $cookie_userid = $exploded[0];

        if (ctype_digit($cookie_userid)) {
            return (int)$cookie_userid;
        } else {
            return -1;
        }
    }

    /**
     * Helper function to get the current user token, or NULL.
     */
    private static function get_current_user_token() {
        if (!isset($_COOKIE['token'])) {
            return NULL;
        }

        $splitstring = explode(':', $_COOKIE['token']);

        if (count($splitstring) == 2) {
            return $splitstring[1];
        } else {
            return NULL;
        }
    }
}

?>
