<?php
	require_once('../const.php');
	require_once('../db.php');
	require_once('../class/Linkedin.php');

	// [success=>bool, response=>array]
	$json = array('success'=>true, 'msg'=>'Nothing appened', 'showMsg'=>true);
	$account = $_GET['account'] ?? 0;

	if(!isset($_POST['action'])){
		$json['success'] = false;
		exit(json_encode($json));
	}

	// GENERAL 
	else if($_POST['action'] == 'delete'){ 
		$check = delete($_POST['table'], $_POST['id'], $account); 
	   	$json['msg'] = $check?'Item deleted':'An error occured'; 
	   	$json['success'] = $check; 
	}
	// INDEX PAGE

	// DISCONNECT
	else if($_POST['action'] == 'botDisconnect'){
		$json['showMsg'] = false;
		$json['disconnect'] = getIsDisconnect($account);
	}
	else if($_POST['action'] == 'changeBotDisconnect'){
		$json['showMsg'] = false;
		setIsDisconnect($_POST['disconnect'], $account);
	}
	// ON OFF
	else if($_POST['action'] == 'isOn'){
		$json['showMsg'] = false;
		$json['isOn'] = getIsOn();
	}
	else if($_POST['action'] == 'changeBotState'){
		$json['msg'] = 'The bot is now '.($_POST['state']==1?'On':'Off');
		if($_POST['state'])
			setAction('The bot is starting...');
		else
			setAction('The bot is off.');
		setIsOn($_POST['state']?1:0);
	}
	// KEY WORDS
	else if($_POST['action'] == 'getKeyWords'){
		$json['showMsg'] = false;
		$json['keyWords'] = getKeyWords(true, $account);
	}
	else if($_POST['action'] == 'saveKeyWord'){
		saveKeyWord($_POST['val'], $account);
		$json['msg'] = 'New key word saved';
		$json['id'] = $db->lastInsertId();
	}
	else if($_POST['action'] == 'delKeyWord'){
		delKeyWord(intval($_POST['id']));
		$json['msg'] = 'Key word deleted';
	}
	// STATS
	else if($_POST['action'] == 'stats'){
		$json['showMsg'] = false;
		$func = $_POST['function'];
		if($func == 'getAction'){
			$json['value'] = $func($account);
		}else{
			$json['value'] = count($func($account));
		}
	}
	else if($_POST['action'] == 'unreadConv'){
		$json['showMsg'] = false;
		// all conversations not read and where watson try but couldn't answer
		$json['unreadConv'] = getMsgReceived($account, null, null, null, null, null, true, false);
	}
	// RANDOM CONVERSATION
	else if($_POST['action'] == 'getMsgConv'){
    	$json['showMsg'] = false;
    	$json['msgs'] = getConversation($_POST['conv']);
	}
	else if($_POST['action'] == 'checkConvAnswered'){
		$json['showMsg'] = false;
		$li = new Linkedin($account);
		$conv = $li->getAllMsg($_POST['conv']);
		// we save msgs just in case we answered it with human way
		foreach ($conv as $msg) {
			$li->saveMsg($msg);
		}
		sleep(1);
	}
	//USER INFOS
	else if($_POST['action'] == 'getUserInformations'){
		$json['showMsg'] = false;
		$json['data'] = (new Linkedin($account))->getUserInformations($_POST['profile_id']);
	}
	//MARK AS READ
	else if($_POST['action'] == 'markRead'){
		$json['showMsg'] = isset($_POST['show']);
		$json['msg'] = 'Conversation marked as read!';
		$msg = setRead($_POST['conv']);
		resetContext($_POST['conv']);
	}
	// SEND MESSAGE
	else if($_POST['action'] == 'sendMsg'){
		$msg = (new Linkedin($account))->sendMsg($_POST['profile_id'], $_POST['msg']);
		$json['msg'] = 'Your msg has been sent!';
	}

	// --------------------- TEMPLATE MANAGER -----------------------

	else if($_POST['action'] == 'saveTemplate'){
		$json['msg'] = 'Template saved!';
		saveTemplate($_POST['msg'], $account);
	}
	else if($_POST['action'] == 'getAllTemplates'){
		$json['showMsg'] = false;
		$json['templates'] = getAllTemplates(null, $account);
	}
	else if($_POST['action'] == 'getActiveTemplates'){
		$json['showMsg'] = false;
		$json['templates'] = getAllTemplates(true, $account);
	}
	else if($_POST['action'] == 'changeTemplateState'){
		$state = $_POST['state'] == 'true';
		$json['msg'] = 'The template '.$_POST['id'].' is now '.($state?'active':'inactive');
		setTemplateState($_POST['id'], $state);
	}

	// --------------------- UNIQUE TEMPLATE MANAGER -----------------------

	else if($_POST['action'] == 'getMessage'){
		$json['showMsg'] = false;
		$json['value'] = getTemplate($_POST['template'])['msg'];
	}
	else if($_POST['action'] == 'getNumberSent'){
		$json['showMsg'] = false;
		$number = count(getMsgSent($account, null, null, null, $_POST['template']));
		$total = count(directQuery('SELECT ID FROM msg_conversation WHERE by_bot=true AND template_msg!=0 AND accountID='.$account));
		$percent = $total!=0?round($number*100/$total):0;
		$json['value'] = $number.' ('.$percent.'% of first message with this template)';
	}
	else if($_POST['action'] == 'getNumberReceived'){
		$json['showMsg'] = false;
		$number = count(directQuery('SELECT DISTINCT conv_id from msg_conversation WHERE by_bot=false AND template_msg='.$_POST['template'])??[]);
		$total = count(directQuery('SELECT ID FROM msg_conversation WHERE by_bot=true AND template_msg='.$_POST['template']));
		$percent = $total!=0?round($number*100/$total):0;
		$json['value'] = $number.' ('.$percent.'% of messages sent with this template had a response)';
	}

	// ----------------- ACCOUNTS MANAGERS ------------------------

	else if($_POST['action'] == 'getAllAccounts'){
		$json['showMsg'] = false;
		$json['value'] = getAllAccounts();
	}
	else if($_POST['action'] == 'saveNewAccount'){
		$json['newId'] = saveNewAccount($_POST['email'], $_POST['password']);
		$json['msg'] = 'New account saved';
	}
	else if($_POST['action'] == 'changeAccountActive'){
		$state = $_POST['state'] == 'true';
		changeAccountActive($_POST['id'], $state);
		setAction($state ? 'The bot will reconnect soon...' : 'This account is inactive', $account);
		$json['msg'] = 'This account is now '.($state?'':'in').'active: '.$_POST['id'];
	}
	else if($_POST['action'] == 'changeChatActive'){
		$state = $_POST['state'] == 'true';
		changeChatActive($_POST['id'], $state);
		$json['msg'] = $state ? 'This account will chat with all users.' : 'This account will only chat with users related to the bot.';
	}
	else if($_POST['action'] == 'deleteAccount'){
		deleteAccount($_POST['id']);
		$json['msg'] = 'Account deleted';
	}

	exit(json_encode($json));
