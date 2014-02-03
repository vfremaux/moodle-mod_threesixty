<?php

require_once $CFG->dirroot.'/lib/formslib.php';

class mod_threesity_score_form extends moodleform {

    function definition() {
		global $DB;

        $mform =& $this->_form;
        $a = $this->_customdata['a'];
        $threesixty = $DB->get_record('threesixty', array('id' => $a));
        $competency = $this->_customdata['competency'];
        $page = $this->_customdata['page'];
        $nbpages = $this->_customdata['nbpages'];

        $mform->addElement('hidden', 'a', $this->_customdata['a']);
        $mform->setType('a', PARAM_INT);

        $mform->addElement('hidden', 'code', $this->_customdata['code']);
        $mform->setType('code', PARAM_ALPHANUM);

        $mform->addElement('hidden', 'page', $this->_customdata['page']);
        $mform->setType('page', PARAM_INT);
        
        $mform->addElement('hidden', 'userid', $this->_customdata['userid']);
        $mform->setType('userid', PARAM_INT);

        $mform->addElement('hidden', 'typeid', $this->_customdata['typeid']);
        $mform->setType('typeid', PARAM_INT);

        $mform->addElement('header', 'competency', format_string($competency->name));
        $mform->addElement('html', '<div class="competencydescription">'.format_text($competency->description).'</div>');
		if ($threesixty->skillgrade < 0){
			// This is a non numeric scale, based on a custom scale
			$mform->addElement('html', '<div class="completionlegend"><p class="legendheading">'.get_string('legend:heading', 'threesixty').'</p><ul>');
			$scale = $DB->get_record('scale', array('id' => -$threesixty->skillgrade));
			$scaleitems = explode(',', $scale->scale);
			$maxscale = count($scaleitems);
			$i = 0;
			foreach($scaleitems as $sci){
				$mform->addElement('html', "<li><b>$i</b> : $sci </li>");
				$i++;
			}
			$mform->addElement('html', '</ul></div>');
		} else {
			// This is a numeric scale
			$maxscale = $threesixty->skillgrade + 1;
		}

        if ($competency->skills and count($competency->skills) > 0) {
            $mform->addElement('html','<br />');
            $mform->addElement('html','<div class="compheader">');


			$mform->addElement('html', '<table class="generaltable" width="100%"><tr>');
			$mform->addElement('html', '<th class="header">'.get_string('skills', 'threesixty').'</th><th class="header">'.get_string('notapplicable', 'threesixty').'</th>');
            for($i = 1 ; $i < $maxscale ; $i++){
				$mform->addElement('html', '<th class="header"><b>'.$i.'</b></th>');
            }
			$mform->addElement('html', '</tr>');
            foreach ($competency->skills as $skill) {
            	$skillname = format_string($skill->name);
                if(strlen($skill->description) > 0) {
                	$skillname .= " - ".format_string($skill->description);
                }
				$mform->addElement('html', "<tr><td class=\"cell c0\">$skillname</td>");
                $elementname = "score_{$skill->id}";
                $radioarray = array();
                $attribs = '';
				if ($competency->locked) $attribs = 'disabled="disabled"';
                for($i = 0 ; $i < $maxscale ; $i++){
					$mform->addElement('html', "<td class=\"cell\">");
					$mform->addElement('radio', $elementname, '', '', $i, $attribs);
					$mform->addElement('html', "</td>");
                }
				$mform->addElement('html', "</tr>");
            }
            $mform->addElement('html', '</table>');

        } else {
            $mform->addElement('html', get_string('noskills', 'threesixty'));
        }

        //if (1 == $competency->showfeedback and empty($this->_customdata['code'])) {
        // Kat - allowed externals to leave feedback
        if (1 == $competency->showfeedback) {
            $mform->addElement('textarea', 'feedback', get_string('feedback'), array('cols'=>'53', 'rows'=>'8'));
            if ($competency->locked) {
                $mform->hardFreeze('feedback');
            }
        }

        // Paging buttons
        $buttonarray = array();
        if ($page > 1) {
            $buttonarray[] = &$mform->createElement('submit', 'previous', get_string('previous'));
        } else {
            $buttonarray[] = &$mform->createElement('submit', 'previous', get_string('previous'), array('disabled'=>true));
        }
        if ($page < $nbpages) {
            $buttonarray[] = &$mform->createElement('submit', 'next', get_string('next'));
        } else {
            $buttonlabel = get_string('finishbutton', 'threesixty');
            if ($competency->locked) {
                $buttonlabel = get_string('closebutton', 'threesixty');
            }
            $buttonarray[] = &$mform->createElement('submit', 'finish', $buttonlabel);
        }

        $a = new stdClass();
        $a->page = $page;
        $a->nbpages = $nbpages;

        $mform->addGroup($buttonarray, 'buttonarray', '', ' ' . get_string('xofy', 'threesixty', $a) . ' ');
        $mform->closeHeaderBefore('buttonarray');
    }
}
