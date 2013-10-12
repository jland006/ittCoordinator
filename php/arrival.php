<?php

	include 'connection.php';

	$date = $_POST['date'];
	$id = $_POST['id'];
	$type = $_POST['type'];
	$arrived = $_POST['arrived'];
	
	$query = "";
	
	if ($arrived) {
		if ($type == "member_info") {
			$query .= "INSERT INTO members_arrived(id, cDate) VALUES ('$id','$date');";
		}
		else {
			$query .= "INSERT INTO guests_arrived(id, cDate) VALUES ('$id','$date');";
		}
	}
	else {
		if ($type == "member_info") {
			$query .= "DELETE FROM members_arrived WHERE id = '$id' AND cDate = '$date' LIMIT 1;";
		}
		else {
			$query .= "DELETE FROM guests_arrived WHERE id = '$id' AND cDate = '$date' LIMIT 1;";
		}
	}
	
	$conn->query($query);
?>