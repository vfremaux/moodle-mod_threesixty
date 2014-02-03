<?php

/**
 * Allows a student (or an external person using a code) to assess
 * skills accross competencies.
 *
 * @author  Francois Marier <francois@catalyst.net.nz>
 * @package mod/threesixty
 */

	require_once '../../config.php';
	require_once 'locallib.php';
	require_once 'score_form.php';

	$id    = optional_param('id', 0, PARAM_INT);  // coursemodule ID
	$a    = optional_param('a', 0, PARAM_INT);  // threesixty instance ID
	$code = optional_param('code', '', PARAM_ALPHANUM); // unique hash for eternal users
	$internalbypass = optional_param('internal', '', PARAM_BOOL); // boolean
	$page = optional_param('page', 0, PARAM_INT); // page number
	$typeid = optional_param('typeid', 0, PARAM_INT); //type of response

	$respondent = null;
	$analysis = null;
	$activity = null;
	$user = null;
	$userid = 0;

	$externalrespondent = !empty($code);
	if ($externalrespondent) {
	    // External respondent
	    if (!$respondent = $DB->get_record('threesixty_respondent', array('uniquehash' => $code))) {
	        error_log("threesixty: Invalid response hash from {$_SERVER['REMOTE_ADDR']}");
	        print_error('error:invalidcode', 'threesixty');
	    }
	    if (!$analysis = $DB->get_record('threesixty_analysis', array('id' => $respondent->analysisid))) {
	        print_error('invalidthreesixtyid', 'threesixty');
	    }
	    if (!$threesixty = $DB->get_record('threesixty', array('id' => $analysis->activityid))) {
	        print_error('invalidmoduleid');
	    }
	    if (!$user = $DB->get_record('user', array('id' => $analysis->userid))) {
	        print_error('error:inveliduserid', 'threesixty');
	    }
		if (!$course = $DB->get_record('course', array('id' => $activity->course))) {
		    print_error('coursemisconf');
		}
		if (!$cm = get_coursemodule_from_instance('threesixty', $activity->id, $course->id)) {
		    print_error('invalidcoursemodule');
		}

	    $a = $threesixty->id; // needed to pass to score_form

	} elseif ($id) {
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
            print_error('invalidcoursemodule');
        }
        // move require_course_login here to use forced language for course
        // fix for MDL-6926
        require_course_login($course, true, $cm);
        $strthreesixtys = get_string("modulenameplural", 'threesixty');
        $strthreesixty = get_string("modulename", 'threesixty');
        
        // we are answering for ourself
        $user = $USER;
        
	} else {
	    // We need either $a or $code to be defined
	    print_error('missingparameter');
	}

	if (!$externalrespondent) {
	    // Capability checks only relevant to logged-in users
	    $context = context_module::instance($cm->id);
	    require_login($course, true, $cm);
	    require_capability('mod/threesixty:view', $context);
	    if ($USER->id == $user->id) {
	        require_capability('mod/threesixty:participate', $context);
	    } else {
	        require_capability('mod/threesixty:viewreports', $context);
	    }
	}
	// Set URLs based on logged-in v. loginless mode
	$cancelurl = '';

	$url = $CFG->wwwroot."/mod/threesixty/score.php";

	if (!$externalrespondent) {
	    $url .= "?a={$threesixty->id}&amp;userid={$user->id}&amp;typeid=$typeid";
	    $cancelurl = "$CFG->wwwroot/course/view.php?id=$COURSE->id";
	} else {
	    $url .= "?code=$code";
	}

	$currenturl = "$url&amp;page=$page";

	if ($page < 1) {
	    $page = threesixty_get_first_incomplete_competency($threesixty->id, $user->id, $respondent);
	}

	$nbpages = null;
	$mform = null;
	$fromform = null;

    $nbpages = $DB->count_records('threesixty_competency', array('activityid' => $threesixty->id));

	if ($competency = get_competency_details($page, $threesixty->id, $user->id, $respondent)) {
		// one competency sheet per page
	    $mform = new mod_threesity_score_form(null, compact('a', 'code', 'competency', 'page', 'nbpages', 'userid', 'typeid'));
	    if ($mform->is_cancelled()){
	        redirect($cancelurl);
	    }
		if ($fromform = $mform->get_data()) {
		    if (!empty($fromform->buttonarray['previous'])) { // Previous button
		        $errormsg = save_changes($fromform, $threesixty->id, $user->id, $competency, false, $respondent);
		        if (!empty($errormsg)) {
		            print_error('error:cannotsavescores', 'threesixty', $currenturl, $errormsg);
		        }
		        $newpage = max(1, $page - 1);

				if (debugging(DEBUG_DEVELOPER)){
					echo $OUTPUT->header();
			        echo $OUTPUT->continue_button("{$url}&amp;page=$newpage");
					echo $OUTPUT->footer();
				} else {
		        	redirect("{$url}&amp;page=$newpage");
		        }
		        die;
		    }
		    elseif (!empty($fromform->buttonarray['next'])) { // Next button
		        $errormsg = save_changes($fromform, $threesixty->id, $user->id, $competency, false, $respondent);
		        if (!empty($errormsg)) {
		            print_error('error:cannotsavescores', 'threesixty', $currenturl, $errormsg);
		        }
		        $newpage = min($nbpages, $page + 1);

				if (debugging(DEBUG_DEVELOPER)){
					echo $OUTPUT->header();
			        echo $OUTPUT->continue_button("{$url}&amp;page=$newpage");
					echo $OUTPUT->footer();
				} else {
		        	redirect("{$url}&amp;page=$newpage");
		        }
		        die;
		    }
		    elseif (!empty($fromform->buttonarray['finish'])) {
		        $errormsg = save_changes($fromform, $threesixty->id, $user->id, $competency, true, $respondent);
		        if (!empty($errormsg)) {
		            print_error('error:cannotsavescores', 'threesixty', $currenturl, $errormsg);
		        }
		        if (!$externalrespondent || $internalbypass) {
		            redirect("view.php?a=$threesixty->id");
		        } else {
		            redirect("thankyou.php?a=$threesixty->id");
		        }
		    } else {
		        print_error('error:unknownbuttonclicked', 'threesixty', $cancelurl);
		    }
		}
	} elseif ($page >= $nbpages) {
	    print_error('error:invalidpagenumber', 'threesixty');
	}

	add_to_log($course->id, 'threesixty', 'score', $currenturl, $threesixty->id);

