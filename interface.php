<!DOCTYPE html>
<html>
	<?php 
		require_once('const.php');
		require_once('db.php');
	?>
	<head>
		<title>LinkedIn Bot Admin Interface</title>
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
  		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
		<style>
			.linkedin-color{
				color: rgb(0,119,181);
			}
			.column {
			    background: #f2f2f2;
			    border: 1px solid gray;
			    overflow: hidden;
			}
			.column-1 {
			    float: left;
			    width: 25%;
			    border-radius: 0px 10px 10px 0px;
			}
			.column-2 {
				display: inline-block;
				width: 49%;
			    border-radius: 10px 10px 10px 10px;
			}
			.column-3 {
			    float: right;
			    width: 25%;
			    border-radius: 10px 0px 0px 10px;
			}
			.column .title{
				font-size: 16px;
				padding: 10px 0;
				border-bottom: 1px solid gray;
				font-weight: bold;
				background-color: rgb(66,139,202);
				margin: 0;
			}
			.key-words-list{
				border: 1px solid black;
				margin: 7px;
				background-color: white;
				text-align: left;
			}
			.key-words-list ul{
				list-style-type: none;
			    margin: 0;
			    padding: 0;
			    min-height: 50px;
			}
			.key-words-list ul li{
				display: inline-block;
				border-radius: 5px;
				margin: 2px;
				padding: 2px 5px;
			}
			.key-words-list i{
    			margin-left: 10px;
    			color: rgb(217,83,79);
    			cursor: pointer;
			}
			.ajax-response{
				position: absolute;
				width: 50%;
				border-radius: 10px;
				left: 25%;
				text-align: center;
				visibility: hidden;
				opacity: 0;
				-webkit-transition: visibility 1s, opacity 1s;
				transition: visibility 1s, opacity 1s;
			}
			hr {
			    height: 1px;
			    border: 0;
			    border-top: 1px solid #ccc;
			    margin: 1em 0;
			}


			#random-conv{
				color: white;
				cursor: pointer;
			}
			.conversation .conv-msg{
				max-height: 600px;
				overflow-y: scroll;
			}
			div.bot, div.user{
				width: 60%;
				margin: 1%;
				border-radius: 10px;
				padding: 5px;
			}
			div.bot{
				text-align: right;
				margin-left: 39%;
				background-color: rgb(66,139,202);
				color: white;
			}
			div.user{
				text-align: left;
				background-color: white;
			}
			p.date{
				font-weight: bold;
			}
		</style>
	</head>
	<body>

		<div class="ajax-response">
			<p class="alert"></p>
		</div>

		<h1 class="text-center linkedin-color"><i class="fa fa-linkedin-square" aria-hidden="true"></i> LinkedIn Bot Admin Interface</h1>

		<div class="text-center">
			<div class="column column-1">
				<h6 class="title">Settings</h6>
				<div class="content">
					<div class="alert alert-info"><p class="description">Here you can add or delete key words the bot will use to search people</p></div>
					<input class="form-control" type="text" placeholder="Key word" id="key-word" />
					<button class="btn btn-sm btn-primary" type="button" id="saveKeyWord">Save key word</button>
					<div class="key-words-list">
						<ul>
							<?php
								foreach(getKeyWords() as $value) {
									echo '<li class="alert-info"><span class="key-word-item">'.$value['key_word'].'</span><i class="fa fa-times-circle-o" aria-hidden="true" id='.$value['ID'].'></i></li>';
								}
							?>
						</ul>
					</div>
					<br/>
					<div class="alert alert-info"><p class="description">The default message to send to every user who just accept the invitation</p></div>
					<textarea class="form-control" rows="5"></textarea><br/>
					<button class="btn btn-primary btn-sm" id="save-default-msg">Save default messsage</button>
				</div>
			</div>
			<div class="column column-2 conversation">
				<h6 class="title">Random unread conversation - <a id="random-conv">Another random conversation!</a></h6>
				<div class="content">
					<div class="conv-msg">
						
					</div>
					<hr/>
					<div class="answer-form">
						<textarea class="form-control" rows="5"></textarea><br/>
						<button class="btn btn-primary btn-md btn-block" id="send-msg">Send</button>
					</div>
				</div>
			</div>
			<div class="column column-3">
				<h6 class="title">Statistiques</h6>
				<div class="content">
					<br/>
					<p><b>Number of connections</b></p>
					<h3 class="stats-value" id="getAllConnections">???</h3><hr/>
					<p><b>Number of connections asked by the bot</b></p>
					<h3 class="stats-value" id="getAllConnectionsSent">???</h3><hr/>
					<p><b>Number of msg sent by the bot</b></p>
					<h3 class="stats-value" id="getAllMsgSent">???</h3>
				</div>
			</div>
		</div>

	</body>
