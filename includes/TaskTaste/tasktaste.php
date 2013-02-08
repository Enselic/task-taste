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

// We always have times in UTC
date_default_timezone_set('UTC');

// FIXME: Use a host that better supports PHP include paths...
set_include_path(get_include_path() . PATH_SEPARATOR . '/customers/f/9/a/tasktaste.com/httpd.www/phpincludes');
require_once 'TaskTasteConfig/config.php';

require_once 'TaskTaste/ajax-object.php';
require_once 'TaskTaste/authentication.php';
require_once 'TaskTaste/data-point.php';
require_once 'TaskTaste/constants.php';
require_once 'TaskTaste/project.php';
require_once 'TaskTaste/sql-query.php';
require_once 'TaskTaste/sql.php';
require_once 'TaskTaste/task.php';
require_once 'TaskTaste/user.php';
require_once 'TaskTaste/utils.php';

?>
