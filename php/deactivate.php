<?php

	include 'connection.php';

	$id = $_POST['id'];
	
	$query = "";
	$query .= "DELETE FROM members_active WHERE id='$id' LIMIT 1;";
	
	$conn->query($query);

	exit;

?>