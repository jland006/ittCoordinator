$(function() {
	
	var scroller, scroller_court;
	var mouse_down = false;
	var currentDate = null;

	//Must go before datepicker
	$('.megamenu a').on("click", function(e) {
		e.preventDefault();
		return false;
	});
	
	$(".megamenu .greybox").on('click', function(e){
		navbar(e);
	});
								
	$("#datepicker").datepicker({
		changeMonth: true,
		changeYear: true,
		showOtherMonths: true,
		selectOtherMonths: true,
		dateFormat: "yy-mm-dd",
		minDate: "-2y",
		maxDate: "+2w+1d",
		showButtonPanel: true,
		altFormat: "DD MM d, yy",
		altField: "#date_text",
		onSelect: function(date) {
			currentDate = date;
			load_data();
		}
	});
	
	currentDate = $("#datepicker").val();
	
	$(".change_date").on("click", function() {
		var new_date = $("#datepicker").datepicker('getDate', '+1d');
		if ($(this).hasClass("date_left")) {
			new_date.setDate(new_date.getDate()-1);
		}
		else if ($(this).hasClass("date_right")) {
			new_date.setDate(new_date.getDate()+1);
		}
		$("#datepicker").datepicker( "setDate", new_date );
		currentDate = $("#datepicker").val();
		load_data();
	});
		
	
	

	
	$("#content").on("dblclick", ".member_info", function(e) {
		var $target = $(e.target);
		if($target.hasClass("phone_status") || $target.hasClass("arrival"))
			return false;
			
		var id = $(this).attr("data-itt-id");
		$.ajax({
			beforeSend: function(){
				$('body').append("<div class='reveal-modal2 loading'></div>");
				$(".reveal-modal2.loading").reveal({
					animation: "none",
					closeonbackgroundclick: false	
				});
			},
			dataType: "json",
			type: "POST",
			url: "php/load_request_data.php",
			data: {id:id},
			success: function(output){
				$("#main #profile_layout").html(output["profile_layout"]);
				$("#main #court_layout").html(output["court_layout"]);
				$("#main .all_guests").html(output["all_guests"]);
				$("#main .all_members").html(output["all_members"]);
				$("#main .bl_members").html(output["bl_members"]);
				$('.reveal-modal2.loading').trigger('reveal:close').remove();
			},
			complete: function(){
				
				var change = false;
					$('#main .bl_members, #main .all_guests, #main .all_members').slimscroll({
						height: '100%',
						size: '8px',
						railVisible: true
					});
				$('.member_request .close-reveal-modal').on('click', function() {
					if (change) {
						change = false;
						var id = $("#main #profile_layout #player_id").val();
						var data = new Array();
						$("#profile_layout input:text, #profile_layout select").each(function(){
							data.push($(this).val());
						});
						data = JSON.stringify(data);
						$.post("php/update_member.php", {id:id, data: data}, function(output){
							if (output)
								alert(output);
						});
					}
				});
				$("#profile_layout select").on("change", function() {
					change = true;
				});
				
				$("#profile_layout input:text").on("keyup", function() {
					change = true;
				});
				
				$("#deactivate").on("click", function(){
					var id = $("#main #profile_layout #player_id").val();
					$('[data-itt-id="' + id +'"]').remove();
					$.post("php/deactivate.php", {id:id} );
				});
				
				$('#profile_layout .phone').keypress(function(event) {
					if ( event.which < 48 || event.which > 57 ) {
						event.preventDefault();
					}
				});
				$('.court_request .members_list').slimscroll({
					height: '128px',
					size: '8px'
				});
				
				$('.court_request .slimScrollDiv').hide();
				$('.title.requested').siblings('.slimScrollDiv').show();
				
				$(".ball_request").on("click", function() {
					$title = $(this).siblings('.title');
					$siblings = $(this).siblings().not($title);
					$(this).hide();
					$siblings.slideDown();
					$title.addClass("requested");
					var id = $("#main #profile_layout #player_id").val();
					var rdate = $(this).closest(".court_request").attr("data-itt-sdate");
					$.post("php/request_match_members.php", {id:id, date:rdate, request:1});
				});
				
				$(".court_request").on({
					mouseenter: function() {
						$(this).children("span").html("Remove Request");
					},
					mouseleave: function() {
						$(this).children("span").html($(this).attr("data-itt-ldate"));
					},
					click: function() {
						$title = $(this);
						$siblings = $title.siblings().hide();
						$title.siblings(".ball_request").slideDown();
						$title.removeClass("requested");
						$(this).children("span").html($(this).attr("data-itt-ldate"));
						$menu = $title.siblings(".menu");
						$menu.children(".players").html("1");
						$menu.children(".ballmach").removeClass("withBall");
						var id = $("#main #profile_layout #player_id").val();
						var rdate = $(this).closest(".court_request").attr("data-itt-sdate");
						$.post("php/request_match_members.php", {id:id, date:rdate, request:0});
					}
				}, ".title.requested");
				
				$(".court_request .ballmach").on("click", function() {
					var id = $("#main #profile_layout #player_id").val();
					var rdate = $(this).closest(".court_request").attr("data-itt-sdate");
					if ($(this).hasClass('withBall')) {
						$(this).attr("class","ballmach");
						$.post("php/request_ballmach_members.php", {date: rdate, id: id, ball: 0});
					}
					else {
						$(this).attr("class","ballmach withBall");
						$.post("php/request_ballmach_members.php", {date: rdate, id: id, ball: 1});
					}
				});
				$(".court_request .time").on({
					click: function() {$(this).children(".times").show();},
					mouseleave: function() {$(this).children(".times").hide();}
				});
				$(".court_request .times li").on("click", function(e) {
					var $target = $(e.target);
					var $time = $(this).closest(".time");
					var court_time = $time.attr("data-itt-time");
					var new_time = $target.attr("data-itt-time");

					if (new_time != court_time) {
						$time.children("p").html($(this).children("p").text());
						$time.attr("data-itt-time", new_time)
						var id = $("#main #profile_layout #player_id").val();
						var rdate = $(this).closest(".court_request").attr("data-itt-sdate");
						$.post('php/request_time_members.php', {date: rdate, id:id, time: new_time});
					}
				});
				
				$("#main .all_members").sortable({
					placeholder: "member_request_highlight",
					containment: '#main',
					revert: 'true',
					connectWith: ".allow_members",
					remove: function(event, ui) {
						if (!ui.item.parent().hasClass("bl_members")) {
							ui.item.clone().appendTo(ui.item.parent());
							$(this).sortable('cancel');
							set_DND_request();
						}
					},
					receive: handleMembersDrop
				}).disableSelection();
				$("#main .bl_members").sortable({
					placeholder: "member_request_highlight",
					containment: '#main',
					revert: 'true',
					connectWith: ".allow_blist",
					receive: handleBlistDrop
				}).disableSelection();
				$("#main .all_guests").sortable({
					placeholder: "member_request_highlight",
					containment: '#main',
					revert: 'true',
					connectWith: ".allow_guests",
					remove: function(event, ui) {
						ui.item.clone().appendTo(ui.item.parent());
						$(this).sortable('cancel');
						set_DND_request();
					},
					receive: handleGuestDrop
				}).disableSelection();
				$("#main .members_list").sortable({
					placeholder: "member_request_highlight",
					containment: '#main',
					revert: 'true',
					connectWith: ".allow_members, .allow_guests",
					cancel: ".nomove",
					receive: handleRequestDrop
				}).disableSelection();
				
				set_DND_request();
				
				$('.member_request').reveal({
					closeonbackgroundclick: false	
				});
			}
		});
	});	
		
	$('#court_layout').sortable({
		containment: 'parent',
		start: handleDragCourt,
		update: handleDropCourt
	}).disableSelection();
	
	$('#court_outline').on("dblclick", function(e) {
		var $target = $(e.target);
		if ($target.attr("id") =="court_layout") {
			$list_far_right = $('#list_layout_far_right');
			$list_right = $('#list_layout_right');
			$list_left = $('#list_layout_left');
			if ($list_far_right.hasClass('off_screen') && $list_right.hasClass('off_screen') && $list_left.hasClass('off_screen')) {
				$list_far_right.removeClass('off_screen');
				$list_right.removeClass('off_screen');
				$list_left.removeClass('off_screen');
			}
			else {
				$list_far_right.addClass('off_screen');
				$list_right.addClass('off_screen');
				$list_left.addClass('off_screen');
			}
		}
	});
	
	$("#all_guests_bar").on("dblclick", function() {
		$('#list_layout_far_right').toggleClass('off_screen');
	});
	
	$("#all_members_bar").on("dblclick", function() {
		$('#list_layout_right').toggleClass('off_screen');
	});
	
	$("#waiting_list_bar").on("dblclick", function() {
		$('#list_layout_left').toggleClass('off_screen');		
	});
	
	
	load_data();
	
	function load_data() {	
		$.ajax({
			beforeSend: function(){
				$('body').append("<div class='reveal-modal2 loading'></div>");
				$(".reveal-modal2.loading").reveal({
					animation: "none",
					closeonbackgroundclick: false	
				});
			},
			dataType: "json",
			type: "POST",
			url: "php/load_data.php",
			data: {date: currentDate},
			success: function(output){
				$("#content #court_layout").html(output["court_layout"]);
				$("#content .waiting_list").html(output["waiting_list"]);
				$("#content .all_members").html(output["all_members"]);
				$("#content .all_guests").html(output["all_guests"]);
			},
			complete: function(){
				$('#content #court_layout, #content .waiting_list, #content .all_members, #content .all_guests').slimscroll({
					height: '100%',
					size: '8px',
					railVisible: true
				});
				set_DND();
				$('.court.locked .members_list').sortable({ disabled: true });
				$(".court.locked").on({
					mouseenter: function() {
						$('#court_layout').sortable("disable");
						return false;
					},
					mouseleave: function() {
						$('#court_layout').sortable("enable");
						return false;
					}
				});
				$('.members_list').slimscroll({
					height: '128px',
					size: '8px'
				});
				$(".time").on({
					click: function() {
						if($(this).closest(".court").hasClass("unlocked"))
							$(this).children(".times").show();
					},
					mouseleave: function() {$(this).children(".times").hide();}
				});
				$(".times li").on("click", function(e) {
					var $target = $(e.target);
					var $time = $(this).closest(".time");
					var court_time = $time.attr("data-itt-time");
					var new_time = $target.attr("data-itt-time");

					if (new_time != court_time) {
						$time.children("p").html($(this).children("p").text());
						$time.attr("data-itt-time", new_time)
						var cnum = $(this).closest(".court").attr("data-itt-cid");
						$.post('php/court_time.php', {date: currentDate, cnum:cnum, time: new_time});
					}
				});
				$(".member_info .phone_status, .guest_info .phone_status").on("click", function() {
					$player_info = $(this).closest('li');
					var type = $player_info.attr("class")
					var id = $player_info.attr("data-itt-id");
					
					if ($(this).hasClass('accepted')) {
						$(this).attr("class","phone_status uncalled");
						$.post("php/phone_status.php", {date: currentDate, id: id, type: type, phoned: 2});
					}
					else if ($(this).hasClass('called')) {
						$(this).attr("class","phone_status accepted");
						$.post("php/phone_status.php", {date: currentDate, id: id, type: type, phoned: 1});
					}
					else {
						$(this).attr("class","phone_status called");
						$.post("php/phone_status.php", {date: currentDate, id: id, type: type, phoned: 0});
					}
				});
				$(".member_info .arrival, .guest_info .arrival").on("click", function() {
					$player_info = $(this).closest('li');
					var type = $player_info.attr("class")
					var id = $player_info.attr("data-itt-id");
					if ($(this).hasClass('arrived')) {
						$(this).attr("class","arrival");
						$.post("php/arrival.php", {date: currentDate, id: id, type: type, arrived: 0});
					}
					else {
						$(this).attr("class","arrival arrived");
						$.post("php/arrival.php", {date: currentDate, id: id, type: type, arrived: 1});
					}
				});
				$(".court .ballmach").on("click", function() {
					var $court = $(this).closest(".court");
					
					if ($court.hasClass('locked')) {
						return false;	
					}
					var cnum = $court.attr("data-itt-cid");
					if ($(this).hasClass('withBall')) {
						$(this).attr("class","ballmach");
						$.post("php/court_ballmach.php", {date: currentDate, cnum: cnum, ball: 0});
					}
					else {
						$(this).attr("class","ballmach withBall");
						$.post("php/court_ballmach.php", {date: currentDate, cnum: cnum, ball: 1});
					}
				});
				$(".court .lock").on("click", function(){
		
					var $court = $(this).closest(".court");
					var cnum = $court.attr("data-itt-cid");
					var $list = $court.find(".members_list");
			
					if ($court.hasClass('locked')) {
						$court.attr("class","court unlocked");
						$court.flip({
							direction: 'rl',
							color: '#336600',
							speed: 250
						});
						$list.sortable("enable");
						$.post("php/lock_court.php", {date: currentDate, cnum: cnum, lock: 0});
						return false;
					}
					else if ($court.hasClass('unlocked')){
						$court.attr("class","court locked");
						$court.flip({
							direction: 'lr',
							color: '#98491E',
							speed: 250
						});
						$list.sortable("disable");
						$.post("php/lock_court.php", {date: currentDate, cnum: cnum, lock: 1});
						return false;
					}
				});
				$(".reveal-modal2.loading").trigger('reveal:close').remove();
				/*$(".reveal-modal2.new_member").reveal({
					animation: "none",
					closeonbackgroundclick: false	
				});*/
			}
		});
	}


	
	function set_DND() {
		$( ".connectedSortable" ).sortable({
			placeholder: "member_highlight",
			containment: '#content',
			revert: 'true',
			connectWith: ".connectedSortable",
			update: handleUpdateList,
			receive: handleMemberDrop,
			cancel: ".nomove"
		}).disableSelection();
		
		$('#content .member_info, #content .guest_info').on({
			mousedown: function() {
				mouse_down = true;
			},
			mousemove: function() {
				if (mouse_down) {
					scroller = $(this).closest('.slimScrollDiv');
					scroller.css('position', '');
					scroller.children('.slimScrollBar').hide();
					scroller.children('.slimScrollRail').hide();
					
					scroller_court = $('#court_layout').closest('.slimScrollDiv');
					scroller_court.css('position', '');
					scroller_court.children('.slimScrollBar').hide();
					scroller_court.children('.slimScrollRail').hide();
				}
			},
			mouseup: function() {
				mouse_down = false;
				scroller.css('position', 'relative');
				scroller = null;
				
				scroller_court.css('position', 'relative');
				scroller_court = null;
			}
		});	
	}
	
	function set_DND_request() {
		
		$('#main .member_info, #main .guest_info').on({
			mousedown: function() {
				mouse_down = true;
				if ($(this).parent().hasClass("members_list")) {
					if ($(this).attr("class") == "member_info") {
						$("#main .all_guests").sortable("disable");
						$("#main .members_list").not($(this).parent()).sortable("disable");
					}
					else if ($(this).attr("class") == "guest_info") {
						$("#main .all_members").sortable("disable");
						$("#main .bl_members").sortable("disable");
						$("#main .members_list").not($(this).parent()).sortable("disable");
					}
				}
			},
			mousemove: function() {
				if (mouse_down) {
					scroller = $(this).closest('.slimScrollDiv');
					scroller.css('position', '');
					scroller.children('.slimScrollBar').hide();
					scroller.children('.slimScrollRail').hide();
					
					scroller_court = $('#court_layout').closest('.slimScrollDiv');
					scroller_court.css('position', '');
					scroller_court.children('.slimScrollBar').hide();
					scroller_court.children('.slimScrollRail').hide();
				}
			},
			mouseup: function() {
				mouse_down = false;
				scroller.css('position', 'relative');
				scroller = null;
				
				scroller_court.css('position', 'relative');
				scroller_court = null;
				
				$("#main .all_guests").sortable("enable");
				$("#main .all_members").sortable("enable");
				$("#main .bl_members").sortable("enable");
				$("#main .members_list").sortable("enable");
			}
		});		
		
	}
	
	function handleDragCourt( event, ui ) {
		ui.item.data('courts', $(this).sortable('toArray', {attribute: 'data-itt-cid'}));
	}
	
	function handleDropCourt( event, ui ) {
	
		$this = $(this);
		var courts = ui.item.data('courts');
		var curr_pos = ui.item.index();
		var cf_num = ui.item.attr("data-itt-cid");
		var ct_num = courts[curr_pos];

		$court_from = ui.item;
		$court_to = $('[data-itt-cid="' + ct_num +'"]');
		
		var $list_from = $court_from.find(".members_list");
		var mem_from_sz = $list_from.children('.member_info').size();
		var guest_from_sz = $list_from.children('.guest_info').size();
		var time_from = $court_from.find('.time').attr("data-itt-time");
		var locked_from = $court_from.hasClass("locked");
		var ballmach_from = $court_from.find(".menu .ballmach").hasClass("withBall");
		
		
		var $list_to = $court_to.find(".members_list");
		var mem_to_sz = $list_to.children('.member_info').size();
		var guest_to_sz = $list_to.children('.guest_info').size();
		var time_to = $court_to.find('.time').attr("data-itt-time");
		var locked_to = $court_to.hasClass("locked");
		var ballmach_to = $court_to.find(".menu .ballmach").hasClass("withBall");
		
		
		$court_from.attr("data-itt-cid", ct_num);
		$court_from.find('.title span').html("Court #" + ct_num);
		
		$court_to.attr("data-itt-cid", cf_num);
		$court_to.find('.title span').html("Court #" + cf_num);
		
		$.each( courts, function( key, value ) {
			$this.append($('[data-itt-cid="' + value +'"]'));
		});


		
		var data = new Array();
		data.push(currentDate);
		data.push(cf_num);
		data.push(mem_from_sz);
		data.push(guest_from_sz);
		data.push(time_from);
		data.push(locked_from);
		data.push(ballmach_from);
		data.push(ct_num);
		data.push(mem_to_sz);
		data.push(guest_to_sz);
		data.push(time_to);
		data.push(locked_to);
		data.push(ballmach_to);
		
		data = JSON.stringify(data);
		
		$.post('php/transfer.php', {data: data});
	
	}

	function handleUpdateList() {}

	function handleMemberDrop(event, ui) {
		var $list_to = ui.item.parent();
		var $list_from = ui.sender;
		var id = ui.item.attr("data-itt-id");
		var type = ui.item.attr("class");
		var cnum_to = $list_to.closest(".court").attr("data-itt-cid");
		var cnum_from = $list_from.closest(".court").attr("data-itt-cid");

		if (cnum_to == null && cnum_from == null) {
			cnum_to = 0;
			cnum_from = 0;
		}
		else if (cnum_to == null) {
			cnum_to = 0;
			set_court_prop($list_from);
		}
		else if (cnum_from == null) {
			cnum_from = 0;
			set_court_prop($list_to);
		}
		else {
			set_court_prop($list_to);
			set_court_prop($list_from);
		}

		$.post("php/update_players.php", 
		{date: currentDate, list_to: $list_to.attr("class"), list_from: $list_from.attr("class"), id: id,
		cnum_to: cnum_to, cnum_from:cnum_from, type: type});
	}
	
	function handleRequestDrop(event, ui) {
		var player_id = $("#main #profile_layout #player_id").val();
		//var $list_from = ui.sender;
		var id = ui.item.attr("data-itt-id");
		//var type = ui.item.attr("class");
		var rdate = $(this).closest(".court_request").attr("data-itt-sdate");
		
		$.post("php/update_request_players.php", {player_id:player_id, date:rdate, id:id});
			
		var seen = {};
		$(this).children("li").each(function() {
			var txt = $(this).text();
			if (seen[txt])
				$(this).remove();
			else
				seen[txt] = true;
		});
	}
	
	function handleGuestDrop(event, ui) {
		var player_id = $("#main #profile_layout #player_id").val();
		var id = ui.item.attr("data-itt-id");
		var $list_from = ui.sender;
		var rdate = $list_from.closest(".court_request").attr("data-itt-sdate");
		
		ui.item.remove();	
		
		$.post("php/remove_request_guest.php", {player_id:player_id, date:rdate, id:id});
	}
	
	function handleMembersDrop(event, ui) {
		var player_id = $("#main #profile_layout #player_id").val();
		var id = ui.item.attr("data-itt-id");
		var $list_from = ui.sender;
		var rdate = $list_from.closest(".court_request").attr("data-itt-sdate");

		if ($list_from.hasClass("members_list")) {
			ui.item.remove();
			$.post("php/remove_request_guest.php", {player_id:player_id, date:rdate, id:id});
		}
		else if ($list_from.hasClass("bl_members")) {
			$.post("php/blist_remove.php", {player_id:player_id, id:id});
		}

	}
	
	function handleBlistDrop(event, ui) {
		var player_id = $("#main #profile_layout #player_id").val();
		var id = ui.item.attr("data-itt-id");

		$.post("php/blist_add.php", {player_id:player_id, id:id});
	}
	
	function navbar(e) {
		e.preventDefault();
		var $target = $(e.target);

		if ($target.parent().hasClass('addCourt')) {
			var num = $target.html();
			num = num.substring(7);
			$.post("php/add_court.php", {date: currentDate, cnum:num}, function(output) {
				$("#court_layout").append(output);
				set_DND();
			});
		}
		else if ($target.parent().hasClass('printCourts')) {
			var idx = $target.index();
			switch(idx) {
				case 0:
					print_court_layout();
					break;
				case 1:
					print_list_members();
					break;
			}
		}
		else if ($target.parent().hasClass('members')) {
			var idx = $target.index();
			switch(idx) {
				case 0:
					$.post("php/get_new_member.php", function(output) {
						$('body').append("<div class='reveal-modal2 new_member'></div>");
						$(".reveal-modal2.new_member").html(output).reveal({
							closeonbackgroundclick: false	
						});
					});
					break;
				case 1:
					$.post("php/get_inactive.php", function(output) {
						$('body').append("<div class='reveal-modal2 reactivate'></div>");
						$(".reveal-modal2.reactivate").html(output).reveal({
							closeonbackgroundclick: false	
						});
					});
					break;
			}
		}
		else if ($target.parent().hasClass('guests')) {
			var idx = $target.index();
			switch(idx) {
				case 0:
					$.post("php/get_new_guest.php", function(output) {
						$('body').append("<div class='reveal-modal2 new_guest'></div>");
						$(".reveal-modal2.new_guest").html(output).reveal({
							closeonbackgroundclick: false	
						});
					});
					break;
			}
		}
	}

	function set_court_prop($list) { 
		var $member_list = $list.children('li');
		var size = $member_list.size();
		var l = "";
		var u = "";
		$court_menu = $list.closest(".court").find(".menu");
		$court_menu.children('.players').html(size);
		
		$level = $court_menu.children('.level');
		
		if (size == 0) {
			$level.attr("data-itt-llevel", "")
			$level.attr("data-itt-ulevel", "");
			$level.html('');
		}
		else {
			$member_list.each(function() {
				var skill = $(this).attr("data-itt-skill");
				
				if (skill != "") {
					if (lte(skill, l) || l == "")
						l = skill;
									
					if (lte(u, skill))
						u = skill;
				}
			});

			$level.attr("data-itt-llevel", l)
			$level.attr("data-itt-ulevel", u);	
			$level.html(l + " : " + u);
		}
	}
	
	function lte(level1, level2) {
		if (level1 == level2)
			return true;
			
		var s1 = level1.substr(0,3);
		var s2 = level2.substr(0,3);
		
		if (s1 != s2)
			return (s1 < s2);
			
		s1 = level1.substr(3,1);
		s2 = level2.substr(3,1);
		
		return (s1 == "-" || s2 == "+");
	}
	
	function print_court_layout() {

		var html = "<!DOCTYPE html> \
					<head> \
					<meta http-equiv='Content-Type' content='text/html; charset=utf-8' /> \
					<title>ItsTennisTime.com</title> \
					<link href='css/print.css' rel='stylesheet' type='text/css' media='print' /> \
					<link href='css/print2.css' rel='stylesheet' type='text/css' /> \
					</head> \
					<body> \
						<div id='header'>" + $("#date_text").val() + "</div>";
						
		$('#content .court').each(function() {
			html += "<div id='court'> \
						<div id='title'>" + $(this).find('.title').children('span').text() + "</div> \
						<div id='time'>" + $(this).find('.time_view').text() + "</div>";
				$(this).find('.members_list li').each(function() {
					html += "<div>" + $(this).children('.name').text() + "</div>";
				});
	
			html += "</div>";
		});
		
					
			html += "</body> \
					</html>";
	
		
		var printWP = window.open("","Print Schedule");
	
		printWP.document.open();
	
		//insert content
	
		printWP.document.write(html);
	
		printWP.document.close();
	
		//open print dialog
	
		printWP.print();
	}
	
	function print_list_members() {
		var html = "<!DOCTYPE html> \
					<html xmlns='http://www.w3.org/1999/xhtml'> \
					<head> \
					<meta http-equiv='Content-Type' content='text/html; charset=utf-8' /> \
					<title>ItsTennisTime.com</title> \
					<link href='css/print4.css' rel='stylesheet' type='text/css' /> \
					</head> \
					<body> \
						<div id='header'>" + $("#date_text").val() + "</div> \
						<table width='425' border='1' style='float:left;margin-bottom:20px;margin-right:25px;'> \
							<tr> \
								<td width='200' >Name</td> \
								<td width='50'>Court</td> \
								<td width='75'>Time</td> \
								<td width='100'>Contact</td> \
							</tr>";
		var count = 1;				
		$('.court').each(function() {
			var cid = $(this).attr("data-itt-cid");
			var time = $(this).find('.time_view').text();
			$(this).find('.members_list li').each(function() {

				//var type = $(this).attr("class");

				/*
				if (type == "member_guest") {
					phone =  $(this).children('#pnum').val();
				} else {
					var info = $(this).children('#info').val();
					var obj = jQuery.parseJSON(info);
					phone = obj.p1;
				}*/
				html += "<tr> \
							<td>" + $(this).children('.name').text() + "</td> \
							<td>#" + cid + "</td> \
							<td>" + time + "</td> \
							<td>" + $(this).attr("data-itt-phone") + "</td> \
						<tr>";
				count++;
				if (count == 26) {
					count=1;
					html += "</table> \
							<table width='425' border='1' style='float:left;margin-bottom:20px;margin-right:25px;'> \
							<tr> \
								<td width='200' >Name</td> \
								<td width='50'>Court</td> \
								<td width='75'>Time</td> \
								<td width='100'>Contact</td> \
							</tr>";	
				}
			});
	
			
		});
		
			html += "</table>";		
			html += "</body> \
					</html>";
	
		
		var printWP = window.open("","Print Schedule");
	
		printWP.document.open();
	
		//insert content
	
		printWP.document.write(html);
	
		printWP.document.close();
	
		//open print dialog
	
		printWP.print();
	}
});