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
 * Define all the restore steps that will be used by the restore_threesixty_activity_task
 */

/**
 * Structure step to restore one threesixty activity
 */
class restore_threesixty_activity_structure_step extends restore_activity_structure_step {

    protected function define_structure() {

        $paths = array();
        $userinfo = $this->get_setting_value('userinfo');

        $paths[] = new restore_path_element('threesixty', '/activity/threesixty');
        if ($userinfo) {
            $paths[] = new restore_path_element('threesixty_competency', '/activity/threesixty/comptencies/competency');
            $paths[] = new restore_path_element('threesixty_skill', '/activity/threesixty/comptencies/competency/skills/skill');
            $paths[] = new restore_path_element('threesixty_analyze', '/activity/threesixty/analysis/analyze');
            $paths[] = new restore_path_element('threesixty_carriedcompetency', '/activity/threesixty/analysis/analyze/carriedcompetencies/carriedcompetency');
            $paths[] = new restore_path_element('threesixty_respondent', '/activity/threesixty/respondents/respondent');
            $paths[] = new restore_path_element('threesixty_response', '/activity/threesixty/responses/response');
            $paths[] = new restore_path_element('threesixty_response_comp', '/activity/threesixty/responses/response/responsecomps/responsecomp');
            $paths[] = new restore_path_element('threesixty_response_comp', '/activity/threesixty/responses/response/responseskills/responseskill');
        }

        // Return the paths wrapped into standard activity structure
        return $this->prepare_activity_structure($paths);
    }

    protected function process_threesixty($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();

        $data->assesstimestart = $this->apply_date_offset($data->assesstimestart);
        $data->assesstimefinish = $this->apply_date_offset($data->assesstimefinish);
        if ($data->grade < 0) { // scale found, get mapping
            $data->grade = -($this->get_mappingid('scale', abs($data->grade)));
        }

        $newitemid = $DB->insert_record('threesixty', $data);
        $this->apply_activity_instance($newitemid);
    }

    protected function process_threesixty_competency($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->activityid = $this->get_new_parentid('threesixty');

		/*
        $data->timemodified = $this->apply_date_offset($data->timemodified);
        $data->timestart = $this->apply_date_offset($data->timestart);
        $data->timeend = $this->apply_date_offset($data->timeend);
        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->groupid = $this->get_mappingid('group', $data->groupid);
        $data->usermodified = $this->get_mappingid('user', $data->usermodified);
		*/
		
        $newitemid = $DB->insert_record('threesixty_competency', $data);
        $this->set_mapping('threesixty_competency', $oldid, $newitemid);
    }

    protected function process_threesixty_skill($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->competencyid = $this->get_new_parentid('threesixty_competency');
		
        $newitemid = $DB->insert_record('threesixty_skill', $data);
        $this->set_mapping('threesixty_skill', $oldid, $newitemid);
    }

    protected function process_threesixty_analyze($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->activityid = $this->get_new_parentid('threesixty');
        $data->userid = $this->get_mappingid('user', $data->userid);
		
        $newitemid = $DB->insert_record('threesixty_analysis', $data);
        $this->set_mapping('threesixty_analysis', $oldid, $newitemid);
    }

    protected function process_threesixty_carriedcompetency($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->analysisid = $this->get_new_parentid('threesixty_analysis');
        $data->competencyid = $this->get_mappingid('threesixty_competency', $data->competencyid);
		
        $newitemid = $DB->insert_record('threesixty_carried_comp', $data);
        $this->set_mapping('threesixty_carried_comp', $oldid, $newitemid);
    }

    protected function process_threesixty_respondent($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->activityid = $this->get_new_parentid('threesixty');
        $data->userid = $this->get_mappingid('user', $data->userid);
        if ($data->respondentuserid){
	        $data->respondentuserid = $this->get_mappingid('user', $data->respondentuserid);
	    }
        $data->analysisid = $this->get_mappingid('threesixty_analysis', $data->analysisid);
        $data->declinetime = $this->apply_date_offset($data->declinetime);
		
        $newitemid = $DB->insert_record('threesixty_respondent', $data);
        $this->set_mapping('threesixty_respondent', $oldid, $newitemid);
    }

    protected function process_threesixty_response($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->activityid = $this->get_new_parentid('threesixty_analyze');
        $data->respondentid = $this->get_mappingid('threesixty_respondent', $data->respondentid);
        $data->timecompleted = $this->apply_date_offset($data->timecompleted);
		
        $newitemid = $DB->insert_record('threesixty_response', $data);
        $this->set_mapping('threesixty_response', $oldid, $newitemid);
    }

    protected function process_threesixty_response_skill($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->responseid = $this->get_new_parentid('threesixty_response');
        $data->skillid = $this->get_mappingid('threesixty_skill', $data->skillid);
		
        $newitemid = $DB->insert_record('threesixty_response_skill', $data);
        $this->set_mapping('threesixty_response_skill', $oldid, $newitemid);
    }

