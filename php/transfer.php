<?php

	include 'connection.php';
	
	$data = json_decode($_POST['data']);
	
	$date = $data[0];
	$cf_num = $data[1];
	$mem_from_sz = $data[2];
	$guest_from_sz = $data[3];
	$time_from = $data[4];
	$locked_from = $data[5];
	$ballmach_from = $data[6];
	$ct_num = $data[7];
	$mem_to_sz = $data[8];
	$guest_to_sz = $data[9];
	$time_to = $data[10];
	$locked_to = $data[11];
	$ballmach_to = $data[12];
	
	$query = "";
	
	if ($mem_from_sz > 0 && $mem_to_sz > 0) {
		$query .= "
		UPDATE members_schedule s1, members_schedule s2 SET s1.cNum = s2.cNum, s2.cNum=s1.cNum 
		WHERE s1.cNum = '$cf_num' AND s2.cNum = '$ct_num' AND s1.cDate = '$date' AND s2.cDate = '$date';";
	}
	else if ($mem_from_sz > 0) {
		$query .= "UPDATE members_schedule SET cNum = '$ct_num' WHERE cNum = '$cf_num' AND cDate = '$date';";
	}
	else if ($mem_to_sz > 0) {
		$query .= "UPDATE members_schedule SET cNum = '$cf_num' WHERE cNum = '$ct_num' AND cDate = '$date';";
	}
	
	if ($guest_from_sz > 0 && $guest_to_sz > 0) {
		$query .= "
		UPDATE guests_schedule s1, guests_schedule s2 SET s1.cNum = s2.cNum, s2.cNum=s1.cNum 
		WHERE s1.cNum = '$cf_num' AND s2.cNum = '$ct_num' AND s1.cDate = '$date' AND s2.cDate = '$date';";
	}
	else if ($guest_from_sz > 0) {
		$query .= "UPDATE guests_schedule SET cNum = '$ct_num' WHERE cNum = '$cf_num' AND cDate = '$date';";
	}
	else if ($guest_to_sz > 0) {
		$query .= "UPDATE guests_schedule SET cNum = '$cf_num' WHERE cNum = '$ct_num' AND cDate = '$date';";
	}

	if (strcmp($time_from,"00:00:00") == 0) {
		$query .= "DELETE FROM court_time WHERE cNum = '$ct_num' AND cDate = '$date' LIMIT 1;";
	}
	else {
		$query .= "INSERT INTO court_time(cNum, cDate, cTime) VALUES ('$ct_num','$date','$time_from') ON DUPLICATE KEY UPDATE cTime='$time_from';";
	}
	
	if (strcmp($time_to,"00:00:00") == 0) {
		$query .= "DELETE FROM court_time WHERE cNum = '$cf_num' AND cDate = '$date' LIMIT 1;";
	}
	else {
		$query .= "INSERT INTO court_time(cNum, cDate, cTime) VALUES ('$cf_num','$date','$time_to') ON DUPLICATE KEY UPDATE cTime='$time_to';";
	}
	
	if ($locked_to) {
		$query .= "DELETE FROM court_locked WHERE cNum = '$ct_num' AND cDate = '$date' LIMIT 1;";
		$query .= "INSERT INTO court_locked(cNum, cDate) VALUES ('$cf_num','$date');";
	}
	
	if ($ballmach_from && !$ballmach_to) {
		$query .= "DELETE FROM court_ballmach WHERE cNum = '$cf_num' AND cDate = '$date' LIMIT 1;";
		$query .= "INSERT INTO court_ballmach(cNum, cDate) VALUES ('$ct_num','$date');";
	}
	else if (!$ballmach_from && $ballmach_to) {
		$query .= "DELETE FROM court_ballmach WHERE cNum = '$ct_num' AND cDate = '$date' LIMIT 1;";
		$query .= "INSERT INTO court_ballmach(cNum, cDate) VALUES ('$cf_num','$date');";	
	}

	$conn->multi_query($query);
	
?>