<!DOCTYPE html>
<html>
	<?php 
		require_once('../const.php');
		require_once('../db.php');
	?>
	<head>
		<title>Watson Test Interface</title>
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
		<link rel="stylesheet" href="style.css">

		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
  		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
  		<script src="bootbox.min.js"></script>
  		<script src="functions.js"></script>
	</head>
	<body>
		<div class="container">
			<h2>WATSON TEST</h2>
			<a href="index.php"><button class="btn btn-sm btn-primary"><i class="fa fa-arrow-left"></i> Back</button></a>
			<br><br>
			<p class="alert alert-info">In this interface, you are a LinkedIn user named <b>Gustaf Hector</b></p>
			<div class="alert alert-info">
				<div class="conv-msg"></div>
			</div>
			<hr/><br/>
			<div class="answer-form">
				<input autofocus placeholder="What do you want to test?" type="text" class="form-control" id="answer-conv-msg" rows="5" /><br/>
				<button class="btn btn-primary btn-md btn-block" id="send-msg">Send</button>
				<br><br>
				<button class="btn btn-block btn-warning" id="sendTemplate">Send a random template to you !</button>
				<br><br/>
				<button class="btn btn-block btn-danger" id="reset">Reset and try again</button>
			</div>
		</div>
	</body>
</html>

<script>
$(document).ready(function(){
	function genContext(action, msg){
		var resp = '';
		if(msg === undefined){
			msg = '';
		}
		$.post('watson_context.php', {'action': action, 'msg': msg}).done(function( r ){
			if(action == 'getResponse'){
				if(r != ''){
					newMsg('bot', r);
				}
			}
		});
	}

	function newMsg(whoSend, msg){
		$('.conv-msg').append(
			'<div class="convMsg '+whoSend+'"><p class="date">'+whoSend+'</p><p class="text">'+msg.replace(/\\n/g, "<br/>")+'</p></div>'
		);
		$("html").animate({ scrollTop: $('.conv-msg')[0].scrollHeight }, "slow");
	}

	$('#sendTemplate').click(function(){
		post({'action':'getAllTemplates'}, function(resp){
			var templates = resp.templates;
			var r = Math.floor((Math.random() * (templates.length)));
			newMsg('bot', templates[r]['msg']);
		});
		genContext('init');
		$('#answer-conv-msg').focus();
	});

	$('#send-msg').click(function(){
		var text = $('#answer-conv-msg').val();
		if(text !== ''){
			newMsg('Me', text);
			$('#answer-conv-msg').val("");
			genContext('getResponse', text);
		}
		$('#answer-conv-msg').focus();
	});

	$('#reset').click(function(){
		genContext('reset');
		$('.conv-msg').html('');
		$('#answer-conv-msg').focus();
	})

	$('#answer-conv-msg').keyup(function(e){
		if(e.keyCode == 13){
			var text = $('#answer-conv-msg').val();
			if(text !== ''){
				newMsg('Me', text);
				$('#answer-conv-msg').val("");
				genContext('getResponse', text);
			}
		}
	});

	genContext('reset');
});
</script>