/// Header

	$PAGE->set_url($currenturl);
	$PAGE->set_title(format_string($threesixty->name));
	$PAGE->set_heading(format_string($threesixty->name));
	$PAGE->set_focuscontrol('');
	$PAGE->set_cacheable(true);
	$PAGE->navbar->add($strthreesixtys, $CFG->wwwroot."/mod/theesixty/index.php?id=$course->id");
	$PAGE->navbar->add(format_string($threesixty->name));
	$PAGE->set_headingmenu(navmenu($course, $cm));

	echo $OUTPUT->header();

/// Main content

	if (!$externalrespondent) {
	    $currenttab = 'activity';
	    $section = null;
	    include 'tabs.php';
	} else {
		$RESPONDENTTYPES = threesixty_get_respondent_types($threesixty);
		$m = new StdClass;
		$m->respondent = format_string($respondent->email);
		$m->role = $RESPONDENTTYPES[$respondent->type];
		$m->by = fullname($user);
	    $message = get_string('respondentwelcome', 'threesixty', $m);
	    if ($competency->locked) {
	        $message .= get_string('thankyoumessage', 'threesixty');
	    } else {
	        $message .= get_string('respondentwarning', 'threesixty', "wrongemail.php?code=$code");
	        $message .= get_string('respondentinstructions', 'threesixty');
	        $message .= "<p>".get_string('respondentindividual', 'threesixty', $user->firstname." ".$user->lastname)."</p>";
	    }
	    echo $OUTPUT->box($message);
	}
	if ($mform) {
	    set_form_data($mform, $competency);
	    $mform->display();
	} else {
	    echo $OUTPUT->box(get_string('nocompetencies', 'threesixty'));
	}
	echo $OUTPUT->footer($course);

