<?php

	include 'time_array.php';
	include 'create_member.php';
	include 'create_guest.php';
	
	function create_court(&$cnum = NULL, &$row = NULL, &$resultCourts = NULL) {
	global $time_array;
		$locked = "unlocked";
		$lower_level = "";
		$upper_level = "";
		$clock = "";
		$levels = "";
		$numPlayers = 0;
		$ctime = "";
		$withBall = "";
		$members = "";
		
		if ($row && $resultCourts) {
			$current_court = $cnum;
			$cnum = $row["cNum"];
			$ctime = $row["cTime"];
			$skill = $row["skill"];
			
			if ($row["locked"]) {
				$locked = "locked";
				$clock = "disabled";
			}
			
			if ($row["ballmach"]) {
				$withBall = "withBall";
			}
			
			if ($row["numPlayers"]) {
				$numPlayers = $row["numPlayers"];
				$skill = $row["skill"];
				if (!empty($skill)) {
					get_levels($lower_level, $upper_level, $skill);
					$members .= create_member($row, $resultCourts);
				}
				else {
					$members .= create_guest($row, true);
				}
				for ($i = 1; $i < $numPlayers; $i++) {
					$row = $resultCourts->fetch_assoc();
					$skill = $row["skill"];
					if (!empty($skill)) {
						get_levels($lower_level, $upper_level, $skill);
						$members .= create_member($row, $resultCourts);
					}
					else {
						$members .= create_guest($row, true);
					}
				}
			}
			
			if (!empty($lower_level) && !empty($upper_level)) {
				$levels = "$lower_level : $upper_level";
			}
		}
		
		$times = create_times($ctime);

		$data = "
			<li class='court $locked' data-itt-cid='$cnum'>
                <div class='title'>
                    <span>Court #$cnum</span>
                    <div class='time' data-itt-time='$ctime'>
						<p class='time_view'>$time_array[$ctime]</p>
						<ul class='times'>
						  <li><p data-itt-time='00:00:00'>&#8226;&nbsp; TIME &nbsp;&#8226;</p></li>
						  <li><p data-itt-time='07:00:00'>7:00 AM</p>
							<ul><li><p data-itt-time='07:15:00'>7:15 AM</p></li><li><p data-itt-time='07:30:00'>7:30 AM</p></li><li><p data-itt-time='07:45:00'>7:45 AM</p></li></ul>
						  </li>
						  <li><p data-itt-time='08:00:00'>8:00 AM</p>
							<ul><li><p data-itt-time='08:15:00'>8:15 AM</p></li><li><p data-itt-time='08:30:00'>8:30 AM</p></li><li><p data-itt-time='08:45:00'>8:45 AM</p></li></ul>
						  </li>
						  <li><p data-itt-time='09:00:00'>9:00 AM</p>
							<ul><li><p data-itt-time='09:15:00'>9:15 AM</p></li><li><p data-itt-time='09:30:00'>9:30 AM</p></li><li><p data-itt-time='09:45:00'>9:45 AM</p></li></ul>
						  </li>
						  <li><p data-itt-time='10:00:00'>10:00 AM</p>
							<ul><li><p data-itt-time='10:15:00'>10:15 AM</p></li><li><p data-itt-time='10:30:00'>10:30 AM</p></li><li><p data-itt-time='10:45:00'>10:45 AM</p></li></ul>
						  </li>
						  <li><p data-itt-time='11:00:00'>11:00 AM</p>
							<ul><li><p data-itt-time='11:15:00'>11:15 AM</p></li><li><p data-itt-time='11:30:00'>11:30 AM</p></li><li><p data-itt-time='11:45:00'>11:45 AM</p></li></ul>
						  </li>
						  <li><p data-itt-time='12:00:00'>12:00 PM</p>
							<ul><li><p data-itt-time='12:15:00'>12:15 PM</p></li><li><p data-itt-time='12:30:00'>12:30 PM</p></li><li><p data-itt-time='12:45:00'>12:45 AM</p></li></ul>
						  </li>
						  <li data-itt-time='13:00:00'><p>1:00 PM</p>
							<ul><li><p data-itt-time='13:15:00'>1:15 PM</p></li><li><p data-itt-time='13:30:00'>1:30 PM</p></li><li><p data-itt-time='13:45:00'>1:45 AM</p></li></ul>
						  </li>
						  <li><p data-itt-time='14:00:00'>2:00 PM</p>
							<ul><li><p data-itt-time='14:15:00'>2:15 PM</p></li><li><p data-itt-time='14:30:00'>2:30 PM</p></li><li><p data-itt-time='14:45:00'>2:45 AM</p></li></ul>
						  </li>
						</ul>
					</div>
                </div>
				<div class='menu'>
                	<div class='ballmach $withBall'></div>
            		<div class='level' data-itt-llevel='$lower_level' data-itt-ulevel='$upper_level'>$levels</div>
            		<div class='players' title='# of players scheduled'>$numPlayers</div>
            		<div class='lock'></div>
        		</div>
                <ul class='members_list connectedSortable'>$members</ul>
			</li>";
		return $data;
	}
	
	function create_times($ctime) {
		global $time_array;
		$data = "";
		
		foreach ($time_array as $key => $value) {
			$selected = "";
			if($ctime == $key)
				$selected = "selected='selected'";
			$data .= "<option data-itt-time='$key' $selected>$value</option>";
		}
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
	
	function get_levels(&$lower_level, &$upper_level, $skill) {
		if (lte($skill, $lower_level) || $lower_level == "")
			$lower_level = $skill;
						
		if (lte($upper_level, $skill))
			$upper_level = $skill;
	}

?>