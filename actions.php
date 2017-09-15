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
	// KEY WORDS
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
	// RANDOM CONVERSATION
	else if($_POST['action'] == 'randomUnreadConv'){
		// first all unread conversation
		// !!! [IMPORTANT] : we do not instantiate a new Linkedin --> means that this is in the same directory than the bot for cookies !
    	$unread = Linkedin::noInst()->getUnreadConversations();
    	// then select a random one
    	$conv = $unread[rand(0, count($unread)-1)];
    	$conv = '6313350241940750337';
    	//then all msgs frome this conv
    	$msgs = Linkedin::noInst()->getAllMsg($conv);

    	$json['showMsg'] = false;
    	$json['conv'] = json_encode($msgs);
    	$json['conv_id'] = $conv;
	}
	// SEND MESSAGE
	else if($_POST['action'] == 'sendMsg'){
		$msg = Linkedin::noInst()->send_msg();
	}

	exit(json_encode($json));
