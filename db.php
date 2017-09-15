<?php

	try{
		$db = new PDO('mysql:host='.HOST.';dbname='.DB_NAME.';charset=utf8', DB_USER, DB_PASSWORD);
	}catch(Exception $e){
		exit('Error: '.$e);
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
	* @return false if msg never send, the query response else
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

	

	/**
	* Get all msg sent by the bot
	*
	* @return Array with all msg
	*/
	function getAllMsgSent(){
		global $db;
		$statement = $db->prepare('SELECT * FROM msg_sent ORDER BY ID');
		$statement->execute();
		return $statement->fetchAll();
	}

	/**
	* Check if the bot already sent a msg to this user
	*
	* @param string $profile_id The id of the user to check
	*
	* @return false if msg never send, the query response else
	*/
	function isMsgSentUser($profile_id){
		global $db;
		$statement = $db->prepare('SELECT * FROM msg_sent WHERE profile_id= :profile_id');
		$statement->execute(array(':profile_id' => $profile_id));
		if($statement->rowCount() == 0)
			return false;
		return $statement->fetch();
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
		$statement = $db->prepare('SELECT * FROM msg_sent WHERE conv_id = :conv AND msg_id = :msg_id');
		$statement->execute(array(':conv' => $conv, ':msg_id' => $msg_id));
		if($statement->rowCount() == 0)
			return false;
		return $statement->fetch();
	}

	/**
	* Save the msg sent from the bot to an user
	*
	* @param string $profile_id The id of the user to check
	* @param string $pmsg The msg
	*
	*/
	function saveMsgSent($profile_id, $msg, $conv, $msg_id){
		global $db;
		$statement = $db->prepare('INSERT INTO msg_sent (profile_id, msg, conv_id, msg_id) VALUES (:profile_id, :msg, :conv, :msg_id)');
		$statement->execute(array(':profile_id' => $profile_id, ':msg' => $msg, ':conv' => $conv, ':msg_id' => $msg_id));
	}

	

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
		return $statement;
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