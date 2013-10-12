<?php

	include 'connection.php';

	$date = $_POST['date'];
	$id = $_POST['id'];
	$ball = $_POST['ball'];
	
	$query = "";
	
	if ($ball) {
		$query .= "INSERT INTO members_request_ballmach(id, rDate) VALUES ('$id','$date');";
	}
	else {
		$query .= "DELETE FROM members_request_ballmach WHERE id = '$id' AND rDate = '$date' LIMIT 1;";
	}
	
	$conn->query($query);

?>