<?php

	include 'connection.php';

	$date = $_POST['date'];
	$cnum = $_POST['cnum'];
	$lock = $_POST['lock'];
	
	$query = "";
	
	if ($lock) {
		$query .= "INSERT INTO court_locked(cNum, cDate) VALUES ('$cnum','$date');";
	}
	else {
		$query .= "DELETE FROM court_locked WHERE cNum = '$cnum' AND cDate = '$date' LIMIT 1;";
	}
	
	$conn->query($query);

?>