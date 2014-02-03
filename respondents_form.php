<?php

require_once $CFG->dirroot.'/lib/formslib.php';

class mod_threesixty_respondents_form extends moodleform {

    function definition() {
		global $COURSE, $USER;

        $mform =& $this->_form;
        $typelist = $this->_customdata['typelist'];
        $remaininginvitations = $this->_customdata['remaininginvitations'];

        $mform->addElement('hidden', 'a', $this->_customdata['a']);
        $mform->setType('a', PARAM_INT);
        $mform->addElement('hidden', 'userid', $this->_customdata['userid']);
        $mform->setType('userid', PARAM_INT);

        $mform->addElement('header', 'requestrespondent', get_string('requestrespondentheading', 'threesixty'));
        $mform->addElement('html', get_string('requestrespondentexplanation', 'threesixty', $remaininginvitations));

		$peers = array();
		$peers[0] = get_string('externaluser', 'threesixty'); 
		if ($userlist = get_enrolled_users(context_course::instance($COURSE->id))){
			foreach($userlist as $u){
				if ($u->id == $USER->id) continue;
				$peers[$u->id] = "$u->lastname $u->firstname";
			}
		}
        $mform->addElement('select', 'respondentuserid', get_string('respondentuserid', 'threesixty'), $peers);
        $mform->setType('responderuserid', PARAM_INT);

        $mform->addElement('static', 'static', get_string('or', 'threesixty'));

        $mform->addElement('text', 'email', get_string('email'), array('size' => 40));
        $mform->setType('email', PARAM_NOTAGS);
        $mform->addRule('email', get_string('invalidemail'), 'email');

        $mform->addElement('select', 'type', get_string('respondenttype', 'threesixty'), $typelist);
        $mform->setType('type', PARAM_INT);

        $mform->addElement('html', '<br/><br/>');
        $mform->addElement('submit', 'send', get_string('sendemail', 'threesixty'));
    }

    function validation($data, $files) {
    	global $DB;
    	
        $errors = parent::validation($data, $files);
        $analysisid = $this->_customdata['analysisid'];

        $email = strtolower($data['email']);
        if ($DB->get_field('threesixty_respondent', 'id', array('analysisid' => $analysisid, 'email' => $email))) {
            $errors['email'] = get_string('validation:emailnotunique', 'threesixty');
        }

        return $errors;
    }
}
