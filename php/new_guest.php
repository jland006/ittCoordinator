<?php

	include 'connection.php';
	
	$data_array = $_POST['data'];
	$data_array = json_decode($data_array);
	
	$LName = $data_array[0];
	$FName = $data_array[1];
	$Phone1 = $data_array[2];
	
	$empty_lname =  "Please enter a last name\n";
	$empty_fname =  "Please enter a first name\n";
	$empty_phone1 =  "Please enter a primary phone\n";
	
	
	$invalid_lname = "Please enter a valid last name\n";
	$invalid_fname = "Please enter a valid first name\n";
	$invalid_phone1 = "Please enter a valid primary phone\n";
	
	$success = "Guest successfully created and added to the 'Guests List'";
	
	$alert = '';
	
	if (empty($LName))
		$alert .= $empty_lname;
	elseif ( !preg_match( "/^[a-zA-Z]+$/", $LName ) )
		$alert .= $invalid_lname;
		
	if (empty($FName))
		$alert .= $empty_fname;
	elseif ( !preg_match( "/^[a-zA-Z]+$/", $FName ) )
		$alert .= $invalid_fname;

	if (!empty($Phone1)){
		if ( !preg_match( "/^[2-9]\d{9}$/", $Phone1 ) )
			$alert .= $invalid_phone1;
	}
		
	if ( !empty($alert) )
		echo json_encode(array($alert));
	else {
	
		$LName = strtolower($LName);
		$FName = strtolower($FName);
	
		$LName = $conn->real_escape_string($LName);
		$FName = $conn->real_escape_string($FName);

		$query = "SELECT UUID_SHORT() AS id";
		$resultQuery = $conn->query($query);
		$row = $resultQuery->fetch_assoc();
		$id = $row['id'];
		
		$query = "INSERT INTO guests(id, email, pass, lname, fname, gender, skill, phone1, phone2, createdAt) VALUES ('$id','','','$LName','$FName','','','$Phone1','',NOW());";
		$conn->query($query);
	
		$guest = "
			<li class='guest_info' data-itt-id='$id' data-itt-skill='' title='$FName $LName&#013;Ph: $Phone1' data-itt-phone='$Phone1'>
            	<img class='profile_pic' src='images/guest_sm.png' alt width='24' height='30'>
                <div class='name'>$FName $LName</div>
                <div class='guest_logo'>GUEST</div>
            </li>";
		echo json_encode(array($success, $guest));
	}
	
	exit;
	
?>