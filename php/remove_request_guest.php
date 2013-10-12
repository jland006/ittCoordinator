<?php

	include 'connection.php';

	$player_id = $_POST['player_id'];
	$date = $_POST['date'];
	$id = $_POST['id'];

	$query .= "DELETE FROM members_request_friend WHERE id='$player_id' AND rDate='$date' AND friend='$id';";
	
	$conn->query($query);
	

?>