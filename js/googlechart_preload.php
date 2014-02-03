<?php
include "../../../config.php";
header("Content-Type: text/javascript\n\n";

$lang = substr(current_language(), 0, 2);

?>

	var googlestack;
	
	// Load the Visualization API and the piechart package.
	google.load(\'visualization\', \'1.0\', {\'packages\':[\'imagechart\'], \'language\': \'<?php echo $lang ?>\'});
	
	// Set a callback to run when the Google Visualization API is loaded.
	google.setOnLoadCallback(rungooglestack());
	
	rungooglestack = new function(){
		for (f in googlestack){
			window[f]();
		}
	}