/**
* End of page production
* Starting with local library
*/

function get_competency_details($page, $activityid, $userid, $respondent){
    global $CFG, $DB;

    if ($competencies = $DB->get_records('threesixty_competency', array('activityid' => $activityid), 'sortorder', '*', $page - 1, 1)) {
        if (!$record = array_pop($competencies)) {
            return false;
        }

        $respondentclause = 'r.respondentid IS NULL';
        if ($respondent != null) {
            $respondentclause = "r.respondentid = $respondent->id";
        }
        $responsesql = "
        	SELECT 
        		r.id AS responseid, 
        		c.feedback AS competencyfeedback,
				r.timecompleted AS timecompleted
			FROM 
				{threesixty_analysis} a
			LEFT OUTER JOIN 
				{threesixty_response} r 
			ON 
				a.id = r.analysisid
			LEFT OUTER JOIN 
				{threesixty_response_comp} c 
			ON 
				c.responseid = r.id AND 
				c.competencyid = ?
			WHERE 
				a.userid = ? AND 
				a.activityid = ? AND
				$respondentclause
		";

        $response = $DB->get_record_sql($responsesql, array($record->id, $userid, $activityid));

        if ($response and !empty($response->competencyfeedback)) {
            $record->feedback = $response->competencyfeedback;
        }

        $record->locked = false;
        if ($response and !empty($response->timecompleted)) {
            $record->locked = true;
        }

        // Get skill descriptions
        $record->skills = $DB->get_records('threesixty_skill', array('competencyid' => $record->id), 'sortorder', 'id, name, description, 0 AS score');

        if ($record->skills and $response and $response->responseid != null) {
            // Get scores
            $sql = "
            	SELECT 
            		s.id, 
            		r.score
				FROM 
					{threesixty_skill} s
				JOIN 
					{threesixty_response_skill} r 
				ON 
					s.id = r.skillid
				WHERE 
					s.competencyid = ? AND 
					r.responseid = ?
			";

            if ($scores = $DB->get_records_sql($sql, array($record->id, $response->responseid))) {
                foreach ($scores as $s) {
                    $record->skills[$s->id]->score = $s->score;
                }
            }
        }

        return $record;
    }
    return false;
}

function set_form_data($mform, $competency){
    $toform = array();

    if (!empty($competency->feedback)) {
        $toform['feedback'] = $competency->feedback;
    }

    if (!empty($competency->skills) and count($competency->skills) > 0) {
        foreach ($competency->skills as $skill) {
            $toform["radioarray_{$skill->id}[score_{$skill->id}]"] = $skill->score;
        }
    }

    $mform->set_data($toform);
}

