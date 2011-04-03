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

// FIXME: Use a host that better supports PHP include paths...
set_include_path(get_include_path() . PATH_SEPARATOR . '/customers/tasktaste.com/tasktaste.com/httpd.www/phpincludes');
require_once 'TaskTaste/tasktaste.php';

$project_id = Utils::get_id_from_post(PROJECT_ID);
$project_desc = Utils::get_name_from_post(PROJECT_DESCRIPTION);

$desc = Sql::set_project_description($project_id, $project_desc);

include 'TaskTasteHtml/ajax-header.php';
if ($desc) {
    echo "<submitted-text><![CDATA[$desc]]></submitted-text>";
}
include 'TaskTasteHtml/ajax-footer.php';

?>
