<?php
/* 
 * Shows the students' responses to the different profile types required.
 *
 * @author Eleanor Martin <eleanor.martin@kineo.com>
 * @package mod/threesixty
 */

  	require_once '../../config.php';
 	require_once 'locallib.php';

  	$id      = optional_param('id', 0, PARAM_INT);  // coursemodule instance ID
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

/// Security

	$context = context_module::instance($cm->id);

	require_login($course, true, $cm);

	$user = null;

	if ($userid > 0 and !$user = $DB->get_record('user', array('id' => $userid))) {
		print_error('error:invaliduserid', 'threesixty');
	}

	$url = $CFG->wwwroot."/mod/threesixty/profiles.php?a=$threesixty->id";

	$PAGE->set_url($url);
	$PAGE->set_title(format_string($threesixty->name));
	$PAGE->set_heading(format_string($threesixty->name));
	$PAGE->navbar->add($strthreesixtys, $CFG->wwwroot."/mod/theesixty/index.php?id=$course->id");
	$PAGE->navbar->add(format_string($threesixty->name));
	$PAGE->set_focuscontrol('');
	$PAGE->set_cacheable(true);
	$PAGE->set_headingmenu(navmenu($course, $cm));

	echo $OUTPUT->header();

// Main content

	$currenttab = 'activity';
	$section = null;
	include 'tabs.php';
	threesixty_self_profile_options($course->id, $url, $threesixty, $context);
	echo $OUTPUT->footer($course);

/**
* End of page production
* Starting with local library
*/
function threesixty_self_profile_options($courseid, $url, $threesixty, $context){
  	global $CFG, $USER;

  	$view_all_users = has_capability('mod/threesixty:viewreports', $context);
  	$canedit = has_capability('mod/threesixty:edit', $context);

  	if ($view_all_users){
    	//$users = threesixty_users($threesixty);
    	$users = threesixty_get_possible_participants($context);
  	} else {
    	$users = array($USER);
  	}

  	$selfresponses = explode("\n", $threesixty->selftypes);

  	if (!empty($selfresponses)){
    	$table = new html_table();
    	$table->head = array();
    	if ($view_all_users){
      		$table->head[] = get_string('user');
    	}
    	$table->head[] = get_string('self:responsetype', 'threesixty');
    	$table->head[] = get_string('self:responsecompleted', 'threesixty');
    	$table->head[] = get_string('self:responseoptions', 'threesixty');
	    if ($users){
	    	foreach($users as $user){
	      		$data = array();
	      		if ($view_all_users){
	        		$data[] = "<a href=".$CFG->wwwroot."/user/view.php?id={$user->id}&course={$threesixty->course}>".format_string($user->firstname." ".$user->lastname)."</a>";
	      		}
	      		$responsenumber = 0; //This provides the type id of the response. 
	      		foreach ($selfresponses as $responsetype){
	        		if($responsenumber > 0){
	          			$data = array();
	          			if ($view_all_users){
	            			$data[] = "&nbsp;";
	          			}
	        		}
		        	$data[] = $responsetype;
		        	$timecompleted = get_completion_time($threesixty->id, $user->id, $responsenumber, true);
		        	if ($timecompleted > 0){
		          		$canreallyedit = $canedit;
		          		$timeoutput = userdate($timecompleted);
		          		$firsttime = false;
		        	} else {
		          		$canreallyedit = false;
		          		$firsttime = true;
		          		$timeoutput = "<span class=\"incomplete\">".get_string('self:incomplete', 'threesixty')."</span>";
		        	}
		        	$data[] = $timeoutput;
		        	$data[] = threesixty_get_options($threesixty->id, $user->id, $responsenumber, $view_all_users, $canreallyedit, $firsttime);
		        	$responsenumber += 1;
		        	$table->data[] = $data;
		        }
	      	}
	    }
    	echo html_writer::table($table);
  	}
}

function get_completion_time($threesixtyid, $userid, $responsetype, $self=false){
	global $CFG, $DB, $OUTPUT;

	$sql = "
		SELECT 
			MAX(r.timecompleted) as timecompleted
		FROM 
			{threesixty_analysis} a
		JOIN 
			{threesixty_respondent} rp 
		ON 
			a.id = rp.analysisid
		JOIN 
			{threesixty_response} r 
		ON 
			rp.id = r.respondentid
		WHERE 
			a.userid = ? AND 
			a.activityid = ? AND 
			rp.type = ?
	";

	if ($self){
		$sql .= " AND rp.uniquehash IS NULL";
	} else {
		$sql .= " AND rp.uniquehash IS NOT NULL";
	}
	$times = $DB->get_records_sql($sql, array($userid, $threesixtyid, $responsetype));

	if($times){
		if(count($times) > 1){
		  	echo $OUTPUT->notification(get_string('error:time', 'threesixty'));
		} else {
			$time = array_pop($times);
			return $time->timecompleted;
		}
	}
}

/**
*
*
*/
function threesixty_get_options($threesixtyid, $userid, $typeid, $view_all_users, $canedit, $firsttime = false){
  	global $CFG, $USER;

	$scoreurl = $CFG->wwwroot."/mod/threesixty/score.php?a=".$threesixtyid;
	if($view_all_users){
		$scoreurl .= "&userid=".$userid;
	}
	$scoreurl .= "&typeid=".$typeid;
	if ($USER->id == $userid){
		if ($firsttime){
			$viewstr = get_string('makeself', 'threesixty');
		} else {
			$viewstr = get_string('viewself', 'threesixty');
		}
	} else {
		$viewstr = get_string('viewentries', 'threesixty');
	}
	$output = "<a href='".$scoreurl."'>$viewstr</a>";
	if ($canedit){
		$amendstr = ($USER->id == $userid) ? get_string('amendme', 'threesixty') : get_string('amend', 'threesixty') ;
		$amendurl = $CFG->wwwroot."/mod/threesixty/amend.php?a=".$threesixtyid."&typeid=".$typeid."&userid=".$userid;
		$output .= " | <a href='".$amendurl."'>$amendstr</a>";
	}
	return $output;
}
