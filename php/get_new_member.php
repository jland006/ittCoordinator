<?php
	
	$output = "
		<style>
		.new_member {
			height: 414px;
			width: 242px;
			background-color: #7DC24B;
			padding: 10px;
			border: 2px solid #ddd;
		}
		</style>
		<script>
		$(function() {
			$('.new_member .clear').on('click', function() {
				$(this).siblings('input:text').val('');
			});
			$('.new_member .add').on('click', function() {
				var data = new Array();
				$('.new_member input:text, .new_member input:radio:checked, .new_member select').each(function(){
					data.push($(this).val());
				});
				data = JSON.stringify(data);

				$.post('php/new_member.php', {data: data}, function(output) {
					if (output.length > 1) {
						$('#content .all_members').prepend(output[1]);
						$('.reveal-modal2.new_member').trigger('reveal:close').remove();
					}
					alert(output[0]);
				},'json');
			});
			$('.close-reveal-modal').on('click', function() {
				$('.reveal-modal2.new_member').trigger('reveal:close').remove();
			});
		});
		</script>
		<div id='container'>
				<p>* Last Name</p>
				<input type='text' placeholder='Last Name'>
				
				<p>* First Name</p>
				<input type='text' placeholder='First Name'>
				<p>* Primary Phone</p>
				<input type='text' placeholder='ex: 760123456377' maxlength='10'>
				<p>Secondary Phone</p>
				<input type='text' placeholder='ex: 760123456377' maxlength='10'>
				<p>E-mail</p>
				<input type='text' placeholder='E-mail'>
				<p>* Gender</p>
				<input type='radio' id='male' name='gender' value='1' checked='checked' />
				<span style='margin-right: 20px;'>Male</span>
				<input type='radio' id='female' name='gender' value='0' />
				<span>Female</span>
				<p style='margin-top: 5px;'>* Level</p>
				<select>
					<option>2.0-</option>
					<option>2.0</option>
					<option>2.0+</option>
					<option>2.5-</option>
					<option>2.5</option>
					<option>2.5+</option>
					<option>3.0-</option>
					<option>3.0</option>
					<option>3.0+</option>
					<option>3.5-</option>
					<option>3.5</option>
					<option>3.5+</option>
					<option>4.0-</option>
					<option>4.0</option>
					<option>4.0+</option>
					<option>4.5-</option>
					<option>4.5</option>
					<option>4.5+</option>
				</select>
				<br>
				<input type='button' class='add' value='Add'>
				<input type='button' class='clear' value='Clear'>
				
		</div>
		<a class='close-reveal-modal' style='top: 11px;'>&#215;</a>";
	echo $output;

?>