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
 * Last function called during the edit phase of a text. It puts the
 * new text on the page.
 */
function endEdit(data, editedElement, editEventData) {
    // Enable this again
    $('input', editedElement).removeAttr('disabled');

    var responses = $('response', data)
    if (responses.children().size() > 0) {
        var resultHtml = editEventData.responseDataToHtml(data);
        $(editedElement).html(resultHtml);
        if (editEventData.refreshPlot) {
            updatePlot();
        }
        if (editEventData.endEditCallback) {
            editEventData.endEditCallback(resultHtml);
        }
    } else {
        alert("Could not set text, don't know why :(");
    }
}

/**
 * Submit edited text to the database.
 */
function submitToDatabase(editedElement, input, editEventData) {
    // Disable while waiting for reply
    $('input', editedElement).attr('disabled', 'disabled');

    $.post(editEventData.url,
           editEventData.inputToPostData(input),
           function (data) {
               endEdit(data, editedElement, editEventData);
           },
           "xml");
}

/**
 * Function that is run after a user clicks 'Save' or 'Cancel' after
 * having edited something on the project page.
 */
function editComplete(command, editedElement, input, oldHtml, editEventData) {
    if (command == 'save') {
        if (input.val().length > 0) {
            submitToDatabase(editedElement, input, editEventData);
        } else {
            alert("Sorry, but you can not set text of zero length.");
        }
    } else if (command == 'cancel') {
        editedElement.html(oldHtml);
    }
    editedElement.bind('click', editEventData, startEdit);
}

/**
 * Common function to use when text on a project page shall be
 * directly edited. It shows an intput field, with Save and Cancel
 * buttons.
 */
function startEdit(editEvent) {
    var editedElement = $(this);
    var oldHtml = editedElement.html();

    // Create UI for editing
    var inputHtml = $('<span><input id="input-field"/> <br/> <input id="save-button" type="button" value="Save"/> <input id="cancel-button" type="button" value="Cancel"/></span>');
    var input = $('> #input-field', inputHtml);
    var save = $('> #save-button', inputHtml);
    var cancel = $('> #cancel-button', inputHtml);

    // Temporarily disable, enabled again in editComplete()
    editedElement.unbind('click', startEdit);

    function cancelEvent(event) {
        // Don't let this event slip through to the click handler of
        // the element we edit
        event.stopPropagation();

        // To prevent an elsewhere focused button to be activated
        // (which I have seen happens for IE)
        event.preventDefault();

        editComplete('cancel', editedElement, input, oldHtml, editEvent.data);
    }

    function saveEvent(event) {
        // Don't let this event slip through to the click handler of
        // the element we edit
        event.stopPropagation();

        // To prevent an elsewhere focused button to be activated
        // (which I have seen happens for IE)
        event.preventDefault();

        editComplete('save', editedElement, input, oldHtml, editEvent.data);
    }
    input.bind('keypress', function(event) {
        if (event.keyCode == '13' /*return*/) {
            saveEvent(event);
        } else if (event.keyCode =='27' /*escape*/) {
            cancelEvent(event);
        }
    });
    save.bind('click', saveEvent);
    cancel.bind('click', cancelEvent);

    input.attr('value', editedElement.html());

    // Put it on the page
    editedElement.html(inputHtml);

    // Focus and select
    input.focus();
    input.select();
}

/**
 * Creates task HTML from a XML data through a HTTP response.
 */
function createTaskFromReply(data) {
    var taskTag = $(data).find('task');
    if (taskTag.size() > 0) {
        var name = taskTag.text();
        var id = taskTag.attr('id');
        var size = taskTag.attr('size');
        return $('<div class="task" id="task-' + id + '">' +
                 '    <div class="delete-placeholder"></div>' +
                 '    <div class="task-text">' +
                 '        <h3 class="title">' + name + '</h3>' +
                 '        <h3 class="size">' + size + '</h3>' +
                 '    </div>' +
                 '</div>');
    } else {
        return null;
    }
}

/**
 * Take care of the 'create task' HTTP POST callback and add the task
 * to the HTML.
 */
function addTaskCallback(data) {
    $('#add-task-button').removeAttr('disabled');

    var newTask = createTaskFromReply(data);
    $('#add-task-button').before(newTask);
    setupTaskDhtml(newTask);
    $('.title', newTask).click();
    updatePlot();
}

