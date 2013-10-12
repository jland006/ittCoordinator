<?php

	include 'connection.php';

	$date = $_POST['date'];
	$cnum_to = $_POST['cnum_to'];
	$cnum_from = $_POST['cnum_from'];
	
	$list_to_1 = $_POST['list_to'];
	$list_to_2 = explode(" ", $list_to_1, 2);
	$list_to = $list_to_2[0];
	
	$list_from_1 = $_POST['list_from'];
	$list_from_2 = explode(" ", $list_from_1, 2);
	$list_from = $list_from_2[0];
	
	$id = $_POST['id'];
	$type = $_POST['type'];
	
	//Always put INSERT queries last!!!!!!
	$query = "";
	
	if ($list_from == "all_members" && $list_to == "waiting_list") {
		$query .= "INSERT INTO members_request(id, rDate) VALUES ('$id','$date');";
	}
	else if ($list_from == "all_guests" && $list_to == "waiting_list") {
		$query .= "INSERT INTO guests_request(id, rDate) VALUES ('$id','$date');";
	}
	else if ($list_from == "waiting_list" && $list_to == "members_list") {
		if ($type == "member_info") {
			$query .= "INSERT INTO members_schedule(cNum, cDate, id) VALUES ('$cnum_to','$date','$id');";
		}
		elseif ($type == "guest_info") {
			$query .= "INSERT INTO guests_schedule(cNum, cDate, id) VALUES ('$cnum_to','$date','$id');";
		}
	}
	else if ($list_from == "all_members" && $list_to == "members_list") {
		$query .= "INSERT INTO members_schedule(cNum, cDate, id) VALUES ('$cnum_to','$date','$id');";
	}
	else if ($list_from == "all_guests" && $list_to == "members_list") {
		$query .= "INSERT INTO guests_schedule(cNum, cDate, id) VALUES ('$cnum_to','$date','$id');";
	}
	else if ($list_from == "members_list" && $list_to == "members_list") {
		if ($type == "member_info") {
			$query .= "UPDATE members_schedule SET cNum = '$cnum_to' WHERE cNum = '$cnum_from' AND cDate = '$date' AND id = '$id';";
		}
		elseif ($type == "guest_info") {
			$query .= "UPDATE guests_schedule SET cNum = '$cnum_to' WHERE cNum = '$cnum_from' AND cDate = '$date' AND id = '$id';";
		}
	}
	else if ($list_from == "members_list") { //&& $list_to == "waiting_list") {
		if ($type == "member_info") {
			$query .= "DELETE FROM members_schedule WHERE cNum = '$cnum_from' AND cDate = '$date' AND id = '$id';";
			//$query .= "INSERT INTO members_request(id, rDate) VALUES ('$id','$date');";
		}
		elseif ($type == "guest_info") {
			$query .= "DELETE FROM guests_schedule WHERE cNum = '$cnum_from' AND cDate = '$date' AND id = '$id';";
			//$query .= "INSERT INTO guests_request(id, rDate) VALUES ('$id','$date');";
		}
	}
	
	$conn->multi_query($query);
	

?>