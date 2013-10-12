<?php

	include 'connection.php';

	$date = $_POST['date'];
	$cnum = $_POST['cnum'];
	$time = $_POST['time'];
	
	$query = "INSERT INTO court_time(cNum, cDate, cTime) VALUES ('$cnum','$date','$time') ON DUPLICATE KEY UPDATE cTime = '$time';";
	
	$conn->query($query);

?>