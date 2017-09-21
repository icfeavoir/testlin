<?php

	try{
		$db = new PDO('mysql:host='.HOST.';dbname='.DB_NAME.';charset=utf8', DB_USER, DB_PASSWORD);
	}catch(Exception $e){
		exit('Error: '.$e);
	}


	/**
	* Get the current state of the bot
	*
	* @return Bool is the bot on
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
	function getAllConnectionsSent(){
		global $db;
		$statement = $db->prepare('SELECT * FROM connect_asked ORDER BY ID');
		$statement->execute();
		return $statement->fetchAll();
	}

	/**
	* Check if the bot already sent a connect request to this user
	*
	* @param string $profile_id The id of the user to check
	*
	* @return false if connect never send, the query response else
	*/
	function isConnectSent($profile_id){
		global $db;
		$statement = $db->prepare('SELECT * FROM connect_asked WHERE profile_id= :profile_id LIMIT 1');
		$statement->execute(array(':profile_id' => $profile_id));
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
	function saveConnectSent($profile_id){
		global $db;
		$statement = $db->prepare('INSERT INTO connect_asked (profile_id) VALUES (:profile_id)');
		$statement->execute(array(':profile_id' => $profile_id));
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
	* @param ] string $user Only messages to this user
	*
	* @param ] string $conv_id Only messages from this conversration
	*
	* @param ] string $msg_id Only this msg
	*
	* @param ] int $template Only messages from this template
	*
	* @return Array with all msg
	*/
	function getMsgSent($profile_id='%%', $conv_id='%%', $msg_id='%%', $template='%%', $watson_msg='%%'){
		global $db;
		$profile_id = empty($profile_id)?'%%':$profile_id;
		$conv_id = empty($conv_id)?'%%':$conv_id;
		$msg_id = empty($msg_id)?'%%':$msg_id;
		$template = empty($template)?'%%':$template;
		$watson_msg = empty($watson_msg)?'%%':$watson_msg;
		$statement = $db->prepare('SELECT * FROM msg_conversation WHERE by_bot=1 AND profile_id LIKE :profile_id AND conv_id LIKE :conv_id AND msg_id LIKE :msg_id AND template_msg LIKE :template  AND watson_msg LIKE :watson_msg ORDER BY ID');
		$statement->execute(array(':profile_id'=>$profile_id, ':conv_id'=>$conv_id, ':msg_id'=>$msg_id, ':template'=>$template, ':watson_msg'=>$watson_msg));
		return $statement->rowCount()==0?null:$statement->fetchAll(PDO::FETCH_ASSOC);
	}


	/**
	* Check if the bot already sent a msg with a specific ID in a specific conversation
	*
	* @param string $profile_id The id of the user to check
	*
	* @return false if msg never send, the query response else
	*/
	function isMsgSentId($conv, $msg_id){
		global $db;
		$statement = $db->prepare('SELECT * FROM msg_conversation WHERE by_bot=1 AND conv_id = :conv AND msg_id = :msg_id');
		$statement->execute(array(':conv' => $conv, ':msg_id' => $msg_id));
		if($statement->rowCount() == 0)
			return false;
		return $statement->fetch();
	}

	/**
	* Save the msg sent from the bot to an user
	*
	*/
	function saveMsgSent($profile_id, $msg, $conv, $msg_id, $date, $template=0, $watson_msg=0){
		global $db;
		$date = gettype($date)=='string'?$date:date('Y-m-d G:i:s', $date);
		$statement = $db->prepare('INSERT INTO msg_conversation (by_bot, profile_id, conv_id, msg_id, template_msg, msg, watson_msg, date) VALUES (1, :profile_id, :conv, :msg_id, :template, :msg, :watson_msg, :date)');
		$statement->execute(array(':profile_id' => $profile_id, ':conv' => $conv, ':msg_id' => $msg_id, ':template'=>$template, ':msg' => $msg, ':watson_msg'=>$watson_msg, ':date'=>$date));
	}


	/**
	* Get all msg received by the bot
	*
	* @param ] string $user Only messages from this user
	*
	* @param ] string $conv_id Only messages from this conversration
	*
	* @param ] string $msg_id Only this msg
	*
	* @param ] int $template Only messages from this template
	*
	* @return Array with all msg
	*/
	function getMsgReceived($profile_id='%%', $conv_id='%%', $msg_id='%%', $template='%%', $watson_msg='%%', $watson_try='%%', $is_read='%%'){
		global $db;
		$profile_id = empty($profile_id)?'%%':$profile_id;
		$conv_id = empty($conv_id)?'%%':$conv_id;
		$msg_id = empty($msg_id)?'%%':$msg_id;
		$template = empty($template)?'%%':$template;
		$watson_msg = empty($watson_msg)?'%%':$watson_msg;
		$watson_try = empty($watson_try)?'%%':$watson_try;
		$is_read = empty($is_read)?'%%':$is_read;
		$statement = $db->prepare('SELECT * FROM msg_conversation WHERE by_bot=0 AND profile_id LIKE :profile_id AND conv_id LIKE :conv_id AND msg_id LIKE :msg_id AND template_msg LIKE :template AND watson_msg LIKE :watson_msg AND watson_try LIKE :watson_try AND is_read LIKE :is_read ORDER BY ID');
		$statement->execute(array(':profile_id'=>$profile_id, ':conv_id'=>$conv_id, ':msg_id'=>$msg_id, ':template'=>$template, ':watson_msg'=>$watson_msg, ':watson_try'=>$watson_try, ':is_read'=>$is_read));
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
	function saveMsgReceived($profile_id, $msg, $conv, $msg_id, $date, $template=0, $watson_msg=false){
		global $db;
		$date = gettype($date)=='string'?$date:date('Y-m-d G:i:s', $date);
		$statement = $db->prepare('INSERT INTO msg_conversation (by_bot, profile_id, conv_id, msg_id, template_msg, msg, watson_msg, date) VALUES (0, :profile_id, :conv, :msg_id, :template, :msg, :watson_msg, :date)');
		$statement->execute(array(':profile_id' => $profile_id, ':conv' => $conv, ':msg_id' => $msg_id, ':template'=>$template, ':msg' => $msg, ':watson_msg'=>$watson_msg, ':date'=>$date));
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

	/* --------------------------------------- */

	/**
	* Get all connections
	*
	* @return Array of all connections
	*/
	function getAllConnections(){
		global $db;
		$statement = $db->prepare('SELECT * FROM connect_list ORDER BY ID');
		$statement->execute();
		return $statement->fetchAll();
	}

	/**
	* Check if the user accepted the connect request
	*
	* @param string $profile_id The id of the user to check
	*
	* @return false if msg never send, the query response else
	*/
	function isConnectedTo($profile_id){
		global $db;
		$statement = $db->prepare('SELECT * FROM connect_list WHERE profile_id= :profile_id');
		$statement->execute(array(':profile_id' => $profile_id));
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
	function saveConnectedTo($profile_id){
		global $db;
		$statement = $db->prepare('INSERT INTO connect_list (profile_id) VALUES (:profile_id)');
		$statement->execute(array(':profile_id' => $profile_id));
	}



	/**
	* Get all key words saved
	*
	* @return array of key_words
	*/
	function getKeyWords(){
		global $db;
		$statement = $db->prepare('SELECT * FROM key_word_list');
		$statement->execute();
		return $statement->fetchAll(PDO::FETCH_ASSOC);
	}

	/**
	* Save a new key_word in databse
	*
	* @param string $key_word The key word to save
	*
	*/
	function saveKeyWord($key_word){
		global $db;
		$statement = $db->prepare('INSERT INTO key_word_list (key_word) VALUES (:key_word)');
		$statement->execute(array(':key_word' => $key_word));
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
	* Save template to send in DB
	*
	* @param string $msg the msg
	*
	*/
	function saveTemplate($msg){
		global $db;
		$statement = $db->prepare('INSERT INTO msg_template SET msg = :msg');
		$statement->execute(array(':msg' => $msg));
	}

	/**
	* Get all templates (recent to older)
	*
	* @param bool $active (optional) Get only the active templates
	*
	* @return array All templates
	*/
	function getAllTemplates($active=null){
		global $db;
		if($active != null){
			$statement = $db->prepare('SELECT ID, msg, DATE_FORMAT(created, "%Y-%m-%e") AS created, active FROM msg_template WHERE active=:active ORDER BY ID DESC');
			$statement->execute(array(':active'=>$active));
		}else{
			$statement = $db->prepare('SELECT ID, msg, DATE_FORMAT(created, "%Y-%m-%e") AS created, active FROM msg_template ORDER BY ID DESC');
			$statement->execute();
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

	// GENERAL FUNCTIONS

	/**
	* Delete one item in one table
	*
	* @param string The table name
	* @param int The ID to delete
	*
	* @return bool Item deleted or not
	*/
	function delete($table, $id){
		global $db;
		$statement = $db->prepare('DELETE FROM '.$table.' WHERE ID=:id');
		$statement->execute(array(':id'=>$id));
		return $statement->rowCount()==1;
	}

	function directQuery($query){
		global $db;
		$statement = $db->prepare($query);
		$statement->execute();
		return $statement->fetchAll();
	}