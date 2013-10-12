<?php

	include 'connection.php';

	$id = $_POST['id'];
	
	$query = "";
	$query .= "INSERT INTO members_active(id) VALUES ('$id');";
	$query .= "SELECT * FROM members WHERE id='$id' LIMIT 1;";
	
	$output = "";
	
	if ($conn->multi_query($query)) {
		$conn->next_result();
		$resultQuery = $conn->store_result();
		$row = $resultQuery->fetch_assoc();
		$output .= create_member($row);
	}
	
	echo $output;
	exit;
	
	function create_member($row) {
		$id = $row["id"];
		$skill = $row["skill"];
		$lname = ucfirst($row["lname"]);
		$fname = ucfirst($row["fname"]);
		$phone1 = $row["phone1"];
		$phone2 = $row["phone2"];
		
		$data = "
			<li class='member_info' data-itt-id='$id' data-itt-skill='$skill' title='$fname $lname&#013;Ph: $phone1&#013;Ph: $phone2' data-itt-phone='$phone1'>
				<img class='profile_pic' src='images/member_sm.png' alt width='24' height='30'>
				<div class='name'>$fname $lname</div>
			</li>";
		return $data;
	}
	
?>