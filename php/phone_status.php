<?php

	include 'connection.php';

	$date = $_POST['date'];
	$id = $_POST['id'];
	$type = $_POST['type'];
	$phoned = $_POST['phoned'];
	
	$query = "";
	
	if ($phoned == 2) {
		if ($type == "member_info") {
			$query .= "DELETE FROM members_called WHERE id = '$id' AND cDate = '$date' LIMIT 1;";
		}
		else {
			$query .= "DELETE FROM guests_called WHERE id = '$id' AND cDate = '$date' LIMIT 1;";
		}
	}
	else {
		if ($type == "member_info") {
			$query .= "INSERT INTO members_called(id, cDate, called) VALUES ('$id','$date', '$phoned') ON DUPLICATE KEY UPDATE called='$phoned';";
		}
		else {
			$query .= "INSERT INTO guests_called(id, cDate, called) VALUES ('$id','$date', '$phoned') ON DUPLICATE KEY UPDATE called='$phoned';";
		}
	}

	$conn->query($query);
?>