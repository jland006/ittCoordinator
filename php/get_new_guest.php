<?php
	
	$output = "
		<style>
		.new_guest {
			height: 230px;
			width: 242px;
			background-color: #7DC24B;
			padding: 10px;
			border: 2px solid #ddd;
		}
		</style>
		<script>
		$(function() {
			$('.new_guest .clear').on('click', function() {
				$(this).siblings('input:text').val('');
			});
			$('.new_guest .add').on('click', function() {
				var data = new Array();
				$('.new_guest input:text, .new_guest input:radio:checked, .new_guest select').each(function(){
					data.push($(this).val());
				});
				data = JSON.stringify(data);

				$.post('php/new_guest.php', {data: data}, function(output) {
					if (output.length > 1) {
						$('#content .all_guests').prepend(output[1]);
						$('.reveal-modal2.new_guest').trigger('reveal:close').remove();
					}
					alert(output[0]);
				},'json');
			});
			$('.close-reveal-modal').on('click', function() {
				$('.reveal-modal2.new_guest').trigger('reveal:close').remove();
			});
		});
		</script>
		<div id='container'>
				<p>* Last Name</p>
				<input type='text' placeholder='Last Name'>
				
				<p>* First Name</p>
				<input type='text' placeholder='First Name'>
				<p> Primary Phone</p>
				<input type='text' placeholder='ex: 760123456377' maxlength='10'>
				<br>
				<input type='button' class='add' value='Add'>
				<input type='button' class='clear' value='Clear'>
				
		</div>
		<a class='close-reveal-modal' style='top: 11px;'>&#215;</a>";
	echo $output;

?>