/**
 * Add a new task to the project. In one page session, the tasks has a
 * sequence number.
 */
taskSequenceNumber = 0;
function addTask(event) {
    $('#add-task-button').attr('disabled', 'disabled');

    taskSequenceNumber++;
    $.post('/ajax/create-task.php',
           { tasksequencenumber: taskSequenceNumber,
             projectid: getProjectId() },
           addTaskCallback,
           "xml");
}

/**
 * Updates the project schedule plot based on data from a HTTP GET
 * request.
 */
function updatePlotCallback(data) {

    // We need to calculate the maximum size ourselves, we always want
    // min to 0 and jqplot doens't seem to support this
    var maxSize = 1;

    // We extrapolate based on the last datapoint
    var lastSize = -1;
    var lastSizeDate = null;

    var beginningOfProject = null;

    lineData = [];

    // Go through all data points, oldest will be first, newest last
    $(data).find("point").each(function (index, point) {
        var size = $(point).attr('size');
        var date = $(point).attr('date');

        // The first data point is the beginning of the project
        if (beginningOfProject == null) {
            beginningOfProject = date;
        }

        if (parseInt(size) > maxSize) {
            maxSize = parseInt(size);
        }
        lineData.push([$(point).attr('date'), size]);

        // Extrapolate from the last data point, so remember it
        lastSize = size;
        lastSizeDate = date;
    });

    var workPerWeek = parseFloat(getWorkedPerWeek());
    var workPerDay = workPerWeek / 7.0;

    targetLineData = [];
    var today = new $.jsDate(new Date());
    var todaysDate = $.jsDate.strftime(today, '%Y-%m-%d');
    var todaysSize = lastSize;
    var todaysDatapoint = [todaysDate, todaysSize];

    // Defines approximately the minimum amount of days to stretch the
    // date axis to
    var approxMinDaysOnDateAxis = 5;

    var completionDateString = null;
    var chartTimeSpannInDays = approxMinDaysOnDateAxis;
    if (beginningOfProject != null && workPerWeek > 0) {
        var daysBeforeComplete = lastSize / workPerDay;
        var completionDate = new $.jsDate(new Date()).add(daysBeforeComplete, 'days');
        var daysAfterStart = today.diff(beginningOfProject, 'days');
        var sizeOfStart = daysAfterStart * workPerDay;
        var interpolatedStartDate = Date.parse(beginningOfProject);
        completionDateString = $.jsDate.strftime(completionDate, '%Y-%m-%d');
        targetLineData.push([beginningOfProject, (parseFloat(lastSize) + parseFloat(sizeOfStart))]);
        targetLineData.push(todaysDatapoint);
        targetLineData.push([completionDateString, 0]);

        chartTimeSpannInDays = completionDate.diff(beginningOfProject);
    }

    if (lastSizeDate != todaysDate) {
        // Make sure there is a datapoint for today too
        lineData.push(todaysDatapoint);
    }

    var paddedMaxSize = maxSize * 1.2;

    // Make sure we can at least have approxMinDaysOnDateAxis days on
    // the date line
    if (chartTimeSpannInDays < approxMinDaysOnDateAxis) {
        chartTimeSpannInDays = approxMinDaysOnDateAxis;
    }

    // Make sure we have at least a size of 5 on the y axis
    if (paddedMaxSize < 5) {
        paddedMaxSize = 5;
    }


    plots = [];
    series = [];
    if (lineData.length > 0) {
        plots.push(lineData);
        series.push({ label: "Work left", lineWidth: 4, showMarker: true});
    }
    if (targetLineData.length > 0 && completionDateString) {
        plots.push(targetLineData);
        var label = "Target schedule<br/>Ends:&nbsp;" + completionDateString;
        series.push({ label: label, lineWidth: 1 });
    }
    plot = $.jqplot('chartdiv', plots,
             { show: true,
               legend: { show: true },
               seriesDefaults: {
                   shadow : false,
                   showMarker: false
               },
               grid: {
                   shadow : false
               },
               axes: {
                   shadow: false,
                   xaxis: {
                       autoscale: true,
                       renderer: $.jqplot.DateAxisRenderer,
                       tickOptions: { formatString: '%Y-%m-%d' },
                       tickInterval: (chartTimeSpannInDays / approxMinDaysOnDateAxis) + ' days'
                   },
                   yaxis:{
                       autoscale: true,
                       min: 0,
                       max: paddedMaxSize,
                       tickOptions: { formatString: '%.1f' },
                       tickInterval: '' + (paddedMaxSize / 5)
                   }
               },
               series: series });

    // Always call replot so we can use this function also to update
    // the plot
    plot.replot();
}

