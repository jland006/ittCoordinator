<?php

	include 'connection.php';

	$date = $_POST['date'];
	$cnum = $_POST['cnum'];
	$ball = $_POST['ball'];
	
	$query = "";
	
	if ($ball) {
		$query .= "INSERT INTO court_ballmach(cNum, cDate) VALUES ('$cnum','$date');";
	}
	else {
		$query .= "DELETE FROM court_ballmach WHERE cNum = '$cnum' AND cDate = '$date' LIMIT 1;";
	}
	
	$conn->query($query);
?>