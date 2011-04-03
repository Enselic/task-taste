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
class User
{
    /**
     * User ID.
     */
    private $id;

    /**
     * User name.
     */
    private $name;

    /**
     * User email (optional).
     */
    private $email;

    /**
     * User password salt.
     */
    private $salt;

    /**
     * Password hash, including salt.
     */
    private $password_hash;

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
     * Get name.
     */
    public function get_name() {
        return $this->name;
    }

    /**
     * Set name.
     */
    public function set_name($name) {
        $this->name = $name;
    }

    /**
     * Get email.
     */
    public function get_email() {
        return $this->email;
    }

    /**
     * Set email.
     */
    public function set_email($email) {
        $this->email = $email;
    }

    /**
     * Get salt.
     */
    public function get_salt() {
        return $this->salt;
    }

    /**
     * Set salt.
     */
    public function set_salt($salt) {
        $this->salt = $salt;
    }

    /**
     * Get password hash.
     */
    public function get_password_hash() {
        return $this->password_hash;
    }

    /**
     * Set password hash.
     */
    public function set_password_hash($hash) {
        $this->password_hash = $hash;
    }

    /**
     * {@inheritdoc}
     */
    public function to_xml_tag() {
        return "<user id='{$this->get_id()}' name='{$this->get_name()}' loggedin='{$logged_in}'/>";
    }


    /**
     * Authenticate a user with a password.
     *
     * Returns TRUE if the password matches, FALSE otherwise.
     */
    public static function authenticate($user, $password) {
        $test_hash = hash('whirlpool', $user->get_salt() . $password);
        return $user->get_password_hash() == $test_hash;
    }

    /**
     * Helper function to create a user after applying checks on the
     * in parameters. Rather than throwing an exception, this returns
     * NULL if parameters are invalid.
     */
    public static function create($name, $password, $email) {
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        if (!ctype_alnum($name) ||
            ($email != NULL && !filter_var($email, FILTER_VALIDATE_EMAIL))) {
            return NULL;
        }

        $salt = base64_encode(mcrypt_create_iv(16, MCRYPT_DEV_URANDOM));
        $hash = hash('whirlpool', $salt . $password);

        $user = new User();
        $user->set_name($name);
        $user->set_email($email);
        $user->set_salt($salt);
        $user->set_password_hash($hash);

        return $user;
    }
}

?>
