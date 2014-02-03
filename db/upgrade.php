<?php

function xmldb_threesixty_upgrade($oldversion = 0) {
    global $CFG, $THEME;
    
    $dbman = get_database_manager();

    $result = true;
    if ($result && $oldversion < 2009122101){
        //Add a display order column for the competency table.
        $comptable = new xmldb_table('threesixty_competency');
        $field = new xmldb_field('sortorder');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '999', 'showfeedback');
        if(!$dbman->field_exists($comptable, $field)){
        	$dbman->add_field($comptable, $field);
        }

        reorder_competencies();

        $skilltable = new xmldb_table('threesixty_skill');
        $field->previous = 'description';
        if(!$dbman->field_exists($comptable, $field)){
	        $dbman->add_field($skilltable, $field)){
	    }

        //Update the existing competency data.
        reorder_skills();
        upgrade_mod_savepoint('threesixty', 2009122101);
    }

    if ($oldversion < 2012110500){
    /// Define field respondenttypes to be added to threesixty
        $table = new xmldb_table('threesixty');
        $field = new xmldb_field('respondenttypes');
        $field->set_attributes(XMLDB_TYPE_TEXT, 'small', null, null, null, null, 'requiredrespondents');

    /// Launch add field respondenttypes
        $dbman->add_field($table, $field);

        $field = new xmldb_field('selftypes');
        $field->set_attributes(XMLDB_TYPE_TEXT, 'small', null, null, null, null, 'respondenttypes');

    /// Launch add field selftypes
        $dbman->add_field($table, $field);

        $field = new xmldb_field('grade');
        $field->set_attributes(XMLDB_TYPE_INTEGER, 'int', 10, XMLDB_UNSIGNED, XMLDB_NOTNULL, 0, 'selftypes');

    /// Launch add field selftypes
        $dbman->add_field($table, $field);

        $field = new xmldb_field('skillgrade');
        $field->set_attributes(XMLDB_TYPE_INTEGER, 'int', 10, XMLDB_UNSIGNED, XMLDB_NOTNULL, 0, 'grade');

    /// Launch add field selftypes
        $dbman->add_field($table, $field);

    /// Define field activityid to be added to threesixty_respondent
        $table = new xmldb_table('threesixty_respondent');
        $field = new xmldb_field('activityid');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'id');

    /// Launch add field activityid
        $dbman->add_field($table, $field);

        $field = new xmldb_field('userid');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'activityid');

    /// Launch add field userid
        $dbman->add_field($table, $field);

        $field = new xmldb_field('respondentuserid');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'userid');

    /// Launch add field userid
        $dbman->add_field($table, $field);

        $field = new xmldb_field('declined');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'uniquehash');

    /// Launch add field declined
        $dbman->add_field($table, $field);

        $field = new xmldb_field('declinetime');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '11', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'declined');

    /// Launch add field declined
        $dbman->add_field($table, $field);

        upgrade_mod_savepoint('threesixty', 2012110500);
    }
    // Moodle 2 db changes

    return $result;
}

function reorder_competencies(){
  	global $CFG, $DB;

  	//$sql = "SELECT * FROM ".$CFG->prefix."threesixty_competency ORDER BY activityid, id";
  	if ($competencies = $DB->get_records('threesixty_competency', array(), 'activityid')){
    	$activityid = 0;
    	$nextposition = 0;
    	foreach ($competencies as $competency){
      		if($activityid != $competency->activityid){
        		$nextposition = 0;
        		$activityid = $competency->activityid;
      		}
      		$competency->sortorder = $nextposition;
      		$nextposition ++;
      		$DB->update_record('threesixty_competency', $competency);
    	}
  	} 
}

function reorder_skills(){
 	global $CFG, $DB;
  	//$sql = "SELECT * FROM ".$CFG->prefix."threesixty_skill ORDER BY competencyid, id";
  	if ($skills = $DB->get_records('threesixty_skill', array(array() => 'competencyid'))){
    	$competencyid = 0;
    	$nextposition = 0;

    	foreach ($skills as $skill){
      		if($competencyid != $skill->competencyid){
        		$nextposition = 0;
        		$competencyid = $skill->competencyid;
      		}
      		$skill->sortorder = $nextposition;
      		$nextposition ++;
      		$DB->update_record('threesixty_skill', $skill);
    	}
  	}
}