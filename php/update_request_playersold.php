<?php

	include 'connection.php';

	$player_id = $_POST['player_id'];
	$date = $_POST['date'];
	$id = $_POST['id'];
	$type = $_POST['type'];
	
	$list_from_1 = $_POST['list_from'];
	$list_from_2 = explode(" ", $list_from_1, 2);
	$list_from = $list_from_2[0];
	
	//Always put INSERT queries last!!!!!!
	$query = "";
	
	if ($list_from == "all_members") {
		$query .= "INSERT INTO members_request_friend(id, rDate, friend) VALUES ('$player_id','$date','$id');";
	}
	
	$conn->multi_query($query);
	

?>