/**
 * copyright 2014 Daniel Butum <danibutum at gmail dot com>
 *
 * This file is part of stkaddons
 *
 * stkaddons is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * stkaddons is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with stkaddons.  If not, see <http://www.gnu.org/licenses/>.
 */
"use strict";

// define time constants
var MSECONDS_MINUTE = 60000,
    MSECONDS_HOUR = 3600000,
    MSECONDS_DAY = 86400000,
    MSECONDS_WEEK = 604800000,
    MSECONDS_MONTH = 2592000000,
    MSECONDS_YEAR = 31536000000;

var SEARCH_URL = JSON_LOCATION + "search.php";


// the url loaded on the container on page change
function registerPagination($container, url) {

    // limit changed
    $container.on("change", ".stk-pagination .limit select", function() {
        var limit = this.value,
            page = getUrlVars()["p"];

       if(!parseInt(page)) {
           page = 1;
       }

        loadContent($container, url, {p: page, l: limit}, function() {}, "GET");
    });

    // page button clicked
    $container.on("click", ".stk-pagination ul.pagination a", function() {
        var url_vars = getUrlVars(this.href),
            page = url_vars["p"],
            limit = url_vars["l"];

        if (!parseInt(page)) { // is not a valid button
            return false;
        }

        if(!limit || !parseInt(limit)) {
            limit = 10;
        }

        loadContent($container, url, {p: page, l: limit}, function() {}, "GET");

        console.log(page);
        return false;
    });
}

function isInTimeInterval(time, past_time_limit) {
    var current_time = (new Date()).getTime(),
        elapsed = current_time - time;

    return elapsed < past_time_limit;
}

// TODO add history calls
function loadContent($content, url, params, callback, request_type) {
    request_type = request_type || "GET";
    callback = callback || function() {};

    // define callback
    function onComplete(response, status, xhr) {
        if (status === "error") {
            console.error("Error on loadContent");
            console.error(response, status, xhr);
            $content.html("Sorry there was an error " + xhr.status + " " + xhr.statusText);
        } else {
            callback();
        }
    }

    if (request_type === "GET") {
        $content.load(url + "?" + $.param(params), onComplete);
    } else if (request_type === "POST") {
        $content.load(url, params, onComplete);
    } else {
        console.error("request_type: ", request_type);
        console.error("request type is invalid")
    }
}

function onFormSubmit(form_identifier, callback_success, $container, url, data_type, request_method) {
    if (!_.isFunction(callback_success)) {
        throw "callback parameter is not a function";
    }

    // make defaults
    request_method = request_method || "POST";
    data_type = data_type || {};

    // unregister previous event handler
    $container.off("submit", form_identifier);

    $container.on("submit", form_identifier, function() {
        // put all values in array
        var data = $(form_identifier).serializeArray();

        // populate with our data type
        $.each(data_type, function(name, value) {
            data.push({name: name, value: value});
        });

        $.ajax({
            type   : request_method,
            url    : url,
            data   : $.param(data),
            success: callback_success,
            error  : function(xhr, ajaxOptions, thrownError) {
                console.error("Error onFormSubmit");
                console.error(xhr, ajaxOptions, thrownError);
            }
        }).fail(function() {
            console.error("onFormSubmit post request failed");
        });

        return false;
    });
}

function getByID(id) {
    return document.getElementById(id);
}

function parseJSON(raw_string) {
    var jData = {}; // silently fail on the client side

    try {
        jData = JSON.parse(raw_string);
    } catch (e) {
        console.error("Parson JSON error: ", e);
        console.error("Raw string: ", raw_string);
    }

    return jData;
}

function growlError(message) {
    $.growl({
        title   : "Error",
        icon    : "glyphicon glyphicon-warning-sign",
        position: {
            from : "top",
            align: "center"
        },
        z_index : 9999,
        type    : "danger",
        message : message
    });
}
function growlSuccess(messsage) {
    $.growl({
        title   : "Success",
        icon    : "glyphicon glyphicon-ok-sign",
        position: {
            from : "top",
            align: "center"
        },
        z_index : 9999,
        type    : "success",
        message : messsage
    });
}

function modalDelete(message, yes_callback, no_callback) {
    yes_callback = yes_callback || function() {};
    no_callback = no_callback || function() {};

    bootbox.dialog({
        title  : "Delete",
        message: message,
        buttons: {
            danger: {
                label    : "Yes!",
                className: "btn-danger",
                callback : yes_callback
            },
            main  : {
                label    : "No",
                className: "btn-primary",
                callback : no_callback
            }
        }
    });
}

function redirectTo(url, seconds) {
    url = url || window.location.href;
    seconds = seconds || 0;

    var timeout = setTimeout(function() {
        window.location = url;
        clearTimeout(timeout);
    }, seconds * 1000);
}

// check if it is a wysiwyg5 editor
function isEditor($editor_container) {
    return $editor_container.data("wysihtml5");
}

// update the value of a wysiwyg5 editor
function editorUpdate($editor_container, value) {
    $editor_container.data("wysihtml5").editor.setValue(value);
}

// init a wysiwyg5 editor only once
function editorInit($editor_container, editor_options) {
    if (!isEditor($editor_container)) { // editor does not exist
        return $editor_container.wysihtml5(editor_options);
    }

    return null;
}

// Read a page's GET URL variables and return them as an associative array.
function getUrlVars(url) {
    url = url || window.location.href;

    var vars = {}, hash, slice_start = url.indexOf('?');

    // url does not have any GET params
    if (slice_start === -1) {
        return vars;
    }

    var hashes = url.slice(slice_start + 1).split('&');
    for (var i = 0; i < hashes.length; i++) {
        hash = hashes[i].split('=');
        vars[hash[0]] = hash[1];
    }

    return vars;
}

// Extend string. Eg "{0} is {1}".format("JS", "nice") will output "JS is nice"
if (!String.prototype.format) {
    String.prototype.format = function() {
        var args = arguments;
        return this.replace(/{(\d+)}/g, function(match, number) {
            return typeof args[number] != 'undefined' ? args[number] : match;
        });
    };
}
