<?php

/**
 * Allows a teacher/admin to edit the scores entered by a student
 *
 * @author  Francois Marier <francois@catalyst.net.nz>
 * @package mod/threesixty
 */

	require_once '../../config.php';
	require_once 'amend_form.php';
	require_once 'locallib.php';

	$id      = optional_param('id', 0, PARAM_INT);  // course module ID
	$a      = optional_param('a', 0, PARAM_INT);  // threesixty instance ID
	$typeid = required_param('typeid', PARAM_INT); // the type of the response
	$userid = optional_param('userid', 0, PARAM_INT);

    if ($id) {
        if (! $cm = get_coursemodule_from_id('threesixty', $id)) {
            print_error('invalidcoursemodule');
        }
        if (!$course = $DB->get_record('course', array('id' => $cm->course))) {
            print_error('coursemisconf');
        }
        if (!$threesixty = $DB->get_record('threesixty', array('id' => $cm->instance))) {
            print_error('invalidthreesixtyid', 'threesixty');
        }
        // move require_course_login here to use forced language for course
        // fix for MDL-6926
        require_course_login($course, true, $cm);
        $strthreesixtys = get_string('modulenameplural', 'threesixty');
        $strthreesixty = get_string('modulename', 'threesixty');
    } else if ($a) {

        if (!$threesixty = $DB->get_record('threesixty', array('id' => $a))) {
            print_error('invalidforumid', 'threesixty');
        }
        if (!$course = $DB->get_record('course', array('id' => $threesixty->course))) {
            print_error('coursemisconf');
        }

        if (!$cm = get_coursemodule_from_instance("forum", $threesixty->id, $course->id)) {
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

	$context = context_module::instance($cm->id);
	
	require_login($course, true, $cm);
	require_capability('mod/threesixty:view', $context);
	require_capability('mod/threesixty:edit', $context);

	$url = $CFG->wwwroot."/mod/threesixty/amend.php?a=$threesixty->id&typeid=$typeid";
	
	$user = $DB->get_record('user', array('id' => $userid));

	$mform = null;
	$usertable = null;
	if (isset($user)) {
	
	    $currenturl = "$url&amp;userid=$user->id";
	    $returnurl = "view.php?a=$threesixty->id";
	
	    $skillnames = threesixty_get_skill_names($threesixty->id);
	
	    if (!$analysis = $DB->get_record('threesixty_analysis', array('activityid' => $threesixty->id, 'userid' => $user->id))) {
	        print_error('error:nodataforuserx', 'threesixty', $returnurl, fullname($user));
	    }
	    if (!$respondent = $DB->get_record('threesixty_respondent', array('analysisid' => $analysis->id, 'type' => $typeid, 'uniquehash' => null))){
	        print_error('error:nodataforuser', 'threesixty', $returnurl, fullname($user));
	    }
	    if (!$response = $DB->get_record('threesixty_response', array('analysisid' => $analysis->id, 'respondentid' => $respondent->id))) {
	        print_error('error:nodataforuser', 'threesixty', $returnurl, fullname($user));
	    }
	    if (!$response->timecompleted) {
	        print_error('error:userhasnotsubmitted', 'threesixty', $returnurl, fullname($user));
	    }
	    if (!$selfscores = threesixty_get_self_scores($analysis->id, false, $typeid)) {
	        print_error('error:nodataforuser', 'threesixty', $returnurl, fullname($user));
	    }
	
	    $mform = new mod_threesixty_amend_form(null, compact('a', 'skillnames', 'userid', 'typeid'));
	
	    if ($mform->is_cancelled()){
	        redirect($baseurl);
	    }
	
	    if ($fromform = $mform->get_data()) {
	
	        $returnurl .= "&amp;userid=$user->id";
	
	        if (!empty($fromform->submitbutton)) {
	            $errormsg = save_changes($fromform, $response->id, $skillnames);
	            if (!empty($errormsg)) {
	                print_error('error:cannotsavescores', 'threesixty', $currenturl, $errormsg);
	            }
	
	            redirect($returnurl);
	        }
	        else {
	            print_error('error:unknownbuttonclicked', 'threesixty', $returnurl);
	        }
	    }
	
	    add_to_log($course->id, 'threesixty', 'amend', $currenturl, $threesixty->id);
	}

// Header

	$PAGE->set_url($url);
	$PAGE->set_title(format_string($threesixty->name));
	$PAGE->set_heading(format_string($threesixty->name));
	$PAGE->navbar->add($strthreesixtys, "index.php?id=$course->id");
	$PAGE->navbar->add(format_string($threesixty->name));
	$PAGE->set_focuscontrol('');
	$PAGE->set_cacheable(true);
	$PAGE->set_headingmenu(navmenu($course, $cm));

	echo $OUTPUT->header();

// Main content
$currenttab = 'activity';
$section = 'scores';
include 'tabs.php';

if (isset($mform)) {
    print threesixty_selected_user_heading($user, $course->id, 'profiles.php?a='.$threesixty->id);

    set_form_data($mform, $selfscores);
    $mform->display();
} else {
    print threesixty_user_listing($threesixty, $url);
}

echo $OUTPUT->footer($course);

function set_form_data($mform, $scores){
    $toform = array();

    if (!empty($scores->records) and count($scores->records) > 0) {
        foreach ($scores->records as $score) {
            $toform["radioarray_{$score->id}[score_{$score->id}]"] = $score->score;
        }
    }

    $mform->set_data($toform);
}

function save_changes($formfields, $responseid, $skills){
    global $CFG, $DB;

    foreach ($skills as $skill) {
        $arrayname = "radioarray_$skill->id";
        if (empty($formfields->$arrayname)) {
            error_log("threesixty: $arrayname is missing from the submitted form fields");
            return get_string('error:formsubmissionerror', 'threesixty');
        }
        $a = $formfields->$arrayname;

        $scorename = "score_$skill->id";
        $scorevalue = 0;
        if (!isset($a[$scorename])) {
            error_log("threesixty: $scorename is missing from the submitted form fields");
            return get_string('error:formsubmissionerror', 'threesixty');
        } else {
            $scorevalue = $a[$scorename];
        }

        // Save this skill score in the database
        if ($score = $DB->get_record('threesixty_response_skill', array('responseid' => $responseid, 'skillid' => $skill->id))) {
            $newscore = new stdClass();
            $newscore->id = $score->id;
            $newscore->score = $scorevalue;

            if (!$DB->update_record('threesixty_response_skill', $newscore)) {
                error_log("threesixty: could not update score for skill $skill->id");
                return get_string('error:databaseerror', 'threesixty');
            }
        } else {
            //error_log("threesixty: could not find the response_skill record for skill $skill->id");
            //return get_string('error:databaseerror', 'threesixty');
            $newscore = new stdClass();
            $newscore->skillid = $skill->id;
            $newscore->score = $scorevalue;
            $newscore->responseid = $responseid;
            if(!$DB->insert_record('threesixty_response_skill', $newscore)) {
              error_log("threesixty: could not create record for skill $skill->id");
              return get_string('error:databaseerror', 'threesixty');
            }
        }
    }

    return '';
}
