<!DOCTYPE html>
<html>
	<?php 
		require_once('../const.php');
		require_once('../db.php');
	?>
	<head>
		<title>LinkedIn Bot Admin Interface</title>
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
		<link rel="stylesheet" href="style.css">

		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
  		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
  		<script src="bootbox.min.js"></script>
  		<script src="functions.js"></script>
	</head>
	<body>

		<div class="ajax-response">
			<p class="alert"></p>
		</div>

		<h1 class="col-lg-12 text-center linkedin-color"><i class="fa fa-linkedin-square" aria-hidden="true"></i> LinkedIn Bot Admin Interface</h1>

			<h3 class="col-lg-12 text-center alert alert-danger disconnect" hidden=true>This account is disconnected! You have to reconnect manually.</span> <button class="btn btn-sm btn-success" id="done-reconnect">Done!</button></h3>
			<a href="watson_test.php">Watson test interface</a>
		<div class="onOffBtn text-center">
			<label class="switch">
				<input id="on-off-btn" type="checkbox">
				<span class="slider round"></span>
			</label>
			<h3 class="alert alert-info">The bot is <span id="bot-state">???</span></h3>
		</div>

		<container class="accounts">
			<button class="btn btn-md btn-primary open-modal" id="accounts">Add an account</button>
			Selected Account : <span class="listAccounts"></span>
		</container>

		<div class="text-center">
			<div class="column column-1 col-lg-3">
				<h6 class="title">Settings</h6>
				<div class="content">
					<div class="alert alert-info"><p class="description">Here you can add or delete key words the bot will use to search people</p></div>
					<input class="form-control" type="text" placeholder="Key word" id="key-word" />
					<button class="btn btn-sm btn-primary" type="button" id="saveKeyWord">Save key word</button>

					<div class="alert alert-info"><p class="description">If a key-word is<span class="alert-warning"> like this </span>, that means that the bot already searched all users with this key word and will not use it again. If you want to retry with a <i>done</i> key word, just delete it and save it again.</p></div>
					<div class="key-words-list">
						<ul>
						</ul>
					</div>
					<br/>
					<div class="alert alert-info">
						<p class="description">
							<b>Save a new template</b><br/>
							You can manage the previous templates and the activate templates from the <i>Template Manager</i> below.
						</p>
					</div>
					<textarea class="form-control" rows="5" id="default-msg"></textarea><br/>
					<button class="btn btn-primary btn-sm" id="save-default-msg">Add template</button><br/><br/>
					<button class="btn btn-info btn-sm open-modal" id="template">Template Manager <i class="fa fa-cogs"></i></button>
				</div>
			</div>
			<div class="col-lg-6 col-padding-both">
				<div class="column column-2 conversation">
					<h6 class="title"><span id="nbUnreadConv"><i class="fa fa-circle-o-notch fa-spin" style="font-size:24px"></i></span> unread messages - <a id="random-conv">Another random unread conversation!</a></h6>
					<div class="content">
						<div class="infos-user alert-info">
							<a class="link-profile" target="_blank" href="#">
								<b id="conv-user-name"><i class="fa fa-circle-o-notch fa-spin" style="font-size:24px"></i></b><br/>
								<span id="conv-user-job"></span>
							</a>
						<hr/>
						</div>
						<div class="conv-msg">
							
						</div>
						<hr/>
						<button class="btn btn-md btn-warning" id="mark-read">Mark as read and never see it again?</button>
						<br/><br/>
						<div class="answer-form">
							<textarea class="form-control" id="answer-conv-msg" rows="5"></textarea><br/>
							<button class="btn btn-primary btn-md btn-block" id="send-msg">Send</button>
						</div>
					</div>
				</div>
			</div>
			<div class="column column-3 col-lg-3">
				<h6 class="title">Statistiques</h6>
				<div class="content">
					<br/>
					<p><b>Number of connections</b></p>
					<h3 class="stats-value" id="getAllConnections">???</h3><hr/>
					<p><b>Number of connections asked by the bot</b></p>
					<h3 class="stats-value" id="getAllConnectionsSent">???</h3><hr/>
					<p><b>Number of msg sent</b></p>
					<h3 class="stats-value" id="getMsgSent">???</h3><hr/>
					<p><b>Number of msg received</b></p>
					<h3 class="stats-value" id="getMsgReceived">???</h3><hr/>
					<p><b>Right now:</b></p>
					<h5 class="stats-value" id="getAction">???</h5>
				</div>
			</div>
		</div>

		<!-- Modal -->
		<div class="modal fade" id="modal" role="dialog">
			<div class="modal-dialog modal-lg">
			<!-- Modal content-->
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title"></h4>
					</div>
					<div class="modal-body"></div>
				</div>
			</div>
		</div>
	</body>
</html>

