<?php

/** End of screen : Starting local lib **/

function print_participants_listing($activity, $baseurl){
	global $CFG;
	
    if ($users = threesixty_users($activity)) {
        $table = new html_table();
        $table->head = array(get_string('name'), get_string('numberrespondents', 'threesixty'));
		$table->head[] = get_string('self:responseoptions', 'threesixty');
        $table->data = array();
        $viewstr = get_string('view', 'threesixty');
		foreach ($users as $user) {
			$name = format_string(fullname($user));
			$userurl = "<a href=".$CFG->wwwroot."/user/view.php?id={$user->id}&course={$activity->course}>".$name."</a>";
            $selectlink = "<a href=\"$baseurl&amp;userid=$user->id\">$viewstr</a>";

			$numrespondents = count_respondents($user->id, $activity->id);
            $table->data[] = array($userurl, $numrespondents, $selectlink);
		}
		return get_string('selectuser', 'threesixty').html_writer::table($table, true);
	} else {
		return get_string('nousersfound', 'threesixty');
	}
}

function generate_uniquehash($email){
    $timestamp = time();
    $salt = mt_rand();
    return sha1("$salt $email $timestamp");
}

function send_email($recipientemail, $messageid, $extrainfo){
    // Fake user object necessary for email_to_user()
    $user = new stdClass();
    $user->id = 0; // required for bounce handling and get_user_preferences()
    $user->email = $recipientemail;

    $a = new stdClass();
    $a->url = @$extrainfo['url'];
    $a->userfullname = @$extrainfo['userfullname'];

    $from = $extrainfo['userfullname'];
    $subject = get_string("email:{$messageid}subject", 'threesixty', $a);
    $messagetext = get_string("email:{$messageid}body", 'threesixty', $a);

	return true;
    return email_to_user($user, $from, $subject, $messagetext);
}

function request_respondent($formfields, $activity, $analysisid, $senderfullname){
	global $USER, $CFG, $DB;

	if ($formfields->respondentuserid){
		include_once('mailtemplatelib.php');
		include_once($CFG->dirroot.'/message/lib.php');
		$respondentuser = $DB->get_record('user', array('id' => $formfields->respondentuserid));
	    $respondent = new StdClass;
		$respondent->userid = $USER->id;
		$respondent->activityid = $activity->id;
	    $respondent->analysisid = $analysisid;
	    $respondent->respondentuserid = $respondentuser->id;
	    $respondent->email = $respondentuser->email;
	    $respondent->type = (int)$formfields->type;
	    $respondent->uniquehash = generate_uniquehash($respondentuser->email);
		$responsehashedurl = RESPONSE_BASEURL . $respondent->uniquehash;
	    $REQ['requirant'] = fullname($USER);
	    $REQ['requirantemail'] = $USER->email;
	    $REQ['requiredname'] = $respondentuser->firstname . ' ' .$respondentuser->lastname;
	    $REQ['responsehashedurl'] = $responsehashedurl;
	    $message = threesixty_compile_mail_template('respondentrequest', $REQ, 'threesixty');
        message_post_message($USER, $respondentuser, $message, FORMAT_MOODLE, 'direct');
	} else {
	    $respondent = new stdClass();
		$respondent->activityid = $activity->id;
	    $respondent->analysisid = $analysisid;
		$respondent->userid = $USER->id;
	    $respondent->email = strtolower($formfields->email);
	    $respondent->type = (int)$formfields->type;
	    $respondent->uniquehash = generate_uniquehash($formfields->email);
		$responsehasehdurl = RESPONSE_BASEURL . $respondent->uniquehash;
	}

    $extrainfo = array($responsehashedurl,'userfullname' => $senderfullname);
    if (!send_email($respondent->email, 'request', $extrainfo)) {
        error_log("threesixty: could not send request email to $respondent->email");
        return false;
    }

    if (!$respondent->id = $DB->insert_record('threesixty_respondent', $respondent)) {
        error_log("threesixty: cannot insert respondent email=$respondent->email");
        return false;
    }

    return true;
}

