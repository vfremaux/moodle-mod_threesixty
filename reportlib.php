<?php


function print_score_table($skills, $scores, $feedback, $url, $basetype){
	global $OUTPUT;

	$base_score = $scores[$basetype];
	//Set up the column names.
    $header = array('&nbsp;');
    foreach($scores as $score) {
		if($score->type != $basetype){
        	$header[] = '<a href="'.$url.'&base='.$score->type.'">'.$score->name.'</a>';
		}
		else {
			$header[] = format_string($score->name);
		}
    }

    $curcompetency = 0;
    $table = null;
    foreach($skills as $skill) {
        if ($curcompetency != $skill->competencyid) {
            if($table){
                echo html_writer::table($table);
                print_feedback_table($feedback, $curcompetency);
            }
            echo $OUTPUT->heading(format_string($skill->competencyname), 2, 'competencyname');
            $table = new html_table();
            $table->head = $header;
            $table->width = '100%';

            $curcompetency = $skill->competencyid;
        }

        $data = array("<span class='skillname'>". format_string($skill->skillname)."</span>");
        foreach($scores as $scorecolumn) {
            if (empty($scorecolumn->records[$skill->id]) or !$scorecolumn->records[$skill->id]->score) {
                if(!empty($scorecolumn->records[$skill->id]) && $scorecolumn->records[$skill->id]->score == 0){
					$data[] = get_string('notapplicable', 'threesixty');
				} else {
					$data[] = get_string('noscore', 'threesixty');					
				}
            } else {
              	if($base_score && $base_score->records){
                  	$roundedscore = round($scorecolumn->records[$skill->id]->score, AVERAGE_PRECISION);
                    if($roundedscore > $base_score->records[$skill->id]->score){
                      $data[] = "<span class='scorebigger'>".format_string($roundedscore)."</span>";
                    } elseif($roundedscore < $base_score->records[$skill->id]->score){
						$data[] = "<span class='scoresmaller'>".format_string($roundedscore)."</span>";
                    } else{
                    	$data[] = "<span class='scoreeven'>".format_string($roundedscore)."</span>";
                    }
                } else {
                    $roundedscore = round($scorecolumn->records[$skill->id]->score, AVERAGE_PRECISION);
                    $data[] = format_string($roundedscore);
               	}
            }
        }
        $table->data[] = $data;
    }
    echo html_writer::table($table);
    print_feedback_table($feedback, $curcompetency);
}

/**
*
*
*/
function print_feedback_table($feedback, $curcompetency) {
	global $context;

	if (has_capability('mod/threesixty:feedbackview', $context)) {
		//Set up the column names.
	    $header = array(get_string('feedbacks', 'threesixty'));
	    $table = new html_table();
	    $table->head = $header;
	    $table->width = '100%';
	    foreach ($feedback as $f) {
	        if ($f->competencyid == $curcompetency) {
	            $table->data[] = array("<span class='feedback'>".$f->feedback."</span>");
	        }
	    }
	    echo html_writer::table($table);
	}
}

/**
*
*
*/
function get_scores($analysisid, $filters, $competencyaverage){
    $scores = array();

    foreach ($filters as $code => $name) {
        if (strpos($code, 'self') === 0) {
            $typeid = substr($code, 4);
            $scores[$code] = threesixty_get_self_scores($analysisid, $competencyaverage, $typeid);
        }
        elseif ('average' == $code) {
            $scores[$code] = threesixty_get_average_skill_scores($analysisid, false, $competencyaverage);
        }
        elseif (strpos($code, 'type') === 0) {
            // Normal respondent types
            $typeid = substr($code, 4);
            $scores[$code] = threesixty_get_average_skill_scores($analysisid, $typeid, $competencyaverage);
        }
        else {
            error_log("Invalid respondent type filter: $code");
        }
    }

    return $scores;
}

function print_spiderweb(&$activity, $competencies, $scores){
    require_once('php-ofc-library/open-flash-chart.php');
    require_once('php-ofc-library/ofc_sugar.php');

    $chart = new open_flash_chart();

    // All of these colours are on the Web safe colour palette
    $linecolours = array('#FFCC00', '#66CC00', '#CC66FF', '#3366FF', '#FF3399', '#336600',
                         '#66FFFF', '#FF0000', '#990033', '#0000FF', '#999966', '#99FF00');

    foreach($scores as $scoreline) {
        $line = new line();

        $points = array();
        foreach($competencies as $comp) {
            if (empty($scoreline->records[$comp->id]) or !$scoreline->records[$comp->id]->score) {
                $points[] = null;
            } else {
                $roundedscore = round($scoreline->records[$comp->id]->score, AVERAGE_PRECISION);
                $points[] = $roundedscore;
            }
        }

        $linecolour = array_shift($linecolours);

        $line->set_values($points);
        $line->set_default_dot_style(new s_box($linecolour, 4));
        $line->set_width(1);
        $line->set_colour($linecolour);
        $line->set_tooltip("#val#");
        $line->set_key($scoreline->name, 10);
        $line->loop();

        $chart->add_element($line);
    }

    $r = new radar_axis($activity->skillgrade);

    $LIGHTGRAY = '#CCCCCC';
    $r->set_colour($LIGHTGRAY);
    $r->set_grid_colour($LIGHTGRAY);

    $DARKGRAY = '#666666';
    $axislabels = array('');
    for($i = 1 ; $i <= $activity->skillgrade ; $i++){
    	$axislabels[] = "$i";
    }
    $labels = new radar_axis_labels($axislabels);
    $labels->set_colour($DARKGRAY);
    $r->set_labels($labels);

    $competencynames = array();
    foreach ($competencies as $comp) {
        $competencynames[] = $comp->name;
    }

    $spoke_labels = new radar_spoke_labels($competencynames);
    $spoke_labels->set_colour($DARKGRAY);
    $r->set_spoke_labels($spoke_labels);

    $chart->set_radar_axis($r);

    $tooltip = new tooltip();
    $tooltip->set_proximity();
    $chart->set_tooltip($tooltip);

    $WHITE = '#FFFFFF';
    $chart->set_bg_colour($WHITE);

    include 'spiderwebchart.html';
}

/**
*
*
*/
function print_spiderweb_kineo($analysisid, $activityid, $filters) {
	global $CFG;
	// determine the PHP script that Flash will invoke to get the data it needs
	$scriptURL = $CFG->wwwroot . "/mod/threesixty/flash.php";
	// bring in the HTML page which embeds the SWF
	include("spiderwebchart_kineo.html");
}
