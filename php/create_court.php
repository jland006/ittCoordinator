<?php

	include 'time_array.php';
	include 'create_member.php';
	include 'create_guest.php';
	
	function create_court(&$cnum = NULL, &$row = NULL, &$resultCourts = NULL) {
	
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
                    <select class='time' $clock>$times</select>
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