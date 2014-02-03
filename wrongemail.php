<?php

/**
 * Process a click from a user claiming that the user code did not
 * pick up the right email address. This will delete that respondent
 * and the associated response record if any.
 *
 * @author  Francois Marier <francois@catalyst.net.nz>
 * @package mod/threesixty
 */

require_once '../../config.php';
require_once 'locallib.php';

	$code = required_param('code', PARAM_ALPHANUM); // unique hash

	if (!$respondent = $DB->get_record('threesixty_respondent', array('uniquehash' => $code))) {
	    error_log("threesixty: Invalid response hash from {$_SERVER['REMOTE_ADDR']}");
	    print_error('error:invalidcode', 'threesixty');
	}
	if (!$analysis = $DB->get_record('threesixty_analysis', array('id' => $respondent->analysisid))) {
	    print_error('error:badanalysisid', 'threesixty');
	}
    if (! $threesixty = $DB->get_record('threesixty', array('id' => $analysis->activityid))) {
        print_error('invalidforumid', 'threesixty');
    }
    if (! $course = $DB->get_record('course', array('id' => $threesixty->course))) {
        print_error('coursemisconf');
    }

    if (!$cm = get_coursemodule_from_instance('threesixty', $threesixty->id, $course->id)) {
        print_error('missingparameter');
    }

    // move require_course_login here to use forced language for course
    // fix for MDL-6926
    require_course_login($course, true, $cm);

	add_to_log($course->id, 'threesixty', 'wrongemail', "wrongemail.php?code=$code", $activity->id);

// Header

	$strthreesixtys = get_string('modulenameplural', 'threesixty');
	$strthreesixty  = get_string('modulename', 'threesixty');

	$PAGE->set_title(format_string($activity->name));
	$PAGE->set_heading(format_string($threesixty->name));
	$PAGE->navbar->add($strthreesixtys, "index.php?id=$course->id");
	$PAGE->navbar->add(format_string($activity->name));	
	$PAGE->set_focuscontrol('');
	$PAGE->set_cacheable(true);
	$PAGE->set_headingmenu(navmenu($course, $cm));

	echo $OUTPUT->header();

// Main content

	if (threesixty_delete_respondent($respondent->id)) {
	    error_log("threesixty: user claims the response code doesn't match their email address -- deleted $respondent->email from (analysisid=$analysis->id)");
	} else {
	    error_log("threesixty: user claims the response code doesn't match their email address -- could not delete $respondent->email (analysisid=$analysis->id)");
	}

	echo $OUTPUT->box(get_string('adminnotified', 'threesixty'));

	echo $OUTPUT->footer($course);
