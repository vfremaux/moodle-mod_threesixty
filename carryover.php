<?php

/**
 * Administration settings for selecting which competencies to carry
 * over to the Training Diary.
 *
 * @author  Francois Marier <francois@catalyst.net.nz>
 * @package mod/threesixty
 */

	require_once '../../config.php';
	require_once 'locallib.php';
	require_once 'carryover_form.php';

	define('MAX_DESCRIPTION', 255); // max number of characters of the description to show in the table

	$id       = optional_param('id', 0, PARAM_INT);  // coursemodule id ID
	$a       = optional_param('a', 0, PARAM_INT);  // threesixty instance ID
	$userid  = optional_param('userid', 0, PARAM_INT);

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

	$user = null;
	if ($userid > 0 and !$user = $DB->get_record('user', array('id' => $userid))) {
	    print_error('error:invaliduserid', 'threesixty');
	}

/// Security 

	$context = context_module::instance($cm->id);
	require_login($course, true, $cm);
	require_capability('mod/threesixty:manage', $context);

///

	$url = $CFG->wwwroot."/mod/threesixty/carryover.php?a=$threesixty->id";

	$mform = null;
    $complist = get_full_competency_list($threesixty->id);

	if (isset($user)) {
	    $returnurl = $CFG->wwwroot."/mod/threesixty/view.php?a=$threesixty->id";
	    $currenturl = "$url&amp;userid=$user->id";

	    if (!$analysis = $DB->get_record('threesixty_analysis', array('activityid' => $threesixty->id, 'userid' => $user->id))) {
	        print_error('error:nodataforuserx', 'threesixty', $returnurl, fullname($user));
	    }

	    $nbcarried = $threesixty->competenciescarried;
	    $mform = new mod_threesity_carryover_form(null, compact('a', 'userid', 'complist', 'nbcarried'));
	    if ($fromform = $mform->get_data()) {
	        if ($mform->is_cancelled()) {
	            redirect($url);
	        }
	        if (save_changes($fromform, $analysis->id)) {
	            redirect($currenturl);
	        }
	        else {
	            redirect($currenturl, get_string('error:cannotsavechanges', 'threesixty', get_string('error:databaseerror', 'threesixty')));
	        }
	    }
	    add_to_log($course->id, 'threesixty', 'carryover', $currenturl, $threesixty->id);
	}

/// Header

	$strthreesixtys = get_string('modulenameplural', 'threesixty');
	$strthreesixty  = get_string('modulename', 'threesixty');

	$PAGE->set_url($url);
	$PAGE->set_title(format_string($threesixty->name));
	$PAGE->set_heading(format_string($threesixty->name));
	$PAGE->navbar->add($strthreesixtys, $CFG->wwwroot."/mod/theesixty/index.php?id=$course->id");
	$PAGE->navbar->add(format_string($threesixty->name));
	$PAGE->set_focuscontrol('');
	$PAGE->set_cacheable(true);
	$PAGE->set_headingmenu(navmenu($course, $cm));

	echo $OUTPUT->header();

/// Main content

	$currenttab = 'edit';
	$section = 'carryover';
	include 'tabs.php';
	if (isset($mform)) {
	    echo threesixty_selected_user_heading($user, $course->id, $url);
	    set_form_data($mform, $analysis->id);
	    $mform->display();
	} else {
	    display_current_user_data($threesixty, $url, $complist);
	}
	echo $OUTPUT->footer($course);

/**
* End of page production
* Starting with local library
*/


function get_full_competency_list($threesixtyid){
  global $CFG, $DB;

    $ret = array(0 => get_string('none'));

    $sql = "
    	SELECT 
    		s.*, 
    		c.name as competency 
    	FROM 
    		{threesixty_skill} s
        JOIN 
        	{threesixty_competency} c ON s.competencyid = c.id
        WHERE 
        	c.activityid = ? ";
    if ($records = $DB->get_records_sql($sql, array($threesixtyid))) {
        foreach ($records as $record) {
            $ret[$record->id] = $record->competency.": ".$record->name;
        }
    }

    return $ret;
}

