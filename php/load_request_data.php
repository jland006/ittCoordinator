<?php

	include 'connection.php';

	$date = date("Y-m-d");
	$player_id = $_POST["id"];
	
	$friend_added = false;

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
	$profile_layout = "";
	
	$queryProfile = "SELECT * FROM members M, (SELECT COUNT(*) AS times_played 
	FROM members_schedule WHERE id='$player_id') MS
	WHERE M.id = '$player_id' LIMIT 1";
	
	$resultProfile = $conn->query($queryProfile);
	$row = $resultProfile->fetch_assoc();
	$player_fname = $row["fname"];
	$player_lname = $row["lname"];
	
	$profile_layout .= create_profile($row);

	//////////////////////////////////////////////////////////////////////////////////////
	
	$max_num_courts = 12;
	$current_date;
	$court_layout = "";

	$queryCourts = "
	SELECT D.rDate, MRT.rTime, MRB.rDate AS ballmach, MRF.friend as id, F.fname, F.lname, F.skill FROM(
	SELECT rDate FROM members_request WHERE id='$player_id' AND rDate >= '$date'
	UNION
	SELECT cDate AS rDate FROM members_schedule WHERE id='$player_id' AND cDate >= '$date') D
	LEFT JOIN members_request_time MRT
	ON MRT.id = '$player_id' AND MRT.rDate = D.rDate 
	LEFT JOIN members_request_ballmach AS MRB
	ON MRB.id = '$player_id' AND MRB.rDate = D.rDate 
	LEFT JOIN members_request_friend MRF 
	ON MRF.id = '$player_id' AND MRF.rDate = D.rDate
	LEFT JOIN (SELECT id, fname, lname, skill FROM members UNION SELECT id, fname, lname, skill FROM guests) F
	ON F.id = MRF.friend
	ORDER BY D.rDate, F.lname, F.fname";
	
	$resultCourts = $conn->query($queryCourts);
	$row_cnt = $resultCourts->num_rows;

	if ($row_cnt) {
		$row = $resultCourts->fetch_assoc();
		$current_date = $row["rDate"];
		
		for ($i = 0; $i < $max_num_courts; $i++) {
			$tomorrow = mktime(0,0,0,date("m"),date("d")+$i,date("Y"));
			$court_date_sm = date("Y-m-d", $tomorrow);
			$court_date_lg = date("D. F j", $tomorrow);
			if ($current_date == $court_date_sm) {
				$court_layout.=create_court($row, $resultCourts, $current_date, $court_date_sm, $court_date_lg);
				if ($friend_added) {
					$friend_added = false;
				}
				else {
					$row = $resultCourts->fetch_assoc();
					$current_date = $row["rDate"];
				}
			}
			else {
				$court_layout .= create_court_empty($court_date_sm, $court_date_lg);
			}
		}
	}
	else {
		for ($i = 0; $i < $max_num_courts; $i++) {
			$tomorrow = mktime(0,0,0,date("m"),date("d")+$i,date("Y"));
			$court_date_sm = date("Y-m-d", $tomorrow);
			$court_date_lg = date("D. F j", $tomorrow);
			$court_layout .= create_court_empty($court_date_sm, $court_date_lg);
		}	
	}

	
	//////////////////////////////////////////////////////////////////////////////////////
	
	$allguestsList = "";
	
	$queryGuests = "SELECT * FROM guests ORDER BY lname, fname";
	$resultGuests = $conn->query($queryGuests);
	
	while($row = $resultGuests->fetch_assoc()) {
		$allguestsList .= create_guest($row);
	}
	
	
	//////////////////////////////////////////////////////////////////////////////////////
	
	$allmembersList = "";
	
	$queryRegulars = "
	SELECT M.id, M.lname, M.fname
	FROM members M, members_active MA, (
	SELECT DISTINCT MS2.id FROM (
	SELECT cNum, cDate 
	FROM members_schedule 
	WHERE id='$player_id') MS1 
	LEFT JOIN members_schedule MS2
	ON MS2.cNum = MS1.cNum AND MS2.cDate = MS1.cDate) X
	WHERE X.id <> '$player_id' AND X.id = MA.id AND X.id = M.id AND X.id 
	NOT IN (SELECT enemy AS id 
	FROM members_blklist 
	WHERE id = '$player_id')
	ORDER BY M.lname, M.fname";
	
	$resultRegulars = $conn->query($queryRegulars);
	$row_cnt = $resultRegulars->num_rows;
	
	if ($row_cnt) {
		$allmembersList .= "
			<li class='divider_info nomove'>
                <div class='name'>&#8212;&#8212;&#8212;&#8212; REGULARS &#8212;&#8212;&#8212;&#8212;</div>
            </li>";
	}	
	
	while($row = $resultRegulars->fetch_assoc()) {
		$allmembersList .= create_member($row);
	}
	
	$queryNonRegulars = "
	SELECT MA.id, M.lname, M.fname FROM members_active MA
	LEFT JOIN members M
	ON M.id = MA.id
	WHERE MA.id NOT IN (
	SELECT DISTINCT MS2.id FROM (
	SELECT cNum, cDate 
	FROM members_schedule 
	WHERE id='$player_id') MS1 
	LEFT JOIN members_schedule MS2
	ON MS2.cNum = MS1.cNum AND MS2.cDate = MS1.cDate
	UNION
	SELECT enemy AS id 
	FROM members_blklist 
	WHERE id = '$player_id')
	ORDER BY M.lname, M.fname";
	
	$resultNonRegulars = $conn->query($queryNonRegulars);
	$row_cnt = $resultNonRegulars->num_rows;
	
	if ($row_cnt) {
		$allmembersList .= "
			<li class='divider_info nomove'>
                <div class='name'>&#8212;&#8212;&#8212; NON REGULARS &#8212;&#8212;&#8212;</div>
            </li>";
	}
	
	while($row = $resultNonRegulars->fetch_assoc()) {
		$allmembersList .= create_member($row);
	}
	
	//////////////////////////////////////////////////////////////////////////////////////
	
	$allBlackList = "";
	
	$queryBlackList = "SELECT M.id, M.lname, M.fname FROM members_blklist MB, members M WHERE MB.id = '$player_id' AND M.id = MB.enemy ORDER BY M.lname, M.fname";
	
	$resultBlackList = $conn->query($queryBlackList);
	
	while($row = $resultBlackList->fetch_assoc()) {
		$allBlackList .= create_member($row);
	}
	
	//////////////////////////////////////////////////////////////////////////////////////
	
	echo json_encode(array(
		"profile_layout" => $profile_layout,
		"court_layout" => $court_layout,
		"all_guests" => $allguestsList,
		"all_members" => $allmembersList,
		"bl_members" => $allBlackList
	));
	exit;
	
	function create_profile($row) {
		$id = $row["id"];
		$fname = $row["fname"];
		$lname = $row["lname"];
		$phone1 = $row["phone1"];
		$phone2 = $row["phone2"];
		$email = $row["email"];
		$skill = $row["skill"];
		$createdAt = $row["createdAt"];
		$times_played = $row["times_played"];
		$member_since = date("F Y", strtotime($createdAt));
		
		$level_array = array("2.0-", "2.0", "2.0+", "2.5-", "2.5", "2.5+",
							"3.0-", "3.0", "3.0+", "3.5-", "3.5", "3.5+",
							"4.0-", "4.0", "4.0+", "4.5-", "4.5", "4.5+"
		);
	
		$filename = "../members/$id/profile_lg.jpg";

		if (file_exists($filename)) {
			$filename = "members/$id/profile_lg.jpg";
		}
		else {
			$filename = "images/member_lg.png";
		}
		
		$data = "
			<input type='hidden' id='player_id' value='$id' />
			<p id='fname'>$fname</p>
            <p id='fname'>$lname</p>
            <br>
            <img style='margin-left: 36px;' src='$filename' alt width='144' height='180'>
            <br>
            <span>Ph</span><input class='phone' type='text' value='$phone1' maxlength='10' />
            <br>
            <span>Ph</span><input class = 'phone' type='text' value='$phone2' maxlength='10' />
            <br>
            <span>E-mail</span><input type='text' value='$email' />
            <br>
            <span>Level:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
            <select id='levels'>";
			
			foreach ($level_array as $key => $value) {
				if($skill == $value) {
					$data .= "<option selected='selected'>$value</option>";
				}
				else {
					$data .= "<option>$value</option>";
				}
			}	

		$data .= "
                </select>

            <p>Members since $member_since</p>
            <p>Stats</p>
            <p>Matches Played: $times_played</p>

            <input id='deactivate' type='button' value='Deactivate' />";
			
		return $data;
	}
	
	function create_court_empty($court_date_sm, $court_date_lg) {
		global $player_fname, $player_lname, $player_id;
		
		$filename = "../members/$player_id/profile_sm.jpg";

		if (file_exists($filename)) {
			$filename = "members/$player_id/profile_sm.jpg";
		}
		else {
			$filename = "images/member_sm.png";
		}
		$data = "
			<li class='court_request' data-itt-sdate='$court_date_sm'>
                <div class='title'  data-itt-ldate='$court_date_lg'>
                    <span>$court_date_lg</span>
				</div>
                <div class='ball_request'>Request</div>
				<div class='menu' style='display: none;'>
                	<div class='ballmach'></div>
					<select class='time'> 
						<option data-itt-time='00:00:00' selected='selected'>&#8226;&nbsp; TIME &nbsp;&#8226;</option>
						<option data-itt-time='07:00:00'>7:00 AM</option><option data-itt-time='07:30:00'>7:30 AM</option><option data-itt-time='08:00:00'>8:00 AM</option>
						<option data-itt-time='08:30:00'>8:30 AM</option><option data-itt-time='09:00:00'>9:00 AM</option><option data-itt-time='09:30:00'>9:30 AM</option>
						<option data-itt-time='10:00:00'>10:00 AM</option><option data-itt-time='10:30:00'>10:30 AM</option><option data-itt-time='11:00:00'>11:00 AM</option>
						<option data-itt-time='11:30:00'>11:30 AM</option><option data-itt-time='12:00:00'>12:00 PM</option><option data-itt-time='12:30:00'>12:30 PM</option>
						<option data-itt-time='13:00:00'>1:00 PM</option><option data-itt-time='13:30:00'>1:30 PM</option><option data-itt-time='14:00:00'>2:00 PM</option>
					</select>
            		<div class='players' title='# of players scheduled'>1</div>
        		</div>
                <ul class='members_list allow_members allow_guests'>
					<li class='member_info nomove' data-itt-id='$player_id'>
                            <img class='profile_pic' src='$filename' alt width='24' height='30'>
                            <div class='name'>$player_fname $player_lname</div>
					</li>
				</ul>
			</li>";
		return $data;
	}
	
	function create_court(&$row, &$resultCourts, &$current_date, $court_date_sm, $court_date_lg) {

		global $time_array;		
		global $player_fname, $player_lname, $player_id, $friend_added;
		
		$rDate = $row["rDate"];
		$rTime = $row["rTime"];
		
		$withBall = "";
		
		if ($row["ballmach"]) {
			$withBall = "withBall";
		}
		
		$filename = "../members/$player_id/profile_sm.jpg";

		if (file_exists($filename)) {
			$filename = "members/$player_id/profile_sm.jpg";
		}
		else {
			$filename = "images/member_sm.png";
		}
		
		$data = "
			<li class='court_request' data-itt-sdate='$court_date_sm'>
                <div class='title requested'  data-itt-ldate='$court_date_lg'>
                    <span>$court_date_lg</span>
				</div>
                <div class='ball_request' style='display: none;'>Request</div>
				<div class='menu'>
                	<div class='ballmach $withBall'></div>
					<select class='time'>";
					
		foreach ($time_array as $key => $value) {
			if($rTime == $key) {
				$data .= "<option data-itt-time='$key' selected='selected'>$value</option>";
			}
			else {
				$data .= "<option data-itt-time='$key'>$value</option>";
			}
		}			

		$data .= "
					</select>
            		<div class='players' title='# of players scheduled'>1</div>
        		</div>
                <ul class='members_list allow_members allow_guests'>
					<li class='member_info nomove' data-itt-id='$player_id'>
                            <img class='profile_pic' src='$filename' alt width='24' height='30'>
                            <div class='name'>$player_fname $player_lname</div>
					</li>";
					
		$friend = $row["id"];
		
		if ($friend) {
			$friend_added = true;
			do {
				$current_date = $row["rDate"];
				if($rDate == $current_date) {
					$skill = $row["skill"];
					if ($skill) {
						$data .= create_member($row);
					}
					else {
						$data .= create_guest($row);
					}
				}
				else {
					break;
				}
			} while($row = $resultCourts->fetch_assoc());
		}
		
		
					
		$data .= "			
				</ul>
			</li>";
		return $data;
	}
	function create_member($row) {
		$id = $row["id"];
		$lname = $row["lname"];
		$fname = $row["fname"];
	
		$filename = "../members/$id/profile_sm.jpg";

		if (file_exists($filename)) {
			$filename = "members/$id/profile_sm.jpg";
		}
		else {
			$filename = "images/member_sm.png";
		}
		
		$data = "
			<li class='member_info' data-itt-id='$id'>
				<img class='profile_pic' src='$filename' alt width='24' height='30'>
				<div class='name'>$fname $lname</div>
			</li>";
		return $data;
	}
	function create_guest($row) {
		$id = $row["id"];
		$fname = $row["fname"];
		$lname = $row["lname"];

		$data = "
			<li class='guest_info' data-itt-id='$id'>
            	<img class='profile_pic' src='images/guest_sm.png' alt width='24' height='30'>
                <div class='name'>$fname $lname</div>
            </li>";
		return $data;
	}
	
?>