</html>

<script>
$(document).ready(function(){

	function post(data, callback){
		$.post( "actions.php", data).done(function( resp ){
			resp = JSON.parse(resp);
			if(resp.showMsg)
				showBar(resp.success, resp.msg);
			callback(resp);
		}, "json");
	}

	function showBar(isSuccess, msg){
		$('.ajax-response').css('visibility','visible').css('opacity', 1);
		$('.ajax-response p').html('<i class="fa fa-'+(isSuccess?"check":"exclamation-triangle")+'" aria-hidden="true"></i> '+msg);
		$('.ajax-response p').addClass(isSuccess?'alert-success':'alert-danger').removeClass(!isSuccess?'alert-success':'alert-danger');
		setTimeout(function(){$('.ajax-response').css('visibility','hidden').css('opacity', 0)}, 4000);
	}

	function saveKeyWord(){
		if($('#key-word').val() != ''){
			post({'action': 'saveKeyWord', 'val': $('#key-word').val()}, function(resp){
				$(".key-words-list ul").append('<li class="alert-info"><a class="key-word-item" id="'+resp.id+'">'+$('#key-word').val()+'</a><i class="fa fa-times-circle-o" aria-hidden="true" id='+resp.id+'></i></li>');
				// add the click listener to the new <i>
				$('i[id="'+resp.id+'"]').click(function(){
					var id = $(this).attr('id');
					post({'action': 'delKeyWord', 'id': id}, function(resp){
						$('i[id="'+id+'"]').parent().remove();
					});
				})
				$('#key-word').val('');
			});
		}else{
			showBar(false, 'Please enter a key word');
		}
	}

	function refreshStats(){
		$('.stats-value').each(function(){
			var value = $(this).attr('id');
			post({'action': 'stats', 'function': value}, function(resp){
				$('#'+value).text(resp.value);
			});
		});
	}

	function getRandomUnreadConversation(){
		$('.conversation .conv-msg').html('<br/><i class="fa fa-circle-o-notch fa-spin" style="font-size:24px"></i>');
		post({'action': 'randomUnreadConv'}, function(resp){
			$('.conversation .conv-msg').html('');
			var msgs = $.parseJSON(resp.conv);
			$.each(msgs, function(index, val){
				$('.conversation .conv-msg').append(
					'<div class="'+val.by+'"><p class="date">'+(val.by=='bot'?'BOT':'USER')+' - '+val.date+'</p><p class="text">'+val.msg+'</p></div>'
				);
			});
			// scroll to bottom
			$(".conv-msg").animate({ scrollTop: $('.conv-msg')[0].scrollHeight }, "slow");

			// saving conv_id for answer
			$('#send-msg').attr('conv-id', ''+resp.conv_id);
		});
	}

	$('#send-msg').click(function(){
		console.log($(this).attr('id'));
	});

	$('.key-words-list i').on("click", function(){		// not worrking
		var id = $(this).attr('id');
		post({'action': 'delKeyWord', 'id': id}, function(resp){
			$('i[id="'+id+'"]').parent().remove();
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
	$('#random-conv').click(getRandomUnreadConversation);
	


	// every  10 sec, we refresh stats and the first time too
	refreshStats();
	getRandomUnreadConversation();
	window.setInterval(refreshStats, 5000);
});
</script>