<?php

	include 'time_array.php';
	
	function create_member($row, &$result = NULL) {
		global $time_array, $date;
		$ballmach = "style='display: none;'";
		$raquet = "style='display: none;'";
		$watch = "style='display: none;'";
		$phone_status = "style='display: none;'";
		$arrival = "style='display: none;'";
		$id = $row["id"];
		$skill = $row["skill"];
		$lname = ucfirst($row["lname"]);
		$fname = ucfirst($row["fname"]);
		$phone1 = $row["phone1"];
		$phone2 = $row["phone2"];
		$rtime = "";
		$requestStr = "";
		$friends = "";
		$arrived = "";
		$called = "";
		
		$filename = "../members/$id/profile_sm.jpg";

		if (file_exists($filename)) {
			$filename = "members/$id/profile_sm.jpg";
		}
		else {
			$filename = "images/member_sm.png";
		}
		
		if ($result) {
			if ($row["ballrequest"]) {		
				$ballmach = "";
			}
			
			if ($row["arrived"]) {
				$arrived = "arrived";
			}
			
			if ($row["called"] == NULL) {
				$called == "";
			}
			elseif ($row["called"] == 0) {
				$called = "called";
			}
			elseif($row["called"] == 1) {
				$called = "accepted";
			}
			
			$friends = $row["friends"];
			if ($friends) {
				$raquet = "";
				$requestStr = "Requests to play with:";
				$friend_fname = ucfirst($row["friend_fname"]);
				$friend_lname = ucfirst($row["friend_lname"]);
				$requestStr .= "&#013$friend_fname $friend_lname";
				for ($i = 1; $i < $friends; $i++) {
					$row = $result->fetch_assoc();
					$friend_fname = ucfirst($row["friend_fname"]);
					$friend_lname = ucfirst($row["friend_lname"]);
					$requestStr .= "&#013$friend_fname $friend_lname";	
				}
			}
			
			if ($row["rTime"]) {
				$rtime = $time_array[$row["rTime"]];
				$watch = "";
			}
			
			if ($date == date("Y-m-d")) {
				$arrival = "";
			}
			else {
				$phone_status = "";
			}
		
		}
		

		
		$data = "
			<li class='member_info' data-itt-id='$id' data-itt-skill='$skill' title='$fname $lname&#013;Ph: $phone1&#013;Ph: $phone2' data-itt-phone='$phone1'>
				<img class='profile_pic' src='$filename' alt width='24' height='30'>
				<div class='name'>$fname $lname</div>
				<div class='arrival $arrived' $arrival></div>
				<div class='phone_status $called' $phone_status'></div>
				<div class='ballmach' $ballmach></div>
				<div class='raquet' $raquet title='$requestStr'></div>
				<div class='blue_box' $raquet title='$requestStr'>$friends</div>
				<div class='watch' $watch title='$rtime'></div>
			</li>";
		return $data;
	}
	
?>