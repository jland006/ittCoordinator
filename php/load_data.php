<?php

	include 'connection.php';

	
	if (!isset($_POST['date'])) {
		$date = date("Y-m-d");
	}
	else {
		$date = $_POST['date'];
	}

	$time_array = array(
		"00:00:00" => "&#8226;&nbsp; TIME &nbsp;&#8226;",
		"07:00:00" => "7:00 AM",
		"07:30:00" => "7:30 AM",
		"08:00:00" => "8:00 AM",
		"08:30:00" => "8:30 AM",
		"09:00:00" => "9:00 AM",
		"09:30:00" => "9:30 AM",
		"10:00:00" => "10:00 AM",
		"10:30:00" => "10:30 AM",
		"11:00:00" => "11:00 AM",
		"11:30:00" => "11:30 AM",
		"12:00:00" => "12:00 PM",
		"12:30:00" => "12:30 PM",
		"13:00:00" => "1:00 PM",
		"13:30:00" => "1:30 PM",
		"14:00:00" => "2:00 PM"
	);
	
	//////////////////////////////////////////////////////////////////////////////////////
	$max_num_courts = 13;
	$current_court;
	$court_layout = "";
	
	$queryCourts = "
	SELECT C.cNum, X.numPlayers, CT.cTime, CL.cNum AS locked, CB.cNum AS ballmach, S.id, S.lname, S.fname, S.gender, S.skill, S.phone1, S.phone2, MRT.rTime, MRB.rDate AS ballrequest, MRFC.friends, MRF.friend as friend, F.fname AS friend_fname, F.lname AS friend_lname FROM (
	SELECT cNum FROM court_time WHERE cDate = '$date'
	UNION
	SELECT cNum FROM court_ballmach WHERE cDate = '$date'
	UNION
	SELECT cNum FROM court_locked WHERE cDate = '$date'
	UNION
	SELECT cNum FROM guests_schedule WHERE cDate = '$date'
	UNION
	SELECT cNum FROM members_schedule WHERE cDate = '$date') C
	LEFT JOIN (SELECT Y.cNum, COUNT(*) AS numPlayers 
	FROM (SELECT cNum FROM members_schedule WHERE cDate = '$date'
	UNION ALL
	SELECT cNum FROM guests_schedule WHERE cDate = '$date') Y
	GROUP BY Y.cNum) X
	ON X.cNum = C.cNum
	LEFT JOIN court_time CT
	ON CT.cNum = C.cNum AND CT.cDate = '$date'
	LEFT JOIN court_locked CL 
	ON CL.cNum = C.cNum AND CL.cDate = '$date' 
	LEFT JOIN court_ballmach CB 
	ON CB.cNum = C.cNum AND CB.cDate = '$date'
	LEFT JOIN (SELECT MS.id, MS.cDate, MS.cNum, M.lname, M.fname, M.gender, M.skill, M.phone1, M.phone2 FROM members_schedule MS, members M WHERE MS.id = M.id
	UNION
	SELECT GS.id, GS.cDate, GS.cNum, G.lname, G.fname, G.gender, G.skill, G.phone1, G.phone2 FROM guests_schedule GS, guests G WHERE GS.id = G.id) AS S
	ON S.cNum = C.cNum AND S.cDate = '$date'
	LEFT JOIN members_request_time MRT
	ON MRT.id = S.id AND MRT.rDate = '$date'
	LEFT JOIN members_request_ballmach MRB 
	ON MRB.id = S.id AND MRB.rDate = '$date' 
	LEFT JOIN (SELECT id, COUNT(*) AS friends FROM members_request_friend WHERE rDate = '$date' GROUP BY id) MRFC 
	ON MRFC.id = S.id 
	LEFT JOIN members_request_friend MRF 
	ON MRF.id = S.id AND MRF.rDate = '$date'
	LEFT JOIN (SELECT id, fname, lname FROM members UNION SELECT id, fname, lname FROM guests) F
	ON F.id = MRF.friend  
	ORDER BY LENGTH(C.cNum), cNum, S.lname, S.fname, F.lname, F.fname";
	
	$resultCourts = $conn->query($queryCourts);
	$row_cnt = $resultCourts->num_rows;
	
	if ($row_cnt) {
		$row = $resultCourts->fetch_assoc();
		$current_court = $row["cNum"];
		for ($i = 1; $i <= $max_num_courts; $i++) {	
			if(strcmp($current_court, $i) == 0) {
				$court_layout.=create_court($row, $resultCourts, $current_court);
				$row = $resultCourts->fetch_assoc();
				$current_court = $row["cNum"];
			} else {
				$court_layout.=create_court_empty($i);
			}
		}

		/*while($row = $resultCourts->fetch_assoc()) {
			$court_layout.=create_court($row, $resultCourts, $current_court);
		}*/
		if ($row["cNum"]) {
			do {
				$court_layout.=create_court($row, $resultCourts, $current_court);
			} while($row = $resultCourts->fetch_assoc());
		}
	} else {
		for ($i = 1; $i <= $max_num_courts; $i++) {
			$court_layout.=create_court_empty($i);
		}		
	}

	//////////////////////////////////////////////////////////////////////////////////////
	
	$waitingList = "";
	
	$queryWaiting = "
	SELECT R.id, R.lname, R.fname, R.gender, R.skill, R.phone1, R.phone2, MRT.rTime, MRB.rDate AS ballrequest, MRFC.friends, MRF.friend as friend, F.fname AS friend_fname, F.lname AS friend_lname FROM(
	SELECT MR.id, MR.rDate, M.lname, M.fname, M.gender, M.skill, M.phone1, M.phone2 FROM members_request MR, members M WHERE MR.id = M.id AND MR.rDate = '$date'
	UNION
	SELECT GR.id, GR.rDate, G.lname, G.fname, G.gender, G.skill, G.phone1, G.phone2 FROM guests_request GR, guests G WHERE GR.id = G.id AND GR.rDate = '$date') R
	LEFT JOIN members_request_time MRT 
	ON MRT.id = R.id  AND MRT.rDate = '$date' 
	LEFT JOIN members_request_ballmach MRB 
	ON MRB.id = R.id AND MRB.rDate = '$date' 
	LEFT JOIN (SELECT id, COUNT(*) AS friends FROM members_request_friend WHERE rDate = '$date' GROUP BY id) MRFC 
	ON MRFC.id = R.id 
	LEFT JOIN (SELECT * FROM members_request_friend WHERE rDate = '$date') MRF 
	ON MRF.id = R.id
	LEFT JOIN (SELECT id, fname, lname FROM members UNION SELECT id, fname, lname FROM guests) F
	ON F.id = MRF.friend  
	WHERE R.id
	NOT IN (SELECT id FROM members_schedule WHERE cDate='$date' UNION SELECT id FROM guests_schedule WHERE cDate='$date') 
	ORDER BY R.lname, R.fname, F.lname, F.fname";

	$resultWaiting = $conn->query($queryWaiting);

	while($row = $resultWaiting->fetch_assoc()) {
		$skill = $row["skill"];
		if (empty($skill)) {
			$waitingList .= create_guest($row);
		}
		else {
			$waitingList .= create_member($row, $resultWaiting, true);
		}
	}
	
	//////////////////////////////////////////////////////////////////////////////////////
	
	list($year, $month, $day) = explode('-', $date);
	
	$allmembersList = "";
	
	$date_left = date("Y-m-d", mktime(0, 0, 0, (int)$month  , (int)$day-20, (int)$year));
	$date_new_left = date("Y-m-d", mktime(0, 0, 0, (int)$month  , (int)$day-5, (int)$year));
	$date_new_right = date("Y-m-d", mktime(0, 0, 0, (int)$month  , (int)$day+2, (int)$year));
	
	
	$queryNewMembers = "
	SELECT * FROM members M WHERE M.createdAt BETWEEN '$date_new_left' AND '$date_new_right' AND M.id IN (
	SELECT MA.id FROM members_active MA 
	WHERE MA.id 
	NOT IN (SELECT id FROM members_schedule WHERE cDate='$date' 
	UNION
	SELECT id FROM members_request WHERE rDate='$date'))
	ORDER BY M.lname, M.fname";
	
	$resultNewMembers = $conn->query($queryNewMembers);
	$row_cnt = $resultNewMembers->num_rows;

	if ($row_cnt) {
		$allmembersList .= "
			<li class='divider_info nomove'>
                <div class='name'>&#8212;&#8212;&#8212;&#8212;&#8212; NEW MEMBERS &#8212;&#8212;&#8212;&#8212;&#8212;</div>
            </li>";
	}
	
	while($row = $resultNewMembers->fetch_assoc()) {
		$allmembersList .= create_member($row, $resultNewMembers, false);
	}
	
	
	
	$queryRegulars = "
	SELECT * FROM members M WHERE M.id IN (
	SELECT MA.id FROM members_active MA, (SELECT id FROM members_schedule WHERE cDate BETWEEN '$date_left' AND '$date') MS
	WHERE MA.id = MS.id AND MA.id 
	NOT IN (SELECT id FROM members_schedule WHERE cDate='$date' 
	UNION
	SELECT id FROM members_request WHERE rDate='$date'
	UNION
	SELECT id FROM members WHERE createdAt BETWEEN '$date_new_left' AND '$date_new_right'))
	ORDER BY M.lname, M.fname";
	
	$resultRegulars = $conn->query($queryRegulars);
	$row_cnt = $resultRegulars->num_rows;
	
	if ($row_cnt) {
		$allmembersList .= "
			<li class='divider_info nomove'>
                <div class='name'>&#8212;&#8212;&#8212;&#8212;&#8212;&#8212; REGULARS &#8212;&#8212;&#8212;&#8212;&#8212;&#8212;</div>
            </li>";
	}
	
	while($row = $resultRegulars->fetch_assoc()) {
		$allmembersList .= create_member($row, $resultRegulars, false);
	}
	
	
	$queryNonRegulars = "
	SELECT * FROM members M WHERE M.id IN (SELECT MA.id FROM members_active MA 
	WHERE MA.id 
	NOT IN (SELECT id FROM members_schedule WHERE cDate BETWEEN '$date_left' AND '$date' 
	UNION
	SELECT id FROM members_request WHERE rDate='$date'
	UNION
	SELECT id FROM members WHERE createdAt BETWEEN '$date_new_left' AND '$date_new_right'))
	ORDER BY M.lname, M.fname";
	$resultNonRegulars = $conn->query($queryNonRegulars);
	$row_cnt = $resultNonRegulars->num_rows;
	
	if ($row_cnt) {
		$allmembersList .= "
			<li class='divider_info nomove'>
                <div class='name'>&#8212;&#8212;&#8212;&#8212;&#8212; NON REGULARS &#8212;&#8212;&#8212;&#8212;&#8212;</div>
            </li>";
	}
	
	while($row = $resultNonRegulars->fetch_assoc()) {
		$allmembersList .= create_member($row, $resultNonRegulars, false);
	}
	
	//////////////////////////////////////////////////////////////////////////////////////
	
	$allguestsList = "";
	
	$queryGuests = "
	SELECT * FROM guests G 
	WHERE G.id 
	NOT IN (SELECT id FROM guests_schedule WHERE cDate='$date' 
	UNION
	SELECT id FROM guests_request WHERE rDate='$date')
	ORDER BY G.lname, G.fname";
	$resultGuests = $conn->query($queryGuests);
	
	while($row = $resultGuests->fetch_assoc()) {
		$allguestsList .= create_guest($row);
	}
	
	
	//////////////////////////////////////////////////////////////////////////////////////
	
	echo json_encode(array(
		"court_layout" => $court_layout,
		"waiting_list" => $waitingList,
		"all_members" => $allmembersList,
		"all_guests" => $allguestsList
		));
	exit;
	
	function create_court_empty($cnum) {
		$data = "
			<li class='court unlocked' data-itt-cid='$cnum'>
                <div class='title'>
                    <span>Court #$cnum</span>
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
		return $data;
	}
	
	function create_court(&$row, &$resultCourts, &$current_court) {

		global $time_array;		
		$cnum = $row["cNum"];
		$numPlayers = $row["numPlayers"];
		$cTime = $row["cTime"];
		$skill = $row["skill"];
		$locked = "unlocked";
		$clock = "";
		$levels = "";
		
		$l = "";
		$u = "";
		
		if ($row["locked"]) {
			$locked = "locked";
			$clock = "disabled";
		}
		
		$withBall = "";
		
		if ($row["ballmach"]) {
			$withBall = "withBall";
		}
		$data = "
			<li class='court $locked' data-itt-cid='$cnum'>
                <div class='title'>
                    <span>Court #$cnum</span>
                    <select class='time' $clock>";
		
	
		foreach ($time_array as $key => $value) {
			if($cTime == $key) {
				$data .= "<option data-itt-time='$key' selected='selected'>$value</option>";
			}
			else {
				$data .= "<option data-itt-time='$key'>$value</option>";
			}
		}
					
		$data .= "
				</select>
			</div>";		
        
		$temp = "
                <ul class='members_list connectedSortable'>";
				$skill = $row["skill"];
				$id = $row["id"];
				if (empty($skill) && $id) {
					$temp .= create_guest($row);
				}
				else if($id) {
					if (lte($skill, $l) || $l == "")
						$l = $skill;
									
					if (lte($u, $skill))
						$u = $skill;
						
					$temp .= create_member($row, $resultCourts, true);
				}
				for ($i = 1; $i < $numPlayers; $i++) {
					$row = $resultCourts->fetch_assoc();
					$skill = $row["skill"];
					$id = $row["id"];
					if (empty($skill) && $id) {
						$temp .= create_guest($row);
					}
					else if($id) {
						if (lte($skill, $l) || $l == "")
							$l = $skill;
									
						if (lte($u, $skill))
							$u = $skill;
							
						$temp .= create_member($row, $resultCourts, true);
					}
				}
				
		if ($l != "" && $u!="") {
			$levels = "$l : $u";
		}
		
		if ($numPlayers == NULL)
			$numPlayers = 0;
			
		$data .= "

		
				<div class='menu'>
                	<div class='ballmach $withBall'></div>
            		<div class='level' data-itt-llevel='$l' data-itt-ulevel='$u'>$levels</div>
            		<div class='players' title='# of players scheduled'>$numPlayers</div>
            		<div class='lock'></div>
        		</div>";
				
		$data .= $temp."
				</ul>
			</li>";

		return $data;
	}
	function create_member($row, &$result, $withReq) {
		global $time_array;	
		$id = $row["id"];
		$skill = $row["skill"];
		$lname = ucfirst($row["lname"]);
		$fname = ucfirst($row["fname"]);
		$phone1 = $row["phone1"];
		$phone2 = $row["phone2"];
		
		$filename = "../members/$id/profile_sm.jpg";

		if (file_exists($filename)) {
			$filename = "members/$id/profile_sm.jpg";
		}
		else {
			$filename = "images/member_sm.png";
		}
		
		$data = "
			<li class='member_info' data-itt-id='$id' data-itt-skill='$skill' title='$fname $lname&#013;Ph: $phone1&#013;Ph: $phone2' data-itt-phone='$phone1'>
				<img class='profile_pic' src='$filename' alt width='24' height='30'>
				<div class='name'>$fname $lname</div>";
		
		if ($withReq) {
			$friends = $row["friends"];
			$rTime = $row["rTime"];
			$requestStr = "Requests to play with:";
			
			if ($row["ballrequest"]) {		
				$data .= "<div class='ballmach'></div>";
			}
			if ($friends) {
				$friend_fname = ucfirst($row["friend_fname"]);
				$friend_lname = ucfirst($row["friend_lname"]);
				$requestStr .= "&#013$friend_fname $friend_lname";
				for ($i = 1; $i < $friends; $i++) {
					$row = $result->fetch_assoc();
					$friend_fname = ucfirst($row["friend_fname"]);
					$friend_lname = ucfirst($row["friend_lname"]);
					$requestStr .= "&#013$friend_fname $friend_lname";	
				}
				$data .= "		
					<div class='raquet' title='$requestStr'></div>
					<div class='blue_box' title='$requestStr'>$friends</div>";
			}
			
			if ($rTime) {
				$data .= "<div class='watch' title='$time_array[$rTime]'></div>";
			}
		}
		
		$data .= "
			</li>";
		return $data;
	}
	function create_guest($row) {
		$id = $row["id"];
		$fname = ucfirst($row["fname"]);
		$lname = ucfirst($row["lname"]);
		$phone1 = $row["phone1"];
		$phone2 = $row["phone2"];
		$data = "
			<li class='guest_info' data-itt-id='$id' data-itt-skill='' title='$fname $lname&#013;Ph: $phone1' data-itt-phone='$phone1'>
            	<img class='profile_pic' src='images/guest_sm.png' alt width='24' height='30'>
                <div class='name'>$fname $lname</div>
                <div class='guest_logo'>GUEST</div>
            </li>";
		return $data;
	}
	
	function lte($level1, $level2) {
		if ($level1 == $level2)
			return true;
			
		$s1 = substr($level1,0,3);
		$s2 = substr($level2,0,3);
		
		if ($s1 != $s2)
			return ($s1 < $s2);
			
		$s1 = substr($level1,3,1);
		$s2 = substr($level2,3,1);
		
		return ($s1 == "-" || $s2 == "+");
	}
	
?>