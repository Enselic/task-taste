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

require_once 'TaskTaste/project.php';

/**
 * Unit tests for Project
 */
class ProjectTests extends PHPUnit_Framework_TestCase {
    public function test_url_name_from_name() {
        $cases = array(
            "Normal name with space" => "normal-name-with-space",
            "Non-ascii chars like Bäver" => "non-ascii-chars-like-bäver",
            "Non-ascii uppercase SKOKRÄM" => "non-ascii-uppercase-skokräm",
            "With comma, like this" => "with-comma-like-this",
            "   white       space    " => "white-space",
            "UPPERCASE" => "uppercase",
            "Tom&Jerry" => "tom-jerry",
            "Version number 4.1" => "version-number-4-1",
            "Question?" => "question",
            "\$\$Ev1l <0xh4c<<>> so€`´\$how!!" => "ev1l-0xh4c-so-how",
            );

        $failed = FALSE;
        foreach ($cases as $test => $expected) {
            $actual = Project::url_name_from_name($test);
            if ($actual != $expected) {
                echo "FAIL: '$test' => '$actual'   EXPECTED:   '$expected'\n";
                $failed = TRUE;
            } else {
                echo "PASS: '$test'\n";
            }
        }
        $this->assertTrue(!$failed);
    }

    public function test_strip_unwated_description_chars() {
        $cases = array(
            "Description with <div>div</div>" => "Description with div",
            "<p style='font-size:300%;'>Big text</p>" => "Big text",
            );

        $failed = FALSE;
        foreach ($cases as $test => $expected) {
            $actual = Project::strip_unwated_description_chars($test);
            if ($actual != $expected) {
                echo "FAIL: '$test' => '$actual'   EXPECTED:   '$expected'\n";
                $failed = TRUE;
            } else {
                echo "PASS: '$test'\n";
            }
        }
        $this->assertTrue(!$failed);
    }
}

?>
