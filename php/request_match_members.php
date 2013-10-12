<?php

	include 'connection.php';

	$date = $_POST['date'];
	$id = $_POST['id'];
	$request = $_POST['request'];
	
	$query = "";
	
	if ($request) {
		$query .= "INSERT INTO members_request(id, rDate) VALUES ('$id','$date');";
	}
	else {
		$query .= "DELETE FROM members_request WHERE id = '$id' AND rDate = '$date' LIMIT 1;";
		$query .= "DELETE FROM members_request_time WHERE id = '$id' AND rDate = '$date' LIMIT 1;";
		$query .= "DELETE FROM members_request_ballmach WHERE id = '$id' AND rDate = '$date' LIMIT 1;";
		$query .= "DELETE FROM members_request_friend WHERE id = '$id' AND rDate = '$date';";
	}
	
	$conn->multi_query($query);

?>