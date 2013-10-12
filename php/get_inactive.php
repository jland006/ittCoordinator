<?php

	include 'connection.php';

	$query = "SELECT id, lname, fname FROM members WHERE id NOT IN (SELECT * FROM members_active) ORDER BY lname, fname;";
	
	$resultQuery = $conn->query($query);
	
	$output = "
		<style>
		.reactivate {
			height: 107px;
			width: 208px;
			background-color: #7DC24B;
			padding: 10px;
			border: 2px solid #ddd;
		}
		#the_inactive {
			text-transform: capitalize;
		}
		</style>
		<script>
		$(function() {
			$('.reactivate .activate').on('click', function() {
				var id = $('option:selected', '.reactivate #the_inactive').attr('data-itt-id');
				$.post('php/activate.php', {id:id}, function(output) {
					$('#content .all_members').prepend(output);
				});
				$('.reveal-modal2.reactivate').trigger('reveal:close').remove();
			});
			$('.close-reveal-modal').on('click', function() {
				$('.reveal-modal2.reactivate').trigger('reveal:close').remove();
			});
		});
		</script>
		<div id='container'>
			<p>Member Name</p>
			<select id='the_inactive' style='width: 175px;'>";
			
				while($row = $resultQuery->fetch_assoc()) {
						$id = $row['id'];
						$fname = $row['fname'];
						$lname = $row['lname'];
						$output .= "
							<option data-itt-id='$id'>$fname $lname</option>";
				}
				
	$output .= "			
			</select>
			<br>
			<input type='button' class='activate' value='Reactivate'>
		</div>
		<a class='close-reveal-modal' style='top: 11px;'>&#215;</a>";
	echo $output;

?>