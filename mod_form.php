<?php

require_once($CFG->dirroot.'/course/moodleform_mod.php');

class mod_threesixty_mod_form extends moodleform_mod {

    function definition() {
        global $COURSE;
        $mform =& $this->_form;

        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('name'), array('size'=>'64'));
        $mform->setType('name', PARAM_CLEANHTML);
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        $this->add_intro_editor(true, get_string('intro', 'threesixty'));

        $competenciescarried = array();
        for ($i = 1; $i <= 10; $i += 1) {
            $competenciescarried[$i] = $i;
        }
        $mform->addElement('select', 'competenciescarried', get_string('competenciescarried', 'threesixty'), $competenciescarried);
        $mform->setDefault('competenciescarried', 3);
        $mform->addHelpButton('competenciescarried', 'competenciescarried', 'threesixty');

        $requiredrespondents = array();
        for ($i = 0; $i <= 20; $i += 1) {
            $requiredrespondents[$i] = $i;
        }
        $mform->addElement('select', 'requiredrespondents', get_string('requiredrespondents', 'threesixty'), $requiredrespondents);
        $mform->setDefault('requiredrespondents', 10);
        $mform->addHelpButton('requiredrespondents', 'requiredrespondents', 'threesixty');

        $mform->addElement('textarea', 'selftypes', get_string('selftypes', 'threesixty'));

        $mform->addElement('textarea', 'respondenttypes', get_string('respondenttypes', 'threesixty'));

        $mform->addElement('modgrade', 'skillgrade', get_string('skillgrade', 'threesixty'));
        $mform->setDefault('skillgrade', 4);

        $mform->addElement('modgrade', 'grade', get_string('grade'));
        $mform->setDefault('grade', 100);

        $features = new stdClass;
        $features->groups = false;
        $features->groupings = false;
        $features->groupmembersonly = false;
        $features->outcomes = true;
        $features->gradecat = true;
        $features->idnumber = true;
        $this->standard_coursemodule_elements($features);
        $this->add_action_buttons();

    }
    function set_data($defaults){
    	global $CFG;
    	if (empty($defaults->selftypes)){
    		$defaults->selftypes = $CFG->threesixty_selftypes;
    	}

    	if (empty($defaults->respondenttypes)){
    		$defaults->respondenttypes = $CFG->threesixty_respondenttypes;
    	}
    	parent::set_data($defaults);
    }
}
