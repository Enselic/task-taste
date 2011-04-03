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
 * Contains JS for the dashboard where the user has an overview of
 * projects plans.
 */

var createProjectSelector = '#createproject > form';

lastTriedName = "";

/**
 * Called when the 'create project' input field changes content, so
 * that we can run validation on the project name, namely to see if a
 * project with the specified name already exists.
 */
function nameChanged(event) {
    if (lastTriedName == $('#new-project-name').val()) {
        event.preventDefault();
        return;
    }
    lastTriedName = $('#new-project-name').val();

    // Reset
    $('#new-project-submit').removeAttr('disabled');
    $('#status').html('');
    cantCreateProject = false;

    // Run validation after a small timeout
    if (timeoutId != 0) {
        clearTimeout(timeoutId);
    }
    timeoutId = setTimeout('nameChangedTimeout()', 100);
}

/**
 * Create project callback
 */
function projectCreated(data) {
    project = $(data).find('project');
    if (project.size() > 0) {
        window.location = '/projects/' + $(project).attr('project_owner_user_name') + '/' + $(project).attr('project_urlname');
    } else {
        $('#status').html("Failed to create project");
    }
}

/**
 * Creates a project.
 */
function createProject(event) {
    event.preventDefault();
    $.post(
        '/ajax/create-project.php',
        $(createProjectSelector).serialize(),
        projectCreated,
        "xml");
}

/**
 * Callback for project information fetching, used to see if a project
 * already exists.
 */
function projectInfoResponse(data) {
    if ($(data).find('project').size() > 0) {
        // Disable submit button
        $('#new-project-submit').attr('disabled', 'disabled');

        // Update status
        var status = $('#status');
        if (!status.hasClass('red')) {
            status.addClass('red');
        }
        status.html('Already a project with that name');
    }
}

/**
 * Ask the system for info about a project to see if it exists.
 */
function nameChangedTimeout() {
    timeoutId = 0;
    $.get(
        '/ajax/get-project.php',
        $(createProjectSelector).serialize(),
        projectInfoResponse);
}

$(function(){
    $('#new-project-name').focus();
    $('#new-project-name').bind('keyup', nameChanged);
    $(createProjectSelector).bind('submit', createProject);
});
