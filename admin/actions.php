<?php
	require_once('../const.php');
	require_once('../db.php');
	require_once('../class/Linkedin.php');

	// [success=>bool, response=>array]
	$json = array('success'=>true, 'msg'=>'Nothing appened', 'showMsg'=>true);

	if(!isset($_POST['action'])){
		$json['success'] = false;
		exit(json_encode($json));
	}

	// GENERAL
	else if($_POST['action'] == 'delete'){
		$check = delete($_POST['table'], $_POST['id']);
		$json['msg'] = $check?'Item deleted':'An error occured';
		$json['success'] = $check;
	}

	// INDEX PAGE

	// ON OFF
	else if($_POST['action'] == 'isOn'){
		$json['showMsg'] = false;
		$json['isOn'] = getIsOn();
	}
	else if($_POST['action'] == 'changeBotState'){
		$json['msg'] = 'The bot is now '.($_POST['state']==1?'On':'Off');
		setIsOn($_POST['state']?1:0);
	}
	// KEY WORDS
	else if($_POST['action'] == 'getKeyWords'){
		$json['showMsg'] = false;
		$json['keyWords'] = getKeyWords();
	}
	else if($_POST['action'] == 'saveKeyWord'){
		saveKeyWord($_POST['val']);
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
		$json['value'] = count($func());
	}
	else if($_POST['action'] == 'unreadConv'){
		$json['showMsg'] = false;
		// all conversations not read
		$json['unreadConv'] = getMsgReceived('%%', '%%', '%%', '%%', '%%', '%%', false);
	}
	// RANDOM CONVERSATION
	else if($_POST['action'] == 'getMsgConv'){
    	$json['showMsg'] = false;
    	$json['msgs'] = getConversation($_POST['conv']);
	}
	//USER INFOS
	else if($_POST['action'] == 'getUserInformations'){
		$json['showMsg'] = false;
		$json['data'] = Linkedin::noInst()->getUserInformations($_POST['profile_id']);
	}
	//MARK AS READ
	else if($_POST['action'] == 'markRead'){
		$json['showMsg'] = isset($_POST['show']);
		$json['msg'] = 'Conversation marked as read!';
		$msg = setRead($_POST['conv']);
	}
	// SEND MESSAGE
	else if($_POST['action'] == 'sendMsg'){
		$msg = Linkedin::noInst()->sendMsg($_POST['profile_id'], $_POST['msg']);
		$json['msg'] = 'Your msg has been sent!';
	}
	//TEMPLATE
	else if($_POST['action'] == 'saveTemplate'){
		$json['msg'] = 'Template saved!';
		saveTemplate($_POST['msg']);
	}

	// --------------------- TEMPLATE MANAGER -----------------------

	else if($_POST['action'] == 'getAllTemplates'){
		$json['showMsg'] = false;
		$json['templates'] = getAllTemplates();
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
		$number = count(getMsgSent(null, null, null, $_POST['template']));
		$total = count(getMsgSent());
		$percent = $total!=0?round($number*100/$total):0;
		$json['value'] = $number.' ('.$percent.'%)';
	}
	else if($_POST['action'] == 'getNumberReceived'){
		$json['showMsg'] = false;
		$number = count(getMsgReceived(null, null, null, $_POST['template'])??[]);
		$total = count(getMsgReceived());
		$percent = $total!=0?round($number*100/$total):0;
		$json['value'] = $number.' ('.$percent.'%)';
	}

	exit(json_encode($json));