function save_changes($formfields, $activityid, $userid, $competency, $finished, $respondent){
    global $CFG, $DB;
    
    // print_object($formfields);
    // print_object($competency);

    if ($competency->locked) {
        // No changes are saved for responses which have been submitted already
        return '';
    }

    if (!$analysis = $DB->get_record('threesixty_analysis', array('activityid' => $activityid, 'userid' => $userid))) {
        $analysis = new stdClass();
        $analysis->activityid = $activityid;
        $analysis->userid = $userid;

        if (!$analysis->id = $DB->insert_record('threesixty_analysis', $analysis)) {
            error_log('threesixty: could not insert new analysis record');
            return get_string('error:databaseerror', 'threesixty');
        }
    }

    $respondentid = null;
    if ($respondent == null) {
      $respondent = new stdClass();
      $respondent->analysisid = $analysis->id;
      $respondent->type = $formfields->typeid;
      if(!$respondent->id = $DB->insert_record('threesixty_respondent', $respondent)){
        error_log('threesixty: could not insert new respondent record');
        return get_string('error:databaseerror', 'threesixty');
      }
    }
    $respondentid = $respondent->id;
    if (!$response = $DB->get_record('threesixty_response', array('analysisid' => $analysis->id, 'respondentid' => $respondentid))) {
        $response = new stdClass();
        $response->analysisid = $analysis->id;
        $response->respondentid = $respondentid;

        if (!$response->id = $DB->insert_record('threesixty_response', $response)) {
            error_log('threesixty: could not insert new response record');
            return get_string('error:databaseerror', 'threesixty');
        }
    }

    if (!empty($competency->skills)) {
        foreach ($competency->skills as $skill) {
        	/*
            $arrayname = "radioarray_$skill->id";
            if (empty($formfields->$arrayname)) {
                error_log("threesixty: $arrayname is missing from the submitted form fields");
                return get_string('error:formsubmissionerror', 'threesixty');
            }
            $a = $formfields->$arrayname;
            */
            $scorename = "score_$skill->id";
            $scorevalue = 0;
            if (empty($formfields->$scorename)) {
                // Choosing "Not set" will clear the existing value
            } else {
                $scorevalue = $formfields->$scorename;
            }

            // Save this skill score in the database
            if ($score = $DB->get_record('threesixty_response_skill', array('responseid' => $response->id, 'skillid' => $skill->id))) {
                $newscore = new stdClass();
                $newscore->id = $score->id;
                $newscore->score = $scorevalue;

                if (!$DB->update_record('threesixty_response_skill', $newscore)) {
                    error_log("threesixty: could not update score for skill $skill->id");
                    return get_string('error:databaseerror', 'threesixty');
                }
            }
            else {
                $score = new stdClass();
                $score->responseid = $response->id;
                $score->skillid = $skill->id;
                $score->score = $scorevalue;

                if (!$score->id = $DB->insert_record('threesixty_response_skill', $score)) {
                    error_log("threesixty: could not insert score for skill $skill->id");
                    return get_string('error:databaseerror', 'threesixty');
                }
            }
        }
    }
    if (isset($formfields->feedback)) {
        // Save this competency score in the database
        if ($comp = $DB->get_record('threesixty_response_comp', array('responseid' => $response->id, 'competencyid' => $competency->id))) {
            $newcomp = new stdClass();
            $newcomp->id = $comp->id;
            $newcomp->feedback = $formfields->feedback;

            if (!$DB->update_record('threesixty_response_comp', $newcomp)) {
                error_log("threesixty: could not update score for competency $competency->id");
                return get_string('error:databaseerror', 'threesixty');
            }
        } else {
            $comp = new stdClass();
            $comp->responseid = $response->id;
            $comp->competencyid = $competency->id;
            $comp->feedback = $formfields->feedback;

            if (!$comp->id = $DB->insert_record('threesixty_response_comp', $comp)) {
                error_log("threesixty: could not insert score for competency $competency->id");
                return get_string('error:databaseerror', 'threesixty');
            }
        }
    }

    if ($finished) {

		$sql = "
			SELECT 
				s.id 
			FROM 
				{threesixty_competency} c
			JOIN 
				{threesixty_skill} s 
			ON 
				s.competencyid = c.id
			WHERE 
				c.activityid = ?
		";
        $skills = $DB->get_records_sql($sql, array($activityid));

        $scores = $DB->get_records('threesixty_response_skill', array('responseid' => $response->id), '', 'skillid,score');

        // Check that all of the scores have been set
        foreach ($skills as $skillid => $skill) {
            if (!isset($scores[$skillid])) {
                // Score is not set 
                return get_string('error:allskillneedascore', 'threesixty');
            }
        }

        $newresponse = new stdClass();
        $newresponse->id = $response->id;
        $newresponse->timecompleted = time();
        if (!$DB->update_record('threesixty_response', $newresponse)) {
            error_log('threesixty: could not update the timecompleted field of the response');
            return get_string('error:databaseerror', 'threesixty');
        }
    }

    return '';
}

