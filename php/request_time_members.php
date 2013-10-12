<?php

	include 'connection.php';

	$date = $_POST['date'];
	$id = $_POST['id'];
	$time = $_POST['time'];
	
	$query = "";
	
	$query .= "INSERT INTO members_request_time(id, rDate, rTime) VALUES ('$id','$date', '$time') ON DUPLICATE KEY UPDATE rTime = '$time';";
	
	$conn->query($query);

?>