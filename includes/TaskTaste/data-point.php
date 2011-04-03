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
 * A datapoint in the "work left per date" plot.
 */
class DataPoint implements AjaxObject {
    /**
     * Date. String in the format date('Y-m-d')
     */
    private $date;

    /**
     * Size of work left the given date.
     */
    private $size;

    /**
     * Constructor.
     * @param $time Time of date (through e.g. time())
     * @param $size Size of work left. Positive float.
     */
    public function __construct($time, $size) {
        $size = (float)$size;
        if (!(is_int($time) && $time > 0 &&
              is_float($size) && $size > 0.0)) {
            throw new Exception("Invalid DataPoint constructor parameters $time, $size");
        }

        $this->date = date('Y-m-d', $time);
        $this->size = $size;
    }

    /**
     * {@inheritdoc}
     */
    public function to_xml_tag() {
        return "<point date='{$this->date}' size='{$this->size}' /> ";
    }

    /**
     * Create a new instance. Returns null on invalid parameters.
     * @param $time Time of date (through e.g. time())
     * @param $size Size of work left. Positive float.
     */
    public static function create($time, $size) {
        try {
            return new DataPoint($time, $size);
        } catch (Exception $e) {
            return NULL;
        }
    }
}