    protected function process_threesixty_response_comp($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->responseid = $this->get_new_parentid('threesixty_response');
        $data->competencyid = $this->get_mappingid('threesixty_competency', $data->competencyid);
		
        $newitemid = $DB->insert_record('threesixty_response_comp', $data);
        $this->set_mapping('threesixty_response_comp', $oldid, $newitemid);
    }

    protected function process_threesixty_post($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->discussion = $this->get_new_parentid('threesixty_discussion');
        $data->created = $this->apply_date_offset($data->created);
        $data->modified = $this->apply_date_offset($data->modified);
        $data->userid = $this->get_mappingid('user', $data->userid);
        // If post has parent, map it (it has been already restored)
        if (!empty($data->parent)) {
            $data->parent = $this->get_mappingid('threesixty_post', $data->parent);
        }

        $newitemid = $DB->insert_record('threesixty_posts', $data);
        $this->set_mapping('threesixty_post', $oldid, $newitemid, true);

        // If !post->parent, it's the 1st post. Set it in discussion
        if (empty($data->parent)) {
            $DB->set_field('threesixty_discussions', 'firstpost', $newitemid, array('id' => $data->discussion));
        }
    }

    protected function process_threesixty_rating($data) {
        global $DB;

        $data = (object)$data;

        // Cannot use ratings API, cause, it's missing the ability to specify times (modified/created)
        $data->contextid = $this->task->get_contextid();
        $data->itemid    = $this->get_new_parentid('threesixty_post');
        if ($data->scaleid < 0) { // scale found, get mapping
            $data->scaleid = -($this->get_mappingid('scale', abs($data->scaleid)));
        }
        $data->rating = $data->value;
        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->timecreated = $this->apply_date_offset($data->timecreated);
        $data->timemodified = $this->apply_date_offset($data->timemodified);

        // We need to check that component and ratingarea are both set here.
        if (empty($data->component)) {
            $data->component = 'mod_threesixty';
        }
        if (empty($data->ratingarea)) {
            $data->ratingarea = 'post';
        }

        $newitemid = $DB->insert_record('rating', $data);
    }

    protected function process_threesixty_subscription($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->threesixty = $this->get_new_parentid('threesixty');
        $data->userid = $this->get_mappingid('user', $data->userid);

        $newitemid = $DB->insert_record('threesixty_subscriptions', $data);
    }

    protected function process_threesixty_read($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->threesixtyid = $this->get_new_parentid('threesixty');
        $data->discussionid = $this->get_mappingid('threesixty_discussion', $data->discussionid);
        $data->postid = $this->get_mappingid('threesixty_post', $data->postid);
        $data->userid = $this->get_mappingid('user', $data->userid);

        $newitemid = $DB->insert_record('threesixty_read', $data);
    }

    protected function process_threesixty_track($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->threesixtyid = $this->get_new_parentid('threesixty');
        $data->userid = $this->get_mappingid('user', $data->userid);

        $newitemid = $DB->insert_record('threesixty_track_prefs', $data);
    }

    protected function after_execute() {
        global $DB;

        // Add threesixty related files, no need to match by itemname (just internally handled context)
        $this->add_related_files('mod_threesixty', 'intro', null);

        // If the threesixty is of type 'single' and no discussion has been ignited
        // (non-userinfo backup/restore) create the discussion here, using threesixty
        // information as base for the initial post.
        $threesixtyid = $this->task->get_activityid();
        $threesixtyrec = $DB->get_record('threesixty', array('id' => $threesixtyid));
        if ($threesixtyrec->type == 'single' && !$DB->record_exists('threesixty_discussions', array('threesixty' => $threesixtyid))) {
            // Create single discussion/lead post from threesixty data
            $sd = new stdclass();
            $sd->course   = $threesixtyrec->course;
            $sd->threesixty    = $threesixtyrec->id;
            $sd->name     = $threesixtyrec->name;
            $sd->assessed = $threesixtyrec->assessed;
            $sd->message  = $threesixtyrec->intro;
            $sd->messageformat = $threesixtyrec->introformat;
            $sd->messagetrust  = true;
            $sd->mailnow  = false;
            $sdid = threesixty_add_discussion($sd, null, $sillybyrefvar, $this->task->get_userid());
            // Mark the post as mailed
            $DB->set_field ('threesixty_posts','mailed', '1', array('discussion' => $sdid));
            // Copy all the files from mod_foum/intro to mod_threesixty/post
            $fs = get_file_storage();
            $files = $fs->get_area_files($this->task->get_contextid(), 'mod_threesixty', 'intro');
            foreach ($files as $file) {
                $newfilerecord = new stdclass();
                $newfilerecord->filearea = 'post';
                $newfilerecord->itemid   = $DB->get_field('threesixty_discussions', 'firstpost', array('id' => $sdid));
                $fs->create_file_from_storedfile($newfilerecord, $file);
            }
        }

        // Add post related files, matching by itemname = 'threesixty_post'
        $this->add_related_files('mod_threesixty', 'post', 'threesixty_post');
        $this->add_related_files('mod_threesixty', 'attachment', 'threesixty_post');
    }
}
