<?php

/**
 * Administration settings for a threesixty activity
 *
 * @author  Francois Marier <francois@catalyst.net.nz>
 * @package mod/threesixty
 */

	require_once '../../config.php';
	require_once 'locallib.php';

	define('MAX_DESCRIPTION', 255); // max number of characters of the description to show in the table

	$strmoveup = get_string('moveup');
	$strmovedown = get_string('movedown');

	$id = optional_param('id', 0, PARAM_INT);  // threesixty instance ID
	$a = optional_param('a', 0, PARAM_INT);  // threesixty instance ID
	$move = optional_param('move', 0, PARAM_INT); //Reordering competencies.

	$url = $CFG->wwwroot.'/mod/threesixty/edit.php?id='.$id;

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
	require_capability('mod/threesixty:manage', $context);

	add_to_log($course->id, 'threesixty', 'admin', "edit.php?a=$threesixty->id", $threesixty->id);

	if ($move){
	  	$c = optional_param('c', 0, PARAM_INT); //The competency id that we're needing to move.
	  	if(!$competency = $DB->get_record('threesixty_competency', array('id' => $c, 'activityid' => $threesixty->id))){
	    	print_error('error:badcompetency', 'threesixty');
	  	}
	  	move_competency($competency, $move);
	}

/// Header

	$strthreesixtys = get_string('modulenameplural', 'threesixty');
	$strthreesixty  = get_string('modulename', 'threesixty');

	$PAGE->set_url($url);
	$PAGE->set_title(format_string($threesixty->name));
	$PAGE->set_heading(format_string($threesixty->name));
	$PAGE->set_focuscontrol('');
	$PAGE->set_cacheable(true);
	$PAGE->set_headingmenu(navmenu($course, $cm));

	echo $OUTPUT->header();

// Main content

	$currenttab = 'edit';
	$section = 'competencies';

	include 'tabs.php';

	//print '<h2>'.get_string('competenciesheading', 'threesixty').'</h2>';

	$competencies = threesixty_get_competency_listing($threesixty->id);

	if (count($competencies) > 0) {
		
		$namestr = get_string('name');
		$descriptionstr = get_string('description');
		$skillsstr = get_string('skills', 'threesixty');
		$feedbackstr = get_string('feedback', 'threesixty');
	
		$table = new html_table();
		$table->width = '100%';
	    $table->head = array("<b>$namestr</b>", "<b>$descriptionstr</b>", "<b>$skillsstr</b>", "<b>$feedbackstr</b<", '&nbsp;');
		$table->size = array('5%', '25%', '25%', '5%', '5%');
		$table->align = array('left', 'left', 'left', 'center', 'right');
	
	    $numCompetencies = count($competencies);
	    $deletestr = get_string('delete', 'threesixty');
	    $editstr = get_string('edit', 'threesixty');
	    for ($i = 0; $i < $numCompetencies ; $i++) {
	        $competency = array_shift($competencies);
	        $links = '<a class="icon" href="editcompetency.php?a='.$threesixty->id.'&amp;c='.$competency->id."\" title=\"$editstr\"><img src=\"".$OUTPUT->pix_url('t/edit').'"</a>';
	        $links .= ' <a class="icon" href="deletecompetency.php?a='.$threesixty->id.'&amp;c='.$competency->id."\" title=\"$deletestr\"><img src=\"".$OUTPUT->pix_url('t/delete').'" /></a>';
	        if ($i != 0){
	          $links .= ' <a class="icon" href="edit.php?a='.$threesixty->id.'&amp;c='.$competency->id.'&amp;move=-1" title="'.$strmoveup.'">
	                  <img src="'.$OUTPUT->pix_url('/t/up').'" alt="'.$strmoveup.'" /></a>';
	        }
	        if ($i < $numCompetencies - 1){
	          $links .= ' <a class="icon" href="edit.php?a='.$threesixty->id.'&amp;c='.$competency->id.'&amp;move=1" title="'.$strmovedown.'">
	                  <img src="'.$OUTPUT->pix_url('/t/down').'" alt="'.$strmovedown.'" /></a>';
	        }
	        // Shorten the description field
	        $shortdescription = substr($competency->description, 0, MAX_DESCRIPTION);
	        if (strlen($shortdescription) < strlen($competency->description)) {
	            $shortdescription .= '...';
	        }
	
	        $table->data[] = array(format_string($competency->name), format_text($shortdescription),
	                               format_string($competency->skills),
	                               $competency->showfeedback ? get_string('yes') : get_string('no') , $links);
	    }
	
	    echo html_writer::table($table);
	} else {
	     print_string('nocompetencies', 'threesixty');
	}
	
	echo '<p><a href="editcompetency.php?a='.$threesixty->id.'&amp;c=0">'.get_string('addnewcompetency', 'threesixty').'</a></p>';
	
	echo $OUTPUT->footer($course);

// local function library

function move_competency($competency, $moveTo){
	global $DB;
	
  	$currentlocation = $competency->sortorder;
  	$newlocation = $currentlocation + $moveTo;

  	$swapcomp = $DB->get_record('threesixty_competency', array('activityid' => $competency->activityid, 'sortorder' => $newlocation));
  	if($swapcomp){
    	$swapcomp->sortorder = $currentlocation;
    	$DB->update_record('threesixty_competency', $swapcomp);
  	}

  	$competency->sortorder = $newlocation;
  	$DB->update_record('threesixty_competency', $competency);
}