<?php

	include 'connection.php';
	include 'create_court.php';

	if (!isset($_POST['date'])) {
		$date = date("Y-m-d");
	}
	else {
		$date = $_POST['date'];
	}
	
	//////////////////////////////////////////////////////////////////////////////////////
	$max_num_courts = 13;
	$current_court;
	$court_layout = "";
	
	$queryCourts = "
	SELECT C.cNum, X.numPlayers, CT.cTime, CL.cNum AS locked, CB.cNum AS ballmach, S.id, S.lname, S.fname, S.gender, S.skill, S.phone1, S.phone2, A.id AS arrived, P.called, MRT.rTime, MRB.rDate AS ballrequest, MRFC.friends, MRF.friend as friend, F.fname AS friend_fname, F.lname AS friend_lname FROM (
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
	LEFT JOIN (SELECT id FROM members_arrived WHERE cDate = '$date'
	UNION
	SELECT id FROM guests_arrived WHERE cDate = '$date') AS A
	ON A.id = S.id
	LEFT JOIN (SELECT * FROM members_called WHERE cDate = '$date'
	UNION
	SELECT * FROM guests_called WHERE cDate = '$date') AS P
	ON P.id = S.id
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
				$court_layout.=create_court($current_court, $row, $resultCourts);
				$row = $resultCourts->fetch_assoc();
				$current_court = $row["cNum"];
			} else {
				$court_layout.=create_court($i);
			}
		}
		if ($row["cNum"]) {
			do {
				$court_layout.=create_court($current_court, $row, $resultCourts);
			} while($row = $resultCourts->fetch_assoc());
		}
	} else {
		for ($i = 1; $i <= $max_num_courts; $i++) {
			$court_layout.=create_court($i);
		}		
	}

//////////////////////////////////////////////////////////////////////////////////////
	
	$waitingList = "";
	
	$queryWaiting = "
	SELECT R.id, R.lname, R.fname, R.gender, R.skill, R.phone1, R.phone2, A.id AS arrived, P.called, MRT.rTime, MRB.rDate AS ballrequest, MRFC.friends, MRF.friend as friend, F.fname AS friend_fname, F.lname AS friend_lname FROM(
	SELECT MR.id, MR.rDate, M.lname, M.fname, M.gender, M.skill, M.phone1, M.phone2 FROM members_request MR, members M WHERE MR.id = M.id AND MR.rDate = '$date'
	UNION
	SELECT GR.id, GR.rDate, G.lname, G.fname, G.gender, G.skill, G.phone1, G.phone2 FROM guests_request GR, guests G WHERE GR.id = G.id AND GR.rDate = '$date') R
	LEFT JOIN (SELECT id FROM members_arrived WHERE cDate = '$date'
	UNION
	SELECT id FROM guests_arrived WHERE cDate = '$date') AS A
	ON A.id = R.id
	LEFT JOIN (SELECT * FROM members_called WHERE cDate = '$date'
	UNION
	SELECT * FROM guests_called WHERE cDate = '$date') AS P
	ON P.id = R.id
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
			$waitingList .= create_member($row, $resultWaiting);
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
		$allmembersList .= create_member($row);
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
		$allmembersList .= create_member($row);
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
		$allmembersList .= create_member($row);
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
	

	
?>