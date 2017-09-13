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
	* @return false if msg never send, date else
	*/
	function isConnectSent($profile_id){

	}

	/**
	* Check if the bot already sent a msg to this user
	*
	* @param string $profile_id The id of the user to check
	*
	* @return false if msg never send, array(msg, date) else
	*/
	function isMsgSent($profile_id){

	}
