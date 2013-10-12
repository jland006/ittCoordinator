<?php

	include 'connection.php';

	$player_id = $_POST['player_id'];
	$id = $_POST['id'];

	$query .= "INSERT INTO members_blklist(id, enemy) VALUES ('$player_id','$id');";
	
	$conn->query($query);
	

?>