<?php

	include 'connection.php';

	$date = $_POST['date'];
	$cnum = $_POST['cnum'];
	
	$queryGetCourts = "SELECT C.cNum FROM (
	SELECT cNum FROM court_time WHERE cNum REGEXP '^$cnum\[A\-Z\]$' AND cDate = '$date'
	UNION
	SELECT cNum FROM court_ballmach WHERE cNum REGEXP '^7\[A\-Z\]$' AND cDate = '$date'
	UNION
	SELECT cNum FROM court_locked WHERE cNum REGEXP '^7\[A\-Z\]$' AND cDate = '$date'
	UNION
	SELECT cNum FROM guests_schedule WHERE cNum REGEXP '^7\[A\-Z\]$' AND cDate = '$date'
	UNION
	SELECT cNum FROM members_schedule WHERE cNum REGEXP '^7\[A\-Z\]$' AND cDate = '$date') C 
	ORDER BY LENGTH(C.cNum), C.cNum";
	
	$resultGetCourts = $conn->query($queryGetCourts);
	
	$offset = 65; //Ascii for A
	while($row = $resultGetCourts->fetch_assoc()) {
		$court = $row["cNum"];
		$newCourt = $cnum.chr($offset);
		
		if ($court == $newCourt) {
			$offset++;
		}
		else {
			break;
		}

	}
	$newCourt = $cnum.chr($offset);
	
	//this is so we can save added courts
	$query = "INSERT INTO court_time(cNum, cDate, cTime) VALUES ('$newCourt','$date','');";
		
	$conn->query($query);

	echo "
	<li class='court unlocked' data-itt-cid='$newCourt'>
		<div class='title'>
			<span>Court #$newCourt</span>
			<select class='time'> 
				<option data-itt-time='00:00:00' selected='selected'>&#8226;&nbsp; TIME &nbsp;&#8226;</option>
				<option data-itt-time='07:00:00'>7:00 AM</option><option data-itt-time='07:30:00'>7:30 AM</option><option data-itt-time='08:00:00'>8:00 AM</option>
				<option data-itt-time='08:30:00'>8:30 AM</option><option data-itt-time='09:00:00'>9:00 AM</option><option data-itt-time='09:30:00'>9:30 AM</option>
				<option data-itt-time='10:00:00'>10:00 AM</option><option data-itt-time='10:30:00'>10:30 AM</option><option data-itt-time='11:00:00'>11:00 AM</option>
				<option data-itt-time='11:30:00'>11:30 AM</option><option data-itt-time='12:00:00'>12:00 PM</option><option data-itt-time='12:30:00'>12:30 PM</option>
				<option data-itt-time='13:00:00'>1:00 PM</option><option data-itt-time='13:30:00'>1:30 PM</option><option data-itt-time='14:00:00'>2:00 PM</option>
			</select>
		</div>
		<div class='menu'>
			<div class='ballmach'></div>
			<div class='level' data-itt-llevel='' data-itt-ulevel=''></div>
			<div class='players' title='# of players scheduled'>0</div>
			<div class='lock'></div>
		</div>
		<ul class='members_list connectedSortable'>
		</ul>
	</li>";
?>