function set_form_data($mform, $analysisid){
	global $DB;

    if (!$carriedcomps = $DB->get_records('threesixty_carried_comp', array('analysisid' => $analysisid), '', 'id, competencyid')) {
        return; // no existing data
    }

    $toform = array();

    $previousvalues = array();
    $i = 0;
    foreach ($carriedcomps as $carried) {
        if ($i >= $mform->_customdata['nbcarried']) {
            error_log('threesixty: more records in carried_comp than allowed in activity settings');
            break;
        }

        $compid = $carried->competencyid;
        if (!empty($previousvalues[$compid])) {
            $i++;
            continue; // only add competencies once
        }
        $previousvalues[$compid] = true;

        $toform["comp$i"] = $compid;
        $i++;
    }

    $mform->set_data($toform);
}

function save_changes($formfields, $analysisid){
	global $DB;
	
    if (!empty($formfields->nbcarried)) {
         //SCANMSG: transactions may need additional fixing
            $transaction = $DB->start_delegated_transaction();

            // Remove existing ones
            if (!$DB->delete_records('threesixty_carried_comp', array('analysisid' => $analysisid))) {
                error_log("threesixty: could not delete records from carried_comp");
                return false;
            }
            // Add all new selected competencies
            $previousvalues = array();
            for ($i = 0; $i < $formfields->nbcarried; $i++) {
                $fieldname = "comp$i";
                if (empty($formfields->$fieldname)) {
                    continue; // missing from the form data (or set to 'None')
                }
                $compid = (int)$formfields->$fieldname;
                if (!empty($previousvalues[$compid])) {
                    continue; // only add competencies once
                }
                $previousvalues[$compid] = true;
                $record = new stdClass();
                $record->analysisid = $analysisid;
                $record->competencyid = $compid;
                if (!$DB->insert_record('threesixty_carried_comp', $record)) {
                    error_log("threesixty: could not insert new record in carried_comp");
                    return false;
                }
            }
            $transaction->allow_commit();
    }

    return true;
}

function display_current_user_data($threesixty, $url, &$skilllist){
  	global $CFG, $DB, $OUTPUT;

  	$table = new html_table();
  	$table->head = array(get_string('participant', 'threesixty'));
  	$nbcarried = $threesixty->competenciescarried;
  	for ($i=1; $i <= $nbcarried; $i++){
    	$table->head[] = get_string('skill', 'threesixty').' '.$i;
  	}

  	$table->head[] = '';
	$changestr = get_string('change', 'threesixty');

  	$users = threesixty_users($threesixty);
  	if($users){
    	foreach($users as $user){
      		$data = array("<a href=".$CFG->wwwroot."/user/view.php?id={$user->id}&course={$threesixty->course}>".format_string($user->firstname." ".$user->lastname)."</a>");
      		$sql = "
      			SELECT 
      				s.id as carriedskillid
              	FROM 
              		{threesixty_analysis} a
              	JOIN 
              		{threesixty_carried_comp} cc ON a.id = cc.analysisid
              	JOIN 
              		{threesixty_skill} s ON cc.competencyid = s.id
              	WHERE 
              		a.userid = ? and a.activityid = ?
            ";
      		$carriedcomps = $DB->get_records_sql($sql, array($user->id, $threesixty->id));
      		$missingcells = $nbcarried;

      		if($carriedcomps){
        		foreach ($carriedcomps as $comp){
          			$data[] = $skilllist[$comp->carriedskillid];
          			$missingcells--;
        		}
      		}
      		if($missingcells){
        		for($i = 0 ; $i < $missingcells; $i++){
          			$data[] = "&nbsp;";
        		}
      		}
      		$data[] = "<a href=\"$url&amp;userid=$user->id\" title=\"$changestr\" ><img src=\"".$OUTPUT->pix_url('i/edit').'" /></a>';
      		$table->data[] = $data;
    	}
    	echo html_writer::table($table);
  	} else {
    	echo $OUTPUT->notification(get_string('nousers', 'threesixty'));
  	}
}