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
 * Authentication utilities.
 */
class AuthenticationResult implements AjaxObject {

    /**
     * Username.
     */
    private $username;

    /**
     * Wether authentication was successful.
     */
    private $success;


    /**
     * Constructor.
     */
    public function __construct($username, $success) {
        $this->username = $username;
        $this->success = $success;
    }

    /**
     * {@inheritdoc}
     */
    public function to_xml_tag() {
        $success_string = $this->success ? "yes" : "no";
        return "<authentication-result username='{$this->username}' success='$success_string' />";
    }
}

?>