<script>
$(document).ready(function(){
	var disconnect = false;

	var selectedAccount = getUrlParameter('account');
	if(selectedAccount === undefined){
		// SYNC call to get default accounts
		$.ajax({
			type: 'POST',
			url: 'actions.php',
			data: {'action': 'getAllAccounts'},
			success: function(resp){
				selectedAccount = resp.value[0].ID;
			},
			dataType: "json",
			async:false
		});
		document.location.href="index.php?account="+selectedAccount;
	}

	function botDisconnect(isDisconnect){
		// global var
		disconnect = isDisconnect;

		$('h3.disconnect').prop('hidden', !isDisconnect);

		if(isDisconnect){
			$('#mark-read').prop('disabled', true);
			$('#send-msg').prop('disabled', true);
		}
	}

	function saveKeyWord(){
		if($('#key-word').val() != ''){
			post({'action': 'saveKeyWord', 'val': $('#key-word').val()}, selectedAccount, function(resp){
				$(".key-words-list ul").append('<li class="alert-info"><a class="key-word-item" id="'+resp.id+'">'+$('#key-word').val()+'</a><i class="fa fa-times-circle-o" aria-hidden="true" id='+resp.id+'></i></li>');
				// add the click listener to the new <i>
				$('i[id="'+resp.id+'"]').click(function(){
					var id = $(this).attr('id');
					post({'action': 'delKeyWord', 'id': id}, selectedAccount, function(resp){
						$('i[id="'+id+'"]').parent().remove();
					});
				})
				$('#key-word').val('');
			});
		}else{
			showBar(false, 'Please enter a key word');
		}
	}

	function refreshValues(){
		// check disconnect
		post({'action': 'botDisconnect'}, selectedAccount, function(resp){
			botDisconnect(resp.disconnect);
		});

		// bot on off
		post({'action': 'isOn'}, selectedAccount, function(resp){
			$('#on-off-btn').prop('checked', resp.isOn);
			$('#bot-state').text(resp.isOn?'On':'Off');
		});
		
		$('.stats-value').each(function(){
			var value = $(this).attr('id');
			post({'action': 'stats', 'function': value}, selectedAccount, function(resp){
				var previousVal = $('#'+value).html();
				if(previousVal == '???'){
					$('#'+value).html(resp.value);
				}else if(resp.value != previousVal){
					$('#'+value).css({ color: 'green' });
					$('#'+value).html(resp.value);
					setTimeout(function(){$('#'+value).css({ color: 'black' });}, 1000);
				}
			});
		});
	}


	function getMsgConversation(conv_id){
		conv_id = '6326694911420698624';
		// we first check if answere human way
		post({'action': 'checkConvAnswered', 'conv': conv_id}, selectedAccount, function(noResp){
			post({'action': 'getMsgConv', 'conv': conv_id}, selectedAccount, function(resp){
				var msgs = resp.msgs;
				// get user informations (name & jobs)
				post({'action': 'getUserInformations', 'profile_id': msgs[0].profile_id}, selectedAccount, function(resp){
					var userName = resp.data.firstName+' '+resp.data.lastName;
					$('#conv-user-name').text(userName);
					$('#conv-user-job').text(resp.data.job);
					$('a.link-profile').prop('href', 'https://www.linkedin.com/in/'+msgs[0].profile_id+'/');

					// then the conv
					$('.conversation .conv-msg').html('');
					if((msgs[msgs.length-1].msg == '' && (msgs.length>1 && msgs[msgs.length-2] == '' && msgs[msgs.length-2].by_bot != true)) || msgs[msgs.length-1].by_bot == true){	// not a msg (user just accepted you on LinkedIn, but not a msg) OR last msg by bot (probably human way)
						post({'action': 'markRead', 'conv': conv_id},  selectedAccount);
						showBar(false, "Oups! This message was answered. I'm saving it and choosing another conversation!");
						// getUnreadConv();
					}else{
						$.each(msgs, function(index, val){
							if(val.msg != '' && val.msg != null){
								var whoSend = val.by_bot==1?'bot':userName;
								$('.conversation .conv-msg').append(
									'<div class="convMsg '+whoSend+'"><p class="date">'+whoSend+' - '+val.date+'</p><p class="text">'+(val.msg).replace(/\\n/g, "<br/>")+'</p></div>'
								);
							}
						});
						// scroll to bottom
						$(".conv-msg").animate({ scrollTop: $('.conv-msg')[0].scrollHeight }, "slow");
						// saving conv_id for answer
						$('#send-msg').attr('conv-id', ''+conv_id);
						$('#send-msg').attr('profile-id', ''+msgs[0].profile_id);
						// mark as read btn
						$('#mark-read').attr('conv-id', ''+conv_id);
					}
				});
			});
		});
	}

	function getUnreadConv(){
		$('#nbUnreadConv, .conversation .conv-msg, #conv-user-name').html('<i class="fa fa-circle-o-notch fa-spin" style="font-size:24px"></i>');
		$('#conv-user-job').html('');
		post({'action': 'unreadConv'}, selectedAccount, function(resp){
			var unreadConv = resp.unreadConv;
			var nbUnreadConv = resp.unreadConv == null?0:resp.unreadConv.length;
			$('#nbUnreadConv').text(nbUnreadConv);
			if(nbUnreadConv != 0){
				var conv = unreadConv[Math.floor(Math.random()*nbUnreadConv)].conv_id;
				getMsgConversation(conv);
				$('#send-msg, #mark-read').prop('disabled', false);
			}else{
				$('.conversation .conv-msg, #conv-user-name, #conv-user-job').html('No conversation');
				$('#send-msg, #mark-read').prop('disabled', true);
			}
		});
	}

	$('#on-off-btn').click(function(e){
       	post({'action': 'changeBotState', 'state': $(this).prop('checked')?1:0});
		$('#bot-state').text($(this).prop('checked')?'On':'Off');
	});
	$('#done-reconnect').click(function(){
		bootbox.confirm({
		    message: "This account has been disconnected by LinkedIn. You have to connect yourself and to validate the <i>I am not a robot</i> before restart this account. Have you already done it?",
		    buttons: {
		        confirm: {
		            label: 'Yes',
		            className: 'btn-success'
		        },
		        cancel: {
		            label: 'No',
		            className: 'btn-danger'
		        }
		    },
		    callback: function (result) {
		        if(result){
		        	botDisconnect(false);
		        	post({'action': 'changeBotDisconnect', 'disconnect': 0}, selectedAccount);
		        }
		    }
		});
	});

	$('#send-msg').click(function(){
		if($('#answer-conv-msg' != '')){
			post({'action': 'sendMsg', 'profile_id': $(this).attr('profile-id'), 'msg': $('#answer-conv-msg').val()},  selectedAccount);
			$('.conversation .conv-msg').append(
				'<div class="convMsg bot"><p class="date">bot - just now</p><p class="text">'+$('#answer-conv-msg').val()+'</p></div>'
			);
			$('#answer-conv-msg').val('');
			// scroll to bottom
			$(".conv-msg").animate({ scrollTop: $('.conv-msg')[0].scrollHeight }, "slow");
		}
	});
	$('#mark-read').click(function(){
		$('#nbUnreadConv, .conversation .conv-msg, #conv-user-name').html('<i class="fa fa-circle-o-notch fa-spin" style="font-size:24px"></i>');
		$('#conv-user-job').html('');
		post({'action': 'markRead', 'show': true, 'conv': $(this).attr('conv-id')}, selectedAccount, function(r){
			getUnreadConv();
		});
	});

	$('#saveKeyWord').click(function(){
		saveKeyWord();
	});
	$('#key-word').keyup(function(e){
		e.preventDefault();
		if(e.keyCode == 13){
			saveKeyWord();
		}
	});
	$('#random-conv').click(getUnreadConv);

	$('#save-default-msg').click(function(){
		if($('#default-msg').val() != ''){
			// line break to br
			var val = $('#default-msg').val().replace(/(?:\r\n|\r|\n)/g, '<br />');
			post({'action': 'saveTemplate', 'msg': val},  selectedAccount);
			$('#default-msg').val('');
		}
	});

	$('.open-modal').click(function(){
		openModal($(this).attr("id"), selectedAccount, {}, true);
	});

	// key words
	post({'action': 'getKeyWords'}, selectedAccount, function(resp){
		$.each(resp.keyWords, function(key, value){
			var colorClass = value.done==1?'alert-warning':'alert-info';
			$('.key-words-list ul').append(' <li class="'+colorClass+'"><span class="key-word-item">'+value.key_word+'</span><i class="fa fa-times-circle-o" aria-hidden="true" id='+value.ID+'></i></li>');
			// add the click listener to the new <i>
			$('i[id="'+value.ID+'"]').click(function(){
				var id = $(this).attr('id');
				post({'action': 'delKeyWord', 'id': id}, selectedAccount, function(resp){
					$('i[id="'+id+'"]').parent().remove();
				});
			})
		});
	});

	post({'action': 'getAllAccounts'}, selectedAccount, function(resp){
		$.each(resp.value, function(key, value){
			$('.listAccounts').append('<a style="background-color: '+(value.detected==1?'#ffbaba':'auto')+'" href="index.php?account='+value.ID+'" id="account-link-'+value.ID+'">'+value.email+'</a> | ');
		});
		$('#account-link-'+selectedAccount).css({backgroundColor: '#5bc0de', color: 'white'});
	});

	getUnreadConv();
	// every  sec, we refresh stats and the first time too
	refreshValues();
	window.setInterval(refreshValues, 1000);
});
</script>