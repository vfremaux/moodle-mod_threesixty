<?php

/**
 * Table and Spiderweb reports
 *
 * @author  Francois Marier <francois@catalyst.net.nz>
 * @package mod/threesixty
 */

	require_once '../../config.php';
	require_once 'locallib.php';
	require_once 'report_form.php';
	require_once 'reportlib.php';

	define('AVERAGE_PRECISION', 1); // number of decimal places when displaying averages
	define("SPIDERWEB_IMPL", 'GOOGLE'); // whether to implement the spiderweb using Kineo's method (as opposed to the company that was originally outsourced to)

	$id     = optional_param('id', 0, PARAM_INT);  // coursemodule ID
	$a      = optional_param('a', 0, PARAM_INT);  // threesixty instance ID
	$type   = optional_param('type', 'table', PARAM_ALPHA); // report type
	$userid = optional_param('userid', 0, PARAM_INT); // user's data to examine
	$basetype = optional_param('base', 'self0', PARAM_ALPHANUM); // Score to do gap analysis from.

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

	$context = context_module::instance($cm->id);
	require_login($course, true, $cm);
	if (!has_capability('mod/threesixty:viewreports', $context)) {
	    require_capability('mod/threesixty:viewownreports', $context);
	    $userid = $USER->id; // force same user
	}

	$user = null;
	if ($userid > 0 and !$user = $DB->get_record('user', array('id' => $userid))) {
	    print_error('error:invaliduserid');
	}

	$url = $CFG->wwwroot."/mod/threesixty/report.php?a=$threesixty->id&amp;type=$type";
	$mform = null;
	$filters = array();

	if (isset($user)) {
	    $currenturl = "$url&amp;userid=$user->id";
	    $selfresponsetypes = explode("\n", $threesixty->selftypes);
	    $respondenttypes = explode("\n", $threesixty->respondenttypes);
	    if (!empty($selfresponsetypes)) {
	      foreach ($selfresponsetypes as $key => $value) {
	        $v = trim($value);
	        if (!empty($v)){
	          $filters["self$key"] = $v;
	        }
	      }
	    }
	    if (!empty($respondenttypes)) {
	        foreach ($respondenttypes as $key => $value) {
	            $v = trim($value);
	            if (!empty($v)) {
	                $filters["type$key"] = $v;
	            }
	        }
	    }
	    $filters['average'] = get_string('filter:average', 'threesixty');
	    $mform = new mod_threesity_report_form(null, compact('a', 'type', 'userid', 'filters'));
	    // Apply the filters
	    if ($fromform = $mform->get_data()) {
	        foreach ($filters as $code => $name) {
	            if (empty($fromform->checkarray[$code])) {
	                unset($filters[$code]); // 'code' is not checked, remove it
	            }
	        }
	    }
	    add_to_log($course->id, 'threesixty', 'report', $currenturl, $threesixty->id);
	}

/// Header

	$strthreesixtys = get_string('modulenameplural', 'threesixty');
	$strthreesixty  = get_string('modulename', 'threesixty');

/// Start outputing screen here

	$PAGE->set_url($url);
	$PAGE->set_title(format_string($threesixty->name));
	$PAGE->set_heading(format_string($threesixty->name));
	$PAGE->navbar->add($strthreesixtys, $CFG->wwwroot."/mod/theesixty/index.php?id=$course->id");
	$PAGE->navbar->add(format_string($threesixty->name));
	$PAGE->set_focuscontrol('');
	$PAGE->set_cacheable(true);
	$PAGE->set_headingmenu(navmenu($course, $cm));

	echo $OUTPUT->header();

	if (SPIDERWEB_IMPL == 'GOOGLE') {
		include 'googleimagechartlib.php';
		google_imagechart_preload();
	}

/// Main content

	$currenttab = 'reports';
	include 'tabs.php';
	if (isset($mform)) {
	    if (!$analysis = $DB->get_record('threesixty_analysis', array('activityid' => $threesixty->id, 'userid' => $user->id))) {
	        echo $OUTPUT->notification(get_string('error:nodataforuserx', 'threesixty', fullname($user)));
			$returnurl = "profiles.php?a=$threesixty->id";
	        echo $OUTPUT->continue_button($returnurl);
	        echo $OUTPUT->footer($course);
	        die;
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

		if ($remaininginvitations > 0) {
			echo "<br />";
			echo $OUTPUT->notification(get_string("respondentsremaining", "threesixty"), "$CFG->wwwroot/mod/threesixty/respondents.php?a=$threesixty->id");
		}
	    print threesixty_selected_user_heading($user, $course->id, $url, has_capability('mod/threesixty:viewreports', $context));

/// Display filters

	    $mform->display();
		// Display scores
	    if ('table' == $type) {
	        $scores = get_scores($analysis->id, $filters, false);
	        $skillnames = threesixty_get_skill_names($threesixty->id);
	        $feedback = threesixty_get_feedback($analysis->id);
	        echo "<div class='scoretables'>";
	        print_score_table($skillnames, $scores, $feedback, $url.'&userid='.$user->id, $basetype);
	        echo "</div>";
	    } elseif ('spiderweb' == $type) {
	    	if (SPIDERWEB_IMPL == 'KINEO') {
		    	print_spiderweb_kineo($analysis->id, $threesixty->id, $filters);
	    	} elseif (SPIDERWEB_IMPL == 'GOOGLE') {
	    		$width = 500;
	    		$height = 500;
	    		$instanceid = google_imagechart_html();
		        $scores = get_scores($analysis->id, $filters, true);
		        $competencynames = $DB->get_records('threesixty_competency', array('activityid' => $threesixty->id), 'name', 'id, name');
	    		// google_imagechart_js($datatable, $instanceid, $width, $height);
	    	} else {
		        $scores = get_scores($analysis->id, $filters, true);
		        $competencynames = $DB->get_records('threesixty_competency', array('activityid' => $threesixty->id), 'name', 'id, name');
		        print_spiderweb($threesixty, $competencynames, $scores);
	    	}
	    } else {
	        print_error('error:invalidreporttype', 'threesixty', "view.php?a=$threesixty->id", $type);
	    }
	} else {
	    print threesixty_user_listing($threesixty, $url);
	}
	echo $OUTPUT->footer($course);
