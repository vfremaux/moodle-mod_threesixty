<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package moodlecore
 * @subpackage backup-moodle2
 * @copyright 2010 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Define all the backup steps that will be used by the backup_threesixty_activity_task
 */

/**
 * Define the complete threesixty structure for backup, with file and id annotations
 */
class backup_threesixty_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {

        // To know if we are including userinfo
        $userinfo = $this->get_setting_value('userinfo');

        // Define each element separated

        $threesixty = new backup_nested_element('threesixty', array('id'), array(
            	'name', 'intro', 'introformat', 'competenciescarried', 'requiredrespondents', 'selftypes', 'respondenttypes', 'grade', 'skillgrade', 'timecreated', 'timemodified'
        ));

        $competencies = new backup_nested_element('competencies');

        $competency = new backup_nested_element('competency', array('id'), array(
        	'name', 'description', 'descriptionformat', 'showfeedback', 'sortorder'
		));

        $skills = new backup_nested_element('skills');

        $skill = new backup_nested_element('skill', array('id'), array(
        	'name', 'description', 'descriptionformat', 'sortorder'
		));

        $analysis = new backup_nested_element('analysis');

        $analyze = new backup_nested_element('analyze', array('id'), array(
        	'userid'
		));

        $carriedcompetencies = new backup_nested_element('carriedcompetencies');

        $carriedcompetency = new backup_nested_element('carriedcompetency', array('id'), array(
        	'competencyid'
		));

        $respondents = new backup_nested_element('respondents');

        $respondent = new backup_nested_element('respondent', array('id'), array(
			'activityid', 'userid', 'respondentuserid', 'email', 'type', 'analysisid', 'uniquehash', 'declined', 'declinetime'
		));

        $responses = new backup_nested_element('responses');

        $response = new backup_nested_element('response', array('id'), array(
			'respondentid', 'timecompleted'
		));

        $responsecomps = new backup_nested_element('responsecomps');

        $responsecomp = new backup_nested_element('responsecomp', array('id'), array(
			'competencyid', 'feedback', 'feedbackformat'
		));

        $responseskills = new backup_nested_element('responseskills');

        $responseskill = new backup_nested_element('responseskill', array('id'), array(
			'skillid', 'score'
		));

        // Build the tree

        $threesixty->add_child($competencies);
        $competencies->add_child($competency);

        $competency->add_child($skills);
        $skills->add_child($skill);

        $threesixty->add_child($analysis);
        $analysis->add_child($analyze);
        $analysis->add_child($carriedcompetencies);
        $carriedcompetencies->add_child($carriedcompetency);
		
        $threesixty->add_child($respondents);
        $respondents->add_child($respondent);

        $threesixty->add_child($responses);
        $responses->add_child($response);

        $response->add_child($responsecomps);
        $responsecomps->add_child($responsecomp);

        $response->add_child($responseskills);
        $responseskills->add_child($responseskill);

        // Define sources

        $threesixty->set_source_table('threesixty', array('id' => backup::VAR_ACTIVITYID));
        $competency->set_source_table('threesixty_competency', array('activityid' => backup::VAR_PARENTID));
        $skill->set_source_table('threesixty_skill', array('comptencyid' => backup::VAR_PARENTID));

        // All these source definitions only happen if we are including user info
        if ($userinfo) {

            $analyze->set_source_table('threesixty_analysis', array('activityid' => backup::VAR_PARENTID));
            $carriedcompetency->set_source_table('threesixty_carried_comp', array('analysisid' => backup::VAR_PARENTID));
            $respondent->set_source_table('threesixty_respondent', array('activityid' => backup::VAR_PARENTID));
            $response->set_source_table('threesixty_response', array('activityid' => backup::VAR_PARENTID));
            $responsecomp->set_source_table('threesixty_response_comp', array('responseid' => backup::VAR_PARENTID));
            $responseskill->set_source_table('threesixty_response_skill', array('responseid' => backup::VAR_PARENTID));

        }

        // Define id annotations

        $threesixty->annotate_ids('scale', 'grade');
        $analyze->annotate_ids('user', 'userid');
        $respondent->annotate_ids('user', 'userid');
        $respondent->annotate_ids('user', 'respondentuserid');

        // Define file annotations

        $threesixty->annotate_files('mod_threesixty', 'intro', null); // This file area hasn't itemid
        $threesixty->annotate_files('mod_threesixty', 'competencydesc', 'id');
        $threesixty->annotate_files('mod_threesixty', 'skilldesc', 'id');

        // Return the root element (threesixty), wrapped into standard activity structure
        return $this->prepare_activity_structure($threesixty);
    }

}
