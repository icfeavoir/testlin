<?php

	try{
		$db = new PDO('mysql:host='.HOST.';dbname='.DB_NAME.';charset=utf8', DB_USER, DB_PASSWORD);
	}catch(Exception $e){
		exit('Error: '.$e);
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
		$statement = $db->prepare('SELECT profile_id, request_date FROM connect_asked WHERE profile_id= :profile_id LIMIT 1');
		$statement->execute(array(':profile_id' => $profile_id));
		if($statement->rowCount() == 0)
			return false;
		return $statement->fetch();
	}

	/**
	* Check if the bot already sent a msg to this user
	*
	* @param string $profile_id The id of the user to check
	*
	* @return false if msg never send, the query response else
	*/
	function isMsgSent($profile_id){
		global $db;
		$statement = $db->prepare('SELECT profile_id, msg, request_date FROM msg_sent WHERE profile_id= :profile_id');
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
	* Save the msg sent to an user
	*
	* @param string $profile_id The id of the user to check
	*
	*/
	function saveMsgSent($profile_id, $msg){
		global $db;
		$statement = $db->prepare('INSERT INTO msg_sent (profile_id, msg) VALUES (:profile_id, :msg)');
		$statement->execute(array(':profile_id' => $profile_id, ':msg' => $msg));
	}
