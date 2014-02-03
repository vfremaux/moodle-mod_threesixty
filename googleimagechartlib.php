<?php

/**
* prints an indexed container for imagechart
* @return the actual index for this instance;
*/
function google_imagechart_html() {
	static $objectidix = 0;

	$objectidix++;

	echo '<div id="imagechart'.$objectidix.'"></div>';
	
	return $objectidix;
}

function google_imagechart_preload(){
	global $CFG, $PAGE;
	
	echo '<script src="https://www.google.com/jsapi" type="text/javascript"></script>';
	$PAGE->requires->js('/mod/threesixty/js/googlechart_preload.php');
}

function google_imagechart_js($datatable, $instanceid, $width, $height){

	$snippet = '
    <script type="text/javascript">

      // Load the Visualization API and the piechart package.
      google.load(\'visualization\', \'1.0\', {\'packages\':[\'imagechart\'], \'language\': \''.$lang.'\'});

      // Set a callback to run when the Google Visualization API is loaded.
      googlestack.push(\'drawImageChart'.$instanceid.'\');

      // Callback that creates and populates a data table,
      // instantiates the pie chart, passes in the data and
      // draws it.
      function drawImageChart'.$instanceid.'() {

            var options = {};
            // Chart API chart type \'rs\' is radar chart
            options.cht = \'rs\';
      
            // set the line colors
            options.colors = [\'#008000\' , \'#808000\' , \'#008080\' , \'#000080\' , \'#800080\' , \'#800000\'];
      
            // fill the area under the lines
            options.fill = false;
      
            // create a grid for the chart
            options.chg = \'25.0,25.0,4.0,4.0\';
            options.width = \''.$width.'\';
            options.height = \''.$height.'\';
      
            dataTable = google.visualization.arrayToDataTable('.$datatable.');
      
        // Instantiate and draw our chart, passing in some options.
        var chart = new google.visualization.ImageChart(document.getElementById(\'imagechart'.$instanceid.'\'));
        chart.draw(data, options);
      }
    </script>
	';
}