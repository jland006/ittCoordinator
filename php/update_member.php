<?php

	include 'connection.php';
	
	$id = $_POST['id'];
	$data_array = $_POST['data'];
	$data_array = json_decode($data_array);
	
	$Phone1 = $data_array[0];
	$Phone2 = $data_array[1];
	$Email = $data_array[2];
	$Level = $data_array[3];
	
	$empty_phone1 =  "Please enter a primary phone\n";

	$invalid_phone1 = "Please enter a valid primary phone\n";
	$invalid_phone2 = "Please enter a valid secondary phone\n";
	$invalid_email = "Please enter a valid e-mail\n";
	
	$alert = '';
	
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
		echo $alert;
	else {
		$Email = strtolower($Email);
	
		$Email = $conn->real_escape_string($Email);

		$query = "UPDATE members SET email='$Email',skill='$Level',phone1='$Phone1',phone2='$Phone2' WHERE id='$id'";
		$conn->query($query);
	}
	
	exit;

?>