<?php

/**
 * Allows moodle users to check pending requests from other participants and attend them
 *
 * @author  Valery Fremaux <valery.fremaux@gmail.com>
 * @package mod-threesixty
 */

	require_once '../../config.php';
	require_once 'locallib.php';

	define('RESPONSE_BASEURL', "$CFG->wwwroot/mod/threesixty/score.php");
	define('DECLINE_BASEURL', "$CFG->wwwroot/mod/threesixty/requests.php");

	$id      = optional_param('id', 0, PARAM_INT);  // coursemodule ID
	$a       = optional_param('a', 0, PARAM_INT);  // threesixty instance ID
	$userid  = optional_param('userid', 0, PARAM_INT);
	$decline  = optional_param('decline', 0, PARAM_INT);

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
        $strthreesixtys = get_string("modulenameplural", 'threesixty');
        $strthreesixty = get_string("modulename", 'threesixty');
    } else {
        print_error('missingparameter');
    }

/// Security 

	$context = context_module::instance($cm->id);
	require_login($course, true, $cm);

	if ($userid > 0 and !$user = $DB->get_record('user', array('id' => $userid))) {
	    print_error('error:invaliduserid', 'threesixty');
	}

	if ($decline){
		confirm_sesskey(); // we'll be sure the decline is from the originator
		$respondent = $DB->get_record('threesixty_respondent', array('activityid' => $activityid, 'userid' => $userid, 'respondentuserid' => $USER->id));
		$respondent->declined = 1;
		$respondent->declinetime = time();
		$DB->update_record('threesixty_respondent', $respondent);
	}

/// Header

	$url = $CFG->wwwroot.'/mod/threesixty/requests.php';

	$PAGE->set_url($url);
	$PAGE->set_title(format_string($activity->name));
	$PAGE->set_heading(format_string($threesixty->name));
	$PAGE->navbar->add($strthreesixtys, $CFG->wwwroot."/mod/theesixty/index.php?id=$course->id");
	$PAGE->navbar->add(format_string($activity->name));
	$PAGE->set_focuscontrol('');
	$PAGE->set_cacheable(true);
	$PAGE->set_headingmenu(navmenu($course, $cm));

	echo $OUTPUT->header();

/// Main content

	$currenttab = 'respondents';
	$section = null;

	include 'tabs.php';

    print_requests_table($activity->id, $USER->id);

	echo $OUTPUT->footer($COURSE);

/// Start local library

/**
*
*
*/
function print_requests_table($activityid, $userid){

	$requests = threesixty_has_requests($activityid);	
	$context = context_module::instance($activityid);

	if (!empty($requests)){
		$table = new html_table();
		$table->head = array('<b>'.get_string('lastname').'</b>', '<b>'.get_string('firstname').'</b>', '');
		$table->size = array('40%', '40%', '20%');
		$table->align = array('left', 'left', 'right');
		foreach($requests as $r){
			if (empty($r->uniquehash)){
				print_error('error:unhashedrespondants', 'threesixty');
			}
			$responsehashedurl = RESPONSE_BASEURL;
	        $options = array('a' => $activityid, 'code' => $r->uniquehash, 'internal' => 1);
	        $buttons = $OUTPUT->single_button(new moodle_url($responsehashedurl, $options), get_string('assess', 'threesixty'), 'post');
	        if (has_capability('mod/threesixty:declinerequest', $context)){
				$responsehashedurl = DECLINE_BASEURL;
		        $options = array('a' => $activityid, 'decline' => $r->userid, 'sesskey' => sesskey());
		        $buttons = $OUTPUT->single_button(new moodle_url($responsehashedurl, $options), get_string('decline', 'threesixty'), 'post');
	        }
			$table->data[] = array($r->lastname, $r->firstname, $buttons);
		}
		echo html_writer::table($table);
	} else {
		echo $OUTPUT->box(get_string('norequests', 'threesixty'));
	}
}