/**
 * Updates the project schedule plot.
 */
function updatePlot() {
    $.get('/ajax/get-plot-data.php',
          { projectid: getProjectId() },
          updatePlotCallback);
}

/**
 * Things that should only be run if the project owner is logged in.
 */
function setUpOwnerStuff() {
    $('#add-task-button').bind('click', addTask);

    $('.projectdescription').bind('click',
                           { url: '/ajax/set-project-description.php',
                             inputToPostData: function(input) {
                                 return { projectid: getProjectId(),
                                          projectdesc: $(input).val() }
                             },
                             responseDataToHtml: function(data) {
                                 return $('submitted-text', data).text();
                             } },
                           startEdit);

    $('#worked-per-week-setting > .size').bind('click',
                           { url: '/ajax/set-worked-per-week.php',
                             inputToPostData: function(input) {
                                 return { projectid: getProjectId(),
                                          workedperweek: $(input).val() }
                             },
                             responseDataToHtml: function(data) {
                                 var newVal = $('worked-per-week', data).text();

                                 // Update for getWorkedPerWeek()
                                 $('#hiddendataform > #workedperweek').val(newVal);

                                 return newVal;
                             },
                             refreshPlot: true },
                           startEdit);

    // Setup DHTML stuff for all existing tasks
    $('.task').each(function(index) {
        setupTaskDhtml($(this));
    });
}

/**
 * Sets up DHTML stuff on a task, such as callbacks to run when the
 * name is clicked, to allow the task name to be editable in-place.
 */
function setupTaskDhtml(task) {
    var taskId = task.attr('id').substring('task-'.length);

    $('.title', task).bind('click',
                           { url: '/ajax/update-task-name.php',
                             inputToPostData: function(input) {
                                 return { taskid: taskId,
                                          taskname: $(input).val() }
                             },
                             responseDataToHtml: function(data) {
                                 return $('task', data).text();
                             } },
                           startEdit);
    $('.size', task).bind('click',
                           { url: '/ajax/update-task-size.php',
                             inputToPostData: function(input) {
                                 return { taskid: taskId,
                                          tasksize: $(input).val() }
                             },
                             responseDataToHtml: function(data) {
                                 return $('task', data).attr('size');
                             },
                             endEditCallback: function(resultHtml) {
                                 var hasCompleted = task.addClass('completed');
                                 var shouldHaveCompleted = resultHtml == '0';

                                 if (shouldHaveCompleted && !hasCompleted) {
                                     task.addClass('completed');
                                 } else if (!shouldHaveCompleted && hasCompleted) {
                                     task.removeClass('completed');
                                 }
                             },
                             refreshPlot: true },
                           startEdit);

    // Setup deletion of tasks
    var deleteLink = $("<a class='delete-link' href='/delete-task'><img src='/images/delete-image-35-35.png' alt='Delete task' /></a>");
    $('.delete-placeholder', task).html(deleteLink);
    deleteLink.bind('click',
                    function(event) {
                        event.preventDefault();
                        event.stopPropagation();
                        task.slideUp(function() {
                            $.post('/ajax/delete-task.php',
                                   { taskid: taskId },
                                   function (data) {
                                       if ($('task', data).size() > 0) {
                                           task.remove();
                                           updatePlot();
                                       } else {
                                           alert('Could not delete task :(');
                                       }
                                   },
                                   "xml");
                        });
                    });

}

/**
 * Helper function to get the project ID from the project HTML.
 */
function getProjectId() {
    return $('#hiddendataform > #projectid').val();
}

/**
 * Helper function to get the worked-per-week number from the project
 * HTML.
 */
function getWorkedPerWeek() {
    return $('#hiddendataform > #workedperweek').val();
}

/**
 * Helper function to check if the project owner is logged in.
 */
function getOwnerLoggedIn() {
    return $('#hiddendataform > #ownerloggedin').val() == '1';
}

$(function(){
    // Don't setup editing if the owner is not logged in
    if (getOwnerLoggedIn()) {
        setUpOwnerStuff();
    }

    updatePlot();

    $('#you-need-javascript').remove();
});