function send_reminder($respondentid, $senderfullname){
	global $DB;
	
    if (!$respondent = $DB->get_record('threesixty_respondent', array('id' => $respondentid))) {
        error_log("threesixty: cannot find respondent id=$respondentid");
        return false;
    }

    $extrainfo = array('url' => RESPONSE_BASEURL. $respondent->uniquehash,
                       'userfullname' => $senderfullname);
    if (!send_email($respondent->email, 'reminder', $extrainfo)) {
        error_log("threesixty: could not send reminder email to $respondent->email");
        return false;
    }

    return true;
}

function print_respondent_table($activityid, $analysisid, $userid, $canremind=false, $candelete=false){
	global $CFG, $typelist, $USER, $OUTPUT;

  	$respondents = threesixty_get_external_respondents($analysisid);
  	if ($respondents) {
    	$table = new html_table();
    	$table->head = array(get_string('email'), get_string('respondenttype', 'threesixty'), get_string('completiondate', 'threesixty'));
	    if ($candelete or $canremind) {
	        $table->head[] = '&nbsp;';
	    }
	    $table->data = array();
	    foreach ($respondents as $respondent) {
	        $data = array();
	        $data[] = format_string($respondent->email);
	        if (empty($typelist[$respondent->type])) {
	            $data[] = get_string('unknown');
	        } else {
	            $data[] = $typelist[$respondent->type];
	        }
	        if (empty($respondent->timecompleted)) {
	            $data[] = get_string('none');
	        } else {
	            $data[] = userdate($respondent->timecompleted, get_string('strftimedate'));
	        }
	        // Action buttons
	        $buttons = '';
	        if ($canremind and empty($respondent->timecompleted)) {
	            $link = 'respondents.php';
	            $options = array('a' => $activityid, 'remind' => $respondent->id,'userid' => $userid, 'sesskey' => $USER->sesskey);
	            $buttons .= $OUTPUT->single_button(new moodle_url($link, $options), get_string('remindbutton', 'threesixty'), 'post');
	        }
	        if ($candelete) {
	            $link = 'respondents.php';
	            $options = array('a' => $activityid, 'delete' => $respondent->id,'userid' => $userid, 'sesskey' => $USER->sesskey);
	            $buttons .= $OUTPUT->single_button(new moodle_url($link, $options), get_string('delete'), 'post');
	        }
	        if (!empty($buttons)) {
	            $data[] = $buttons;
	        }

        	$table->data[] = $data;
      	}
      	echo html_writer::table($table);
    } else {
      echo $OUTPUT->box_start();
      echo get_string('norespondents', 'threesixty');
      echo $OUTPUT->box_end();
    }
}

function threesixty_get_external_respondents($analysisid){
	global $CFG, $DB;

  	$sql = "
  		SELECT 
  			rt.id, 
  			rt.email, 
  			rt.type, 
  			re.timecompleted 
  		FROM 
  			{threesixty_respondent} rt
   		LEFT OUTER JOIN 
   			{threesixty_response} re 
   		ON 
   			re.respondentid = rt.id
		WHERE 
			rt.analysisid = ? AND 
			rt.uniquehash IS NOT NULL
		ORDER BY 
			rt.email
	";

  	$respondents = $DB->get_records_sql($sql, array($analysisid));
  	return $respondents;
}

function count_respondents($userid, $activityid){
	global $CFG, $DB;

	$sql = "
		SELECT 
			COUNT(1) 
		FROM 
			{threesixty_respondent} r
		JOIN 
			{threesixty_analysis} a 
		ON 
			r.analysisid = a.id
		WHERE 
			a.userid = ? AND 
			a.activityid = ? AND 
			r.uniquehash IS NOT NULL
	";
	return $DB->count_records_sql($sql, array($userid, $activityid));
}