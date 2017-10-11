<?php

	try{
		$db = new PDO('mysql:host='.HOST.';dbname='.DB_NAME.';charset=utf8', DB_USER, DB_PASSWORD);
	}catch(Exception $e){
		exit('Error: '.$e);
	}
	/* DISCONNECT */

	/**
	* Get if the bot is disconnect
	*
	* @return Bool True if disconnect
	*/
	function getIsDisconnect($account){
		global $db;
		$statement = $db->prepare('SELECT is_disconnect FROM bot_disconnect WHERE accountID=:account LIMIT 1');
		$statement->execute(array(':account'=>$account));
		return $statement->fetch()['is_disconnect'] == true;
	}

	/**
	* Change the state of the bot (reconnect)
	*
	* @param bool $isDisconnect true for the bot disconnected, false for the bot connected
	*/
	function setIsDisconnect($isDisconnect, $account){
		global $db;
		$statement = $db->prepare('UPDATE bot_disconnect SET is_disconnect = :is_disconnect WHERE accountID=:accountID');
		$statement->bindValue(':is_disconnect', $isDisconnect, PDO::PARAM_INT);
		$statement->bindValue(':accountID', $account);
		$statement->execute();
	}

	/**
	* Get action the bot is currently doing
	*
	* @return String the action
	*/
	function getAction($account){
		global $db;
		$statement = $db->prepare('SELECT action FROM bot_action WHERE accountID=:account LIMIT 1');
		$statement->execute(array(':account'=>$account));
		return $statement->fetch()['action'];
	}

	/**
	* Change the action of the bot
	*
	* @param string $action The action
	*/
	function setAllAction($action){
		global $db;
		$statement = $db->prepare('UPDATE bot_action SET action = :action');
		$statement->execute(array(':action'=>$action));
	}
	function setAction($action, $account){
		global $db;
		$statement = $db->prepare('UPDATE bot_action SET action = :action WHERE accountID=:account');
		$statement->execute(array(':action'=>$action, ':account'=>$account));
	}

	/* ON - OFF */

	/**
	* Get the current state of the bot
	*
	* @return Bool True is the bot on
	*/
	function getIsOn(){
		global $db;
		$statement = $db->prepare('SELECT isOn FROM bot_on_off LIMIT 1');
		$statement->execute();
		return $statement->fetch()['isOn'] == true;
	}

	/**
	* Change the state of the bot (on off)
	*
	* @param bool $isOn true for the bot on, false for the bot off
	*/
	function setIsOn($isOn){
		global $db;
		$statement = $db->prepare('UPDATE bot_on_off SET isOn = :isOn');
		$statement->bindValue(':isOn', $isOn, PDO::PARAM_INT);
		$statement->execute();
	}


	/**
	* Get all the connections request the bot sent
	*
	* @return Array with all connections request
	*/
	function getAllConnectionsSent($connect){
		global $db;
		$statement = $db->prepare('SELECT * FROM connect_asked WHERE accountID=:connect ORDER BY ID');
		$statement->execute(array(':connect'=>$connect));
		return $statement->fetchAll();
	}

	/**
	* Check if the bot already sent a connect request to this user
	*
	* @param string $profile_id The id of the user to check
	*
	* @return false if connect never send, the query response else
	*/
	function isConnectSent($profile_id, $account){
		global $db;
		$statement = $db->prepare('SELECT * FROM connect_asked WHERE profile_id= :profile_id AND accountID=:account LIMIT 1');
		$statement->execute(array(':profile_id' => $profile_id, ':account'=>$account));
		if($statement->rowCount() == 0)
			return false;
		return $statement->fetch();
	}

	/**
	* Save the connect request for an user
	*
	* @param string $profile_id The id of the user to check
	*
	*/
	function saveConnectSent($profile_id, $account){
		global $db;
		$statement = $db->prepare('INSERT INTO connect_asked (profile_id, accountID) VALUES (:profile_id, :account)');
		$statement->execute(array(':profile_id' => $profile_id, ':account'=>$account));
	}

	/* -------------- CONVERSATIONS --------------------- */

	/**
	* Get all msg of a conversation
	*
	* @param string $conv Conversation with this id
	*
	* @param bool $fromUser (optional) Use the parameter $conv the profile_id and not the con_id
	*
	* @return Array with all msgs
	*/
	function getConversation($id, $fromUser=false){
		global $db;
		$statement = $db->prepare('SELECT * FROM msg_conversation WHERE conv_id=:id ORDER BY date');
		$statement->execute(array(':id'=>$id));
		return $statement->fetchAll(PDO::FETCH_ASSOC);
	}

	
	/**
	* Get all msg sent by the bot
	*
	* @param string $user Only messages to this user
	*
	* @param string $conv_id Only messages from this conversration
	*
	* @param string $msg_id Only this msg
	*
	* @param int $template Only messages from this template
	*
	* @return Array with all msg
	*/
	function getMsgSent($account, $profile_id='%%', $conv_id='%%', $msg_id='%%', $template='%%', $watson_msg='%%'){
		global $db;
		$profile_id = empty($profile_id)?'%%':$profile_id;
		$conv_id = empty($conv_id)?'%%':$conv_id;
		$msg_id = empty($msg_id)?'%%':$msg_id;
		$template = empty($template)?'%%':$template;
		$watson_msg = empty($watson_msg)?'%%':$watson_msg;
		$statement = $db->prepare('SELECT * FROM msg_conversation WHERE by_bot=1 AND profile_id LIKE :profile_id AND conv_id LIKE :conv_id AND msg_id LIKE :msg_id AND template_msg LIKE :template  AND watson_msg LIKE :watson_msg AND accountID=:account ORDER BY ID');
		$statement->execute(array(':profile_id'=>$profile_id, ':conv_id'=>$conv_id, ':msg_id'=>$msg_id, ':template'=>$template, ':watson_msg'=>$watson_msg, ':account'=>$account));
		return $statement->rowCount()==0?null:$statement->fetchAll(PDO::FETCH_ASSOC);
	}


	/**
	* Check if the bot already sent a msg with a specific ID in a specific conversation
	*
	* @param string $profile_id The id of the user to check
	*
	* @return false if msg never send, the query response else
	*/
	function isMsgSentId($conv, $msg_id, $account){
		global $db;
		$statement = $db->prepare('SELECT * FROM msg_conversation WHERE by_bot=1 AND conv_id = :conv AND msg_id = :msg_id AND accountID=:account');
		$statement->execute(array(':conv' => $conv, ':msg_id' => $msg_id, ':account'=>$account));
		if($statement->rowCount() == 0)
			return false;
		return $statement->fetch();
	}

	/**
	* Save the msg sent from the bot to an user
	*
	*/
	function saveMsgSent($profile_id, $msg, $conv, $msg_id, $date, $template=0, $watson_msg=0, $context=null, $account){
		global $db;
		$date = gettype($date)=='string'?$date:date('Y-m-d G:i:s', $date);
		$statement = $db->prepare('INSERT INTO msg_conversation (by_bot, profile_id, conv_id, msg_id, template_msg, msg, watson_msg, is_read, watson_context, date, accountID) VALUES (1, :profile_id, :conv, :msg_id, :template, :msg, :watson_msg, :read, :context, :date, :account)');
		$statement->execute(array(':profile_id' => $profile_id, ':conv' => $conv, ':msg_id' => $msg_id, ':template'=>$template, ':msg' => $msg, ':watson_msg'=>$watson_msg, ':read'=>true, ':context'=>$context, ':date'=>$date, ':account'=>$account));
	}


	/**
	* Get all msg received by the bot
	*
	* @param string $user Only messages from this user
	*
	* @param string $conv_id Only messages from this conversration
	*
	* @param string $msg_id Only this msg
	*
	* @param int $template Only messages from this template
	*
	* @return Array with all msg
	*/
	function getMsgReceived($account, $profile_id='%%', $conv_id='%%', $msg_id='%%', $template='%%', $watson_msg='%%', $watson_try='%%', $is_read='%%'){
		global $db;
		$profile_id = empty($profile_id)?'%%':$profile_id;
		$conv_id = empty($conv_id)?'%%':$conv_id;
		$msg_id = empty($msg_id)?'%%':$msg_id;
		$template = empty($template)?'%%':$template;
		$watson_msg = getType($watson_msg)!='boolean'?'%%':intval($watson_msg);
		$watson_try = getType($watson_try)!='boolean'?'%%':intval($watson_try);
		$is_read = getType($is_read)!='boolean'?'%%':intval($is_read);

		$statement = $db->prepare('SELECT * FROM msg_conversation WHERE by_bot=0 AND profile_id LIKE :profile_id AND conv_id LIKE :conv_id AND msg_id LIKE :msg_id AND template_msg LIKE :template AND watson_msg LIKE :watson_msg AND watson_try LIKE :watson_try AND is_read LIKE :is_read AND accountID=:account ORDER BY date');
		$statement->execute(array(':profile_id'=>$profile_id, ':conv_id'=>$conv_id, ':msg_id'=>$msg_id, ':template'=>$template, ':watson_msg'=>$watson_msg, ':watson_try'=>$watson_try, ':is_read'=>$is_read, ':account'=>$account));
		return $statement->rowCount()==0?null:$statement->fetchAll(PDO::FETCH_ASSOC);
	}
	
	/**
	* Save the msg received by the bot
	*
	* @param string $profile_id The id of the user to check
	* @param string $pmsg The msg
	* @param string $conv The conversation ID (LinkedIn)
	* @param string $msg_id The msg ID (LinkedIn)
	* @param int $template (optional) The ID of the msg template used
	*
	*/
	function saveMsgReceived($profile_id, $msg, $conv, $msg_id, $date, $template=0, $watson_msg=false, $account){
		global $db;
		$date = gettype($date)=='string'?$date:date('Y-m-d G:i:s', $date);
		$statement = $db->prepare('INSERT INTO msg_conversation (by_bot, profile_id, conv_id, msg_id, template_msg, msg, watson_msg, date, accountID) VALUES (0, :profile_id, :conv, :msg_id, :template, :msg, :watson_msg, :date, :account)');
		$statement->execute(array(':profile_id' => $profile_id, ':conv' => $conv, ':msg_id' => $msg_id, ':template'=>$template, ':msg' => $msg, ':watson_msg'=>$watson_msg, ':date'=>$date, ':account'=>$account));
	}

	/**
	* Get the previous msg of a conversation
	*
	* @param string $conv_id The conversation
	*
	* @param bool $fromUser (optional) True if you want the previous msg of the user, False (default) for the bot
	*
	* @return Array with the previous msg
	*/
	function getLastMsg($conv_id, $fromUser=false){
		global $db;
		// first we get the ID of the msg now (supposed to be only one msg with this msg_id --> [0])
		$statement = $db->prepare('SELECT * FROM msg_conversation WHERE by_bot=:by AND conv_id=:conv_id ORDER BY date DESC LIMIT 1');
		$statement->execute(array(':by'=>!$fromUser, ':conv_id'=>$conv_id));
		return $statement->rowCount()==0?null:$statement->fetch(PDO::FETCH_ASSOC);
	}

	/**
	* Set a conv to read or unread
	*
	* @param string $conv_id The conv
	*
	* @param bool $read (optional) True for read
	*
	*/
	function setRead($conv_id, $is_read=true){
		global $db;
		$statement = $db->prepare('UPDATE msg_conversation SET is_read=:is_read WHERE conv_id=:conv_id');
		$statement->execute(array(':is_read'=>$is_read, ':conv_id'=>$conv_id));
	}

	/**
	* Change the watson_try value (if true and msg not read => watson can't answer)
	*
	* @param string $msg_id The id of the message
	*
	* @param bool $read (optional) True for try
	*
	*/
	function setWatsonTry($msg_id, $try=true){
		global $db;
		$statement = $db->prepare('UPDATE msg_conversation SET watson_try=:try WHERE msg_id=:msg_id');
		$statement->execute(array(':try'=>$try, ':msg_id'=>$msg_id));
	}

	/**
	* Get the last Watson context of a conversation
	*
	* @param string $conv_id
	*
	* @return array() The last context or null if no context created
	*
	*/
	function getLastContext($conv_id){
		global $db;
		$statement = $db->prepare('SELECT watson_context FROM msg_conversation WHERE watson_context IS NOT NULL AND conv_id=:conv_id ORDER BY date DESC LIMIT 1');
		$statement->execute(array(':conv_id'=>$conv_id));
		return $statement->fetch()['watson_context'];
	}

	/**
	* Change the Watson context of a msg
	*
	* @param string $msg_id The id of the message
	*
	* @param string $context The watson context
	*
	*/
	function setContext($msg_id, $context){
		global $db;
		$statement = $db->prepare('UPDATE msg_conversation SET watson_context=:context WHERE msg_id=:msg_id');
		$statement->execute(array(':context'=>$context, ':msg_id'=>$msg_id));
	}

	/**
	* Reset the context
	*
	* @param string $conv_id The conversation
	*
	*/
	function resetContext($conv_id){
		global $db;
		$statement = $db->prepare('UPDATE msg_conversation SET watson_context=NULL WHERE conv_id=:conv_id');
		$statement->execute(array(':conv_id'=>$conv_id));
	}

	/* --------------------------------------- */

	/**
	* Get all connections
	*
	* @return Array of all connections
	*/
	function getAllConnections($account){
		global $db;
		$statement = $db->prepare('SELECT * FROM connect_list WHERE accountID=:account ORDER BY ID');
		$statement->execute(array(':account'=>$account));
		return $statement->fetchAll();
	}

	/**
	* Check if the user accepted the connect request
	*
	* @param string $profile_id The id of the user to check
	*
	* @return false if msg never send, the query response else
	*/
	function isConnectedTo($profile_id, $account){
		global $db;
		$statement = $db->prepare('SELECT * FROM connect_list WHERE profile_id= :profile_id AND accountID=:account');
		$statement->execute(array(':profile_id' => $profile_id, ':account'=>$account));
		if($statement->rowCount() == 0)
			return false;
		return $statement->fetch();
	}

	/**
	* Save that the user accepted the connect request
	*
	* @param string $profile_id The id of the user to check
	*
	*/
	function saveConnectedTo($profile_id, $account){
		global $db;
		$statement = $db->prepare('INSERT INTO connect_list (profile_id, accountID) VALUES (:profile_id, :account)');
		$statement->execute(array(':profile_id' => $profile_id, ':account'=>$account));
	}



	/**
	* Get all key words saved
	*
	* @param boolean $all To get all key words
	*
	* @return array of key_words
	*/
	function getKeyWords($all=false, $account){
		global $db;
		if($all){
			$statement = $db->prepare('SELECT * FROM key_word_list WHERE accountID=:account');
			$statement->execute(array(':account'=>$account));
		}else{
			$statement = $db->prepare('SELECT * FROM key_word_list WHERE done=:done AND accountID=:account');
			$statement->execute(array(':done'=>0, ':account'=>$account));
		}
		return $statement->fetchAll(PDO::FETCH_ASSOC);
	}

	/**
	* Save a new key_word in databse
	*
	* @param string $key_word The key word to save
	*
	*/
	function saveKeyWord($key_word, $account){
		global $db;
		$statement = $db->prepare('INSERT INTO key_word_list (key_word, accountID) VALUES (:key_word, :account)');
		$statement->execute(array(':key_word' => $key_word, ':account'=>$account));
	}

	/**
	* Delete a key_word in databse
	*
	* @param int $id The key word id to delete
	*
	*/
	function delKeyWord($id){
		global $db;
		$statement = $db->prepare('DELETE FROM key_word_list WHERE ID = :key_word');
		$statement->execute(array(':key_word' => $id));
	}

	/**
	* Set a key word as done
	*
	* @param int $id The key word id
	*
	* @param boolean $done (optional) Default true
	*
	*/
	function setKeyWordDone($id, $done=true){
		global $db;
		if(!$done){$done=0;}
		$statement = $db->prepare('UPDATE key_word_list SET done=:done WHERE ID = :key_word');
		$statement->execute(array(':done'=>$done, ':key_word' => $id));
	}


	function setKeyWordPage($id, $page){
		global $db;
		$statement = $db->prepare('UPDATE key_word_list SET page=:page WHERE ID = :key_word');
		$statement->execute(array(':page'=>$page, ':key_word' => $id));
	}


	/**
	* Save template to send in DB
	*
	* @param string $msg the msg
	*
	*/
	function saveTemplate($msg, $account){
		global $db;
		$statement = $db->prepare('INSERT INTO msg_template (msg, accountID) VALUES (:msg, :account)');
		$statement->execute(array(':msg' =>$msg, ':account'=>$account));
	}

	/**
	* Get all templates (recent to older)
	*
	* @param bool $active (optional) Get only the active templates
	*
	* @return array All templates
	*/
	function getAllTemplates($active=null, $account){
		global $db;
		if($active != null){
			$statement = $db->prepare('SELECT ID, msg, DATE_FORMAT(created, "%Y-%m-%e") AS created, active FROM msg_template WHERE active=:active AND accountID=:account ORDER BY ID DESC');
			$statement->execute(array(':active'=>$active, ':account'=>$account));
		}else{
			$statement = $db->prepare('SELECT ID, msg, DATE_FORMAT(created, "%Y-%m-%e") AS created, active FROM msg_template WHERE accountID=:account ORDER BY ID DESC');
			$statement->execute(array(':account'=>$account));
		}
		return $statement->fetchAll(PDO::FETCH_ASSOC);
	}

	/**
	* Get one template
	*
	* @param int The ID of the template
	*
	* @return array The template
	*/
	function getTemplate($id){
		global $db;
		$statement = $db->prepare('SELECT ID, msg, DATE_FORMAT(created, "%Y-%m-%e") AS created, active FROM msg_template WHERE ID=:id');
		$statement->execute(array(':id'=>$id));
		return $statement->fetch(PDO::FETCH_ASSOC);
	}

	/**
	* Change the state of a template
	*
	* @param int $id The ID of the template
	*
	* @param bool $state The state
	*
	*/
	function setTemplateState($id, $state){
		global $db;
		$statement = $db->prepare('UPDATE msg_template SET active=:state WHERE ID=:id');
		$statement->execute(array(':id'=>intval($id), ':state'=>$state?1:0));
	}

	// ACCOUNTS

	function saveNewAccount($email, $password){
		global $db;
		$statement = $db->prepare('INSERT INTO accounts (email, password) VALUES (:email, :password)');
		$statement->execute(array(':email'=>$email, ':password'=>$password));
		$id = $db->lastInsertId();
		$statement = $db->prepare('INSERT INTO bot_action (action, accountID) VALUES (:action, :accountID)');
		$statement->execute(array(':action'=>'Not connected yet', ':accountID'=>$id));
		$statement = $db->prepare('INSERT INTO bot_disconnect (is_disconnect, accountID) VALUES (:disco, :accountID)');
		$statement->execute(array(':disco'=>0, ':accountID'=>$id));
		return $id;
	}

	function getAllAccounts(){
		global $db;
		$statement = $db->prepare('SELECT * FROM accounts');
		$statement->execute();
		return $statement->fetchAll();
	}

	function getAccount($id){
		global $db;
		$statement = $db->prepare('SELECT * FROM accounts WHERE ID=:id');
		$statement->execute(array(':id'=>$id));
		return $statement->fetch(PDO::FETCH_ASSOC);
	}

	function deleteAccount($id){
		global $db;
		$statement = $db->prepare('DELETE FROM accounts WHERE ID=:id');
		$statement->execute(array(':id'=>$id));
	}

	// GENERAL FUNCTIONS

	/**
	* Delete one item in one table
	*
	* @param string The table name
	* @param int The ID to delete
	*
	* @return bool Item deleted or not
	*/
	function delete($table, $id, $account){
		global $db;
		$statement = $db->prepare('DELETE FROM '.$table.' WHERE ID=:id AND accountID=:account');
		$statement->execute(array(':id'=>$id, ':account'=>$account));

		if($table == 'msg_template'){
			// set all template values to 0
			directQuery('UPDATE msg_conversation SET template_msg=0 WHERE template_msg='.$id);
		}
		return $statement->rowCount()==1;
	}

	function directQuery($query){
		global $db;
		$statement = $db->prepare($query);
		$statement->execute();
		return $statement->fetchAll();
	}