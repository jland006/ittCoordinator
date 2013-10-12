<?php

	date_default_timezone_set('America/Los_Angeles');
	
	$conn = new mysqli('aa5py20z65bsuh.cwmhwibeaszn.us-west-1.rds.amazonaws.com', 'jland006', 'nhsg+3Zf', 'nettimi_sunrisedata');

	// Works as of PHP 5.2.9 and 5.3.0.
	if ($conn->connect_error) {
		die('Connect Error: ' . $newdb->connect_error);
	}
?>