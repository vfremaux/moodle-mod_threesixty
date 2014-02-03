<?php

/**
 * Allows a student to assess their skills accross competencies
 *
 * @author  Francois Marier <francois@catalyst.net.nz>
 * @package mod/threesixty
 */

	require_once '../../config.php';
	require_once 'locallib.php';
	require_once 'respondents_form.php';
	require_once 'respondentslib.php';

	define('RESPONSE_BASEURL', "$CFG->wwwroot/mod/threesixty/score.php?code=");

	$id      = optional_param('id', 0, PARAM_INT);  // coursemodule ID
	$a       = optional_param('a', 0, PARAM_INT);  // threesixty instance ID
	$userid  = optional_param('userid', 0, PARAM_INT);
	$delete  = optional_param('delete', 0, PARAM_INT);
	$remind  = optional_param('remind', 0, PARAM_INT);

    if ($id) {
        if (! $cm = get_coursemodule_from_id('threesixty', $id)) {
            print_error('invalidcoursemodule');
        }
        if (! $course = $DB->get_record('course', array('id' => $cm->course))) {
            print_error('coursemisconf');
        }
        if (! $threesixty = $DB->get_record('threesixty', array('id' => $cm->instance))) {
            print_error('invalidthreesixtyid', 'threesixty');
        }
        // move require_course_login here to use forced language for course
        // fix for MDL-6926
        require_course_login($course, true, $cm);
        $strthreesixtys = get_string('modulenameplural', 'threesixty');
        $strthreesixty = get_string('modulename', 'threesixty');
    } else if ($a) {

        if (! $threesixty = $DB->get_record('threesixty', array('id' => $a))) {
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
        $strthreesixtys = get_string('modulenameplural', 'threesixty');
        $strthreesixty = get_string('modulename', 'threesixty');
    } else {
        print_error('missingparameter');
    }

/// Security 

	$context = context_module::instance($cm->id);
	require_login($course, true, $cm);
	if (!has_capability('mod/threesixty:viewrespondents', $context)) {
	    require_capability('mod/threesixty:participate', $context);
	    $userid = $USER->id; // force same user
	}

	$user = null;
	if ($userid > 0 and !$user = $DB->get_record('user', array('id' => $userid))) {
	    print_error('error:invaliduserid', 'threesixty');
	}

	$url = $CFG->wwwroot."/mod/threesixty/respondents.php?a=$threesixty->id";

/// Header

	$mform = null;
	if (isset($user)) {
	    // Make sure the form has been submitted by the student
	    $returnurl = "view.php?a=$threesixty->id";
	    if (!$analysis = $DB->get_record('threesixty_analysis', array('activityid' => $threesixty->id, 'userid' => $user->id))) {
			$PAGE->set_title(format_string($threesixty->name));
			$PAGE->set_heading(format_string($threesixty->name));
			$PAGE->navbar->add($strthreesixtys, $CFG->wwwroot."/mod/theesixty/index.php?id=$course->id");
			$PAGE->navbar->add(format_string($threesixty->name));
			$PAGE->set_focuscontrol('');

			echo $OUTPUT->header();

	        echo $OUTPUT->notification(get_string('error:noscoresyet', 'threesixty'));
	        echo $OUTPUT->continue_button($returnurl);

	        echo $OUTPUT->footer($course);
	        die;
	    }
	    $currenturl = "$url&amp;userid=$user->id";
	    // Handle manual (non-formslib) actions
	    if ($remind > 0) {
	        if (!confirm_sesskey()) {
	            print_error('confirmsesskeybad', 'error', $currenturl);
	        }
	        if (!send_reminder($remind, fullname($user))) {
	            print_error('error:cannotsendreminder', 'threesixty', $currenturl);
	        }
	        redirect($currenturl);
	    }
	    if ($delete > 0) {
	        if (!confirm_sesskey()) {
	            print_error('confirmsesskeybad', 'error', $currenturl);
	        }
	        if (!threesixty_delete_respondent($delete)) {
	            print_error('error:cannotdeleterespondent', 'threesixty', $currenturl);
	        }
	        redirect($currenturl);
	    }
	    $typelist = array();
	    $i = 0;
	    foreach (explode("\n", $threesixty->respondenttypes) as $type) {
	        $t = trim($type);
	        if (!empty($t)) {
	            $typelist[$i] = $t;
	            $i++;
	        }
	    }
	    if (empty($typelist)) {
	        $typelist = array(0 => get_string('none'));
	    }
	    $sql = "
	    	SELECT 
	    		COUNT(1) 
	    	FROM 
	    		{threesixty_respondent} 
	    	WHERE 
	    		analysisid = ? AND 
	    		uniquehash IS NOT NULL
	    ";
	    $currentinvitations = $DB->count_records_sql($sql, array($analysis->id));
	    $remaininginvitations = $threesixty->requiredrespondents - $currentinvitations;
	    $analysisid = $analysis->id;
	    $mform = new mod_threesixty_respondents_form(null, compact('a', 'analysisid', 'userid', 'typelist', 'remaininginvitations'));
	    if ($fromform = $mform->get_data()) {
	        if (!request_respondent($fromform, $threesixty, $analysis->id, fullname($user))) {
	            print_error('error:cannotinviterespondent', 'threesixty', $currenturl);
	        }
	        redirect($currenturl);
	    }
	    add_to_log($course->id, 'threesixty', 'respondents', $currenturl, $threesixty->id);
	}

	$PAGE->set_url($url);
	$PAGE->set_title(format_string($threesixty->name));
	$PAGE->set_heading(format_string($threesixty->name));
	$PAGE->navbar->add(get_string('respondents', 'threesixty'));
	$PAGE->set_focuscontrol('');
	$PAGE->set_cacheable(true);
	$PAGE->set_headingmenu(navmenu($course, $cm));

	echo $OUTPUT->header();

/// Main content

	$currenttab = 'respondents';
	$section = null;
	include 'tabs.php';
	if (isset($mform)) {
	    if ($USER->id != $userid) {
	        echo threesixty_selected_user_heading($user, $course->id, $url);
	    }
	    if ($remaininginvitations > 0) {
	        $mform->display();
	    }
	    $canremind = has_capability('mod/threesixty:remindrespondents', $context);
	    $candelete = has_capability('mod/threesixty:deleterespondents', $context);
	    print_respondent_table($threesixty->id, $analysis->id, $user->id, $canremind, $candelete);
	} else {
	    //print threesixty_user_listing($threesixty, $url);
		echo print_participants_listing($threesixty, $url);
	}
	echo $OUTPUT->footer($course);

