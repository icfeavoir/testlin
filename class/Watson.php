<?php

class Watson{

	private $_ch;

	public function __construct($username, $password, $conversation){
		$this->_ch = curl_init();
		curl_setopt($this->_ch, CURLOPT_USERPWD, $username . ":" . $password);
		curl_setopt($this->_ch, CURLOPT_URL, 'https://gateway-fra.watsonplatform.net/conversation/api/v1/workspaces/'.$conversation.'/message?version=2017-05-26');
		curl_setopt($this->_ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($this->_ch, CURLOPT_POST, 1);
	}

	public function sendMsg($msg = '', $context=null, $userName = ''){
		$headers = array();
		$headers[] = "Content-Type: application/json";
		$headers[] = "Accept: application/json";
		curl_setopt($this->_ch, CURLOPT_HTTPHEADER, $headers);

		$msg = array(
			'input'=> array(
				'text'=>$msg,
			),
		);
		if($context != null){
			$msg['context'] = $context;
		}
		$msg = json_encode($msg);

		curl_setopt($this->_ch, CURLOPT_POSTFIELDS, $msg);
		return json_decode($this->execute());
	}

	public function getResponse($json){
		$resp = gettype($json)=='string'?json_decode($json):$json;
		return $resp;
	}

	public function chat($msg, $context=null){
		return $this->getResponse($this->sendMsg($msg, $context));
	}

	public function execute(){
		return curl_exec($this->_ch);
	}

	public function close(){
		curl_close($this->_ch);
	}
}