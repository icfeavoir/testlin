<?php
	require_once('../class/Watson.php');
	require_once('../const.php');

    $watson = new Watson(WATSON_USERNAME, WATSON_PASSWORD, WATSON_CONVERSATION);
 

	if(isset($_POST['action'])){
		$action = $_POST['action'];
	}else{
		exit('no action');
	}

	if($action == 'reset'){
		file_put_contents('context', null);
	}else if($action == 'init'){
		$defaultContext = $watson->chat('BotYoupicDefaultStart')->context;
		$defaultContext->firstName = 'Gustaf';
		$defaultContext->lastName = 'Hector';
		$defaultContext->job = 'YouPic';
		$defaultContext->myLink = $_POST['msg'];
		file_put_contents('context', serialize($defaultContext));
	}else if($action == 'getResponse'){
		$context = unserialize(file_get_contents('context'));
		$resp = $watson->chat($_POST['msg'], $context??null);
		$newContext = $resp->context;
		if($context == null){
			// first context
			$newContext->firstName = 'Gustaf';
			$newContext->lastName = 'Hector';
			$newContext->job = 'YouPic';
		}
		file_put_contents('context', serialize($newContext));
		if(count($resp->output->text) > 0){
			echo $resp->output->text[0];
		}
	}