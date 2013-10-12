<?php

	include 'connection.php';

	$player_id = $_POST['player_id'];
	$id = $_POST['id'];

	$query .= "DELETE FROM members_blklist WHERE id='$player_id' AND enemy='$id';";
	
	$conn->query($query);
	

?>