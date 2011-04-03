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

$logged_in_user = Authentication::get_logged_in_user();

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta http-equiv="Content-Style-Type" content="text/css" />
    <script type="text/javascript" src="/externaljs/jquery-1.4.4.min.js"></script>
    <link rel="stylesheet" type="text/css" href="/css/tasktaste.css" />
    <script type="text/javascript" src="/js/bottombar.js"></script>
<?php if (!$logged_in_user) { ?>
    <title>Task Taste - <?php echo TASKTASTE_ONELINE_DESCRIPTION ?></title>
    <script type="text/javascript" src="/js/frontpage.js"></script>
    <link rel="stylesheet" type="text/css" href="/css/frontpage.css" />
<?php } else { ?>
    <title><?php echo $logged_in_user->get_name() ?> dashboard - Task Taste</title>
    <script type="text/javascript" src="/js/dashboard.js"></script>
    <link rel="stylesheet" type="text/css" href="/css/dashboard.css" />
<?php } ?>
</head>
<body>
<?php
if (!$logged_in_user) {
    include 'TaskTasteHtml/frontpage.php';
} else {
    include 'TaskTasteHtml/dashboard.php';
}
?>
</body>
</html>
