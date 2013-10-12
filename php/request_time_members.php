<?php

	include 'connection.php';

	$date = $_POST['date'];
	$id = $_POST['id'];
	$time = $_POST['time'];
	
	$query = "";
	
	if (strcmp($time, "00:00:00") == 0) {
		$query .= "DELETE FROM members_request_time WHERE id = '$id' AND rDate ='$date' LIMIT 1;";
	}
	else {
		$query .= "INSERT INTO members_request_time(id, rDate, rTime) VALUES ('$id','$date', '$time') ON DUPLICATE KEY UPDATE rTime = '$time';";
	}
	
	$conn->query($query);


?>