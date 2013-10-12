<?php

	include 'connection.php';

	$player_id = $_POST['player_id'];
	$date = $_POST['date'];
	$id = $_POST['id'];

	$query .= "INSERT INTO members_request_friend(id, rDate, friend) VALUES ('$player_id','$date','$id');";
	
	$conn->query($query);
	

?>