<?php
	require_once('const.php');
	require_once('db.php');
	require_once('Linkedin.php');

	// [success=>bool, response=>array]
	$json = array('success'=>true, 'msg'=>'Nothing appened', 'showMsg'=>true);

	if(!isset($_POST['action'])){
		$json['success'] = false;
		exit(json_encode($json));
	}
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
		$json['unreadConv'] = json_encode(Linkedin::noInst()->getUnreadConversations());
	}
	// RANDOM CONVERSATION
	else if($_POST['action'] == 'getMsgConv'){
		// first all unread conversation
		// !!! [IMPORTANT] : we do not instantiate a new Linkedin --> means that this is in the same directory than the bot for cookies !
    	$conv = $_POST['conv'];
    	//then all msgs frome this conv
$conv='6315817753265651712';
    	
    	$msgs = Linkedin::noInst()->getAllMsg($conv);
    	$json['showMsg'] = false;
    	$json['msgs'] = json_encode($msgs);
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
		$msg = Linkedin::noInst()->markConversationAsRead($_POST['conv']);
	}
	// SEND MESSAGE
	else if($_POST['action'] == 'sendMsg'){
		$msg = Linkedin::noInst()->send_msg($_POST['profile_id'], $_POST['msg']);
		$json['msg'] = 'Your msg has been sent!';
	}
	//DEFAULT MSG
	else if($_POST['action'] == 'getDefaultMsg'){
		$json['showMsg'] = false;
		$json['defaultMsg'] = getDefaultMsg()['msg'];
	}
	else if($_POST['action'] == 'setDefaultMsg'){
		$json['msg'] = 'Default message updated!';
		saveDefaultMsg($_POST['msg']);
	}

	exit(json_encode($json));
