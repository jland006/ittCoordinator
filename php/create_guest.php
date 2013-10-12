<?php
	
	function create_guest($row, $special = NULL) {
		global $date;
		$id = $row["id"];
		$fname = ucfirst($row["fname"]);
		$lname = ucfirst($row["lname"]);
		$phone1 = phone_conv($row["phone1"]);
		$phone2 = phone_conv($row["phone2"]);
		$phone_status = "style='display: none;'";
		$arrival = "style='display: none;'";
		$arrived = "";
		$called = "";
		
		if ($special) {
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
			
			if ($date == date("Y-m-d")) {
				$arrival = "";
			}
			else {
				$phone_status = "";
			}
		}
			
		$data = "
			<li class='guest_info' data-itt-id='$id' data-itt-skill='' data-itt-phone_info='$fname $lname<br>Ph: $phone1' data-itt-phone='$phone1'>
            	<img class='profile_pic' src='images/guest_sm.png' alt width='24' height='30'>
                <div class='name'>$fname $lname</div>
				<div class='arrival $arrived' $arrival></div>
				<div class='phone_status $called' $phone_status></div>
            </li>";
		return $data;
	}
	
?>