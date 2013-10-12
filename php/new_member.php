<?php

	include 'connection.php';
	include 'phone_conv.php';
	
	$data_array = $_POST['data'];
	$data_array = json_decode($data_array);
	
	$LName = $data_array[0];
	$FName = $data_array[1];
	$Phone1 = $data_array[2];
	$Phone2 = $data_array[3];
	$Email = $data_array[4];
	$Gender = $data_array[5];
	$Level = $data_array[6];
	
	$empty_lname =  "Please enter a last name\n";
	$empty_fname =  "Please enter a first name\n";
	$empty_phone1 =  "Please enter a primary phone\n";
	
	
	$invalid_lname = "Please enter a valid last name\n";
	$invalid_fname = "Please enter a valid first name\n";
	$invalid_phone1 = "Please enter a valid primary phone\n";
	$invalid_phone2 = "Please enter a valid secondary phone\n";
	$invalid_email = "Please enter a valid e-mail\n";
	
	$success = "Member successfully created and added to the 'Members List'";
	
	$alert = '';
	
	if (empty($LName))
		$alert .= $empty_lname;
	elseif ( !preg_match( "/^[a-zA-Z]+$/", $LName ) )
		$alert .= $invalid_lname;
		
	if (empty($FName))
		$alert .= $empty_fname;
	elseif ( !preg_match( "/^[a-zA-Z]+$/", $FName ) )
		$alert .= $invalid_fname;

	if (empty($Phone1))
		$alert .= $empty_phone1;
	elseif ( !preg_match( "/^[2-9]\d{9}$/", $Phone1 ) )
		$alert .= $invalid_phone1;

	if (!empty($Phone2)){
		if ( !preg_match( "/^[2-9]\d{9}$/", $Phone2 ) )
			$alert .= $invalid_phone2;
	}
	
	if (!empty($Email)){
		if ( !preg_match("/^[_a-z0-9-]+(.[_a-z0-9-]+)*@[a-z0-9-]+(.[a-z0-9-]+)*(.[a-z]{2,3})$/", $Email) )
			$alert .= $invalid_email;
	}
	
	if ( !empty($alert) )
		echo json_encode(array($alert));
	else {
	
		$LName = strtolower($LName);
		$FName = strtolower($FName);
		$Email = strtolower($Email);
	
		$LName = $conn->real_escape_string($LName);
		$FName = $conn->real_escape_string($FName);
		$Email = $conn->real_escape_string($Email);

		$query = "SELECT UUID_SHORT() AS id";
		$resultQuery = $conn->query($query);
		$row = $resultQuery->fetch_assoc();
		$id = $row['id'];
		
		$query = "INSERT INTO members(id, email, pass, lname, fname, gender, skill, phone1, phone2, createdAt) VALUES ('$id','$Email','','$LName','$FName','$Gender','$Level','$Phone1','$Phone2',NOW());";
		$query .= "INSERT INTO members_active(id) VALUES ('$id')";
		$conn->multi_query($query);
	
		$LName = ucfirst($LName);
		$FName = ucfirst($FName);
		$Phone1 = phone_conv($Phone1);
		
		if (!empty($Phone2)) {
			$Phone2 = phone_conv($Phone2);
		}

		$member = "
			<li class='member_info' data-itt-id='$id' data-itt-skill='$Level' data-itt-phone_info='$FName $LName ($Level)<br>Ph: $Phone1<br>Ph: $Phone2' data-itt-phone='$Phone1'>
				<img class='profile_pic' src='images/member_sm.png' alt width='24' height='30'>
				<div class='name'>$FName $LName</div>
			</li>";
		echo json_encode(array($success, $member));
	}
	
	exit;
	
?>