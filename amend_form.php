<?php

require_once $CFG->dirroot.'/lib/formslib.php';

class mod_threesixty_amend_form extends moodleform {

    function definition() {
		global $DB;
		
        $mform =& $this->_form;
        $skills = $this->_customdata['skillnames'];
        $a = $this->_customdata['a'];
        $threesixty = $DB->get_record('threesixty', array('id' => $a));

        $mform->addElement('hidden', 'a', $this->_customdata['a']);
        $mform->setType('a', PARAM_INT);
        $mform->addElement('hidden', 'userid', $this->_customdata['userid']);
        $mform->setType('userid', PARAM_INT);
        $mform->addElement('hidden', 'typeid', $this->_customdata['typeid']);
        $mform->setType('typeid', PARAM_INT);

        $competency = new StdClass;
        $competency->skills = false;

		/*
        if ($skills and count($skills) > 0) {
            $curcompetency = 0;
            foreach ($skills as $skill) {
            	
                if ($curcompetency != $skill->competencyid) {
                    $mform->addElement('html','<br /><br /><div class="compheader"><div class="complabel">'.format_string($skill->competencyname).'</div><div class="compopt">'.get_string('notapplicable', 'threesixty').'</div><div class="compopt">1</div><div class="compopt">2</div><div class="compopt">3</div><div class="compopt">4</div><div class="clear"><!-- --></div></div>');
                    $curcompetency = $skill->competencyid;
                }

                $mform->addElement('html','<div class="skillset">');
                $elementname = "score_{$skill->id}";
                $radioarray = array();
                $radioarray[] = &$mform->createElement('radio', $elementname, '', '', 0);
                $radioarray[] = &$mform->createElement('radio', $elementname, '', '', 1);
                $radioarray[] = &$mform->createElement('radio', $elementname, '', '', 2);
                $radioarray[] = &$mform->createElement('radio', $elementname, '', '', 3);
                $radioarray[] = &$mform->createElement('radio', $elementname, '', '', 4);
                $skillname = "<div class='skillname'>".format_string($skill->skillname)."</div>";
                $mform->addGroup($radioarray, "radioarray_$skill->id", $skillname);
 				$mform->addElement('html','</div>');
            }
        }
        */
        
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
        
        $this->add_action_buttons();
    }
}
