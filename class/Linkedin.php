<?php

	class Linkedin{

		private $_linkedin_url = 'https://www.linkedin.com/';
		private $_ch;
		private $_username;
		private $_password;
		private $_myProfileId;
		private $_accountID;

		public function __construct($user = null, $pass = null){
			$this->_ch = curl_init();
			if($user != null && $pass != null){		// if new connection
				$this->_username = $user;
				$this->_password = $pass;
				$this->_accountID=0;

				$this->login();
				$this->_myProfileId = $this->fetch_value($this->page('in/'), 'miniProfile:', '&');
				file_put_contents(ROOTPATH.'/id_'.$this->_accountID, $this->_myProfileId);
			}else if($user != null && $pass == null){	// connection from an account in DB
				$account = getAccount($user);
				$this->_username = $account['email'];
				$this->_password = $account['password'];
				$this->_accountID = $account['ID'];

				if($this->getBotDetected()){
					$this->login();
					$this->_myProfileId = $this->fetch_value($this->page('in/'), 'miniProfile:', '&');
					file_put_contents(ROOTPATH.'/id_'.$this->_accountID, $this->_myProfileId);
				}else{
					$this->_myProfileId = file_get_contents(ROOTPATH.'/id_'.$this->_accountID);
				}			
			}else{
				return null;
			}
		}

		public static function noInst(){
			return new Linkedin();
		}

		private function login(){
			//DELETE PREVIOUS COOKIE FILE AND A NEW ONE
		    if(!(is_file(ROOTPATH.'/cookie_'.$this->_accountID.'.txt'))){
		        touch(ROOTPATH.'/cookie_'.$this->_accountID.'.txt');
		    }else{ 	// empty the file
		    	$f = @fopen(ROOTPATH.'/cookie_'.$this->_accountID.".txt", "r+");
		    	if ($f !== false) {
		    	    ftruncate($f, 0);
		    	    fclose($f);
		    	}
		    }
		    // first access to the main page to get some linkedin post data values
		    $login_content = $this->page('/uas/login', array(), array(), true, false);
		    // post data to send
		    $var = array(
		        'isJsEnabled' => 'false',
		        'source_app' => '',
		        'tryCount' => '',
		        'clickedSuggestion' => 'false',
		        'session_key' => trim($this->_username),
		        'session_password' => trim($this->_password),
		        'signin' => 'Sign In',
		        'session_redirect' => '',
		        'trk' => '',
		        'fromEmail' => '',
		    );
		    // some data from the main page
		    $var['loginCsrfParam'] = $this->fetch_value($login_content, 'type="hidden" name="loginCsrfParam" value="', '"');
		    $var['csrfToken'] = $this->fetch_value($login_content, 'type="hidden" name="csrfToken" value="', '"');
		    $var['sourceAlias'] = $this->fetch_value($login_content, 'input type="hidden" name="sourceAlias" value="', '"');
		    $post_array = array();
		    foreach ($var as $key => $value){
		        $post_array[] = urlencode($key) . '=' . urlencode($value);
		    }
		    // Now we can log in
		    $login = $this->page('/uas/login-submit', $post_array, [], true, false);
		    return $login;
		}

		public function getMyProfileId(){
			return $this->_myProfileId;
		}


		/**
		* Go to this LinkedIn page
		*
		* @param string $page Page to access
		* @param array $postdata Associative array will post data to send [key => value] or string with $data_to_string to false
		* @param array $postdata Array will headers to send in headers syntax ['key: value']
		* @param bool $data_to_string True for postdata convert in string, False for let postdata untouched
		* @param string $urlReplace True if the $page need it (put URL same value), useful for nodeJS concept, false if it doesn't matter
		*
		* @return string The content of the page
		*/
		public function page($page = 'nhome', $postdata = array(), $headers = array(), $data_to_string = true, $urlReplace = false){

			while($page[0] === '/')
				$page = substr($page, 1);

		    // first main page to get some linkedin post data values
		    curl_setopt($this->_ch, CURLOPT_URL, $this->_linkedin_url.$page);
		    curl_setopt($this->_ch, CURLOPT_USERAGENT,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.113 Safari/537.36');
		    curl_setopt($this->_ch, CURLOPT_REFERER, $this->_linkedin_url);
		    curl_setopt($this->_ch, CURLOPT_RETURNTRANSFER, true);
		    curl_setopt($this->_ch, CURLOPT_FOLLOWLOCATION, true);
		    curl_setopt($this->_ch, CURLOPT_SSL_VERIFYPEER, false);
		    curl_setopt($this->_ch, CURLOPT_SSL_VERIFYHOST, false);
		    curl_setopt($this->_ch, CURLOPT_COOKIEJAR, realpath(ROOTPATH.'/cookie_'.$this->_accountID.'.txt'));
		    curl_setopt($this->_ch, CURLOPT_COOKIEFILE, realpath(ROOTPATH.'/cookie_'.$this->_accountID.'.txt'));
		    curl_setopt($this->_ch, CURLOPT_POST, false);
		    curl_setopt($this->_ch, CURLOPT_HEADER, false);
		    if(count($postdata) > 0){
				curl_setopt($this->_ch, CURLOPT_POST, true);
		    	if($data_to_string){
				    $post_string = implode('&', $postdata);
				    curl_setopt($this->_ch, CURLOPT_POSTFIELDS, $post_string);
				}else{	// not changing the postdata var
				    curl_setopt($this->_ch, CURLOPT_POSTFIELDS, $postdata);
				}
			}
			if(count($headers) > 0){
				curl_setopt($this->_ch, CURLOPT_HEADER, true);
				curl_setopt($this->_ch, CURLOPT_HTTPHEADER, $headers);
			}
		    $content = curl_exec($this->_ch);
		    if($urlReplace){
		    	// javascript to access to the page (because of nodeJS)
		    	echo '<script>
		    				history.pushState(null, null, "http://botlinkedin/");
		    				history.replaceState(null, "Linkedin", "'.$page.'");
		    		</script>';
		    }
		    return $content;
		}

		public function showPage($page = 'nhome', $postdata = array(), $headers = array(), $data_to_string = true){
			$page = $this->page($page, $postdata, $headers, $data_to_string, true);
			echo $page;
			return $page;
		}

		public function connectTo($profile_id, $check_in_db = true){
			if($profile_id === null){
				return null;
			}
			if($check_in_db && isConnectSent($profile_id)!==false){	// already sent
				return null;
			}

			// we have to delete every slashes (/)
			while($profile_id[0] === '/')
				$profile_id = substr($profile_id, 1);
			$profile_id = rtrim($profile_id, '/');
			// first access to the profile to find the tracking ID
			$profile = $this->page('in/'.$profile_id);
			$trackingId = $this->fetch_value($profile, "&quot;trackingId&quot;:&quot;", '&#61;&#61;&quot;').'==';
			// sending invitation to profileId account!
			$payload = '{"trackingId":"'.$trackingId.'","invitations":[],"excludeInvitations":[],"invitee":{"com.linkedin.voyager.growth.invitation.InviteeProfile":{"profileId":"'.$profile_id.'"}}}';

			$headers = $this->getHeaders();

			//saving in DB
			if($check_in_db)
				saveConnectSent($profile_id, $this->_accountID);

			return $this->page('voyager/api/growth/normInvitations', $payload, $headers, false, false);
		}

		// to get invitations from other users
		public function acceptLastConnectionRequest(){
			$headers = $this->getHeaders();
			$content = $this->page('voyager/api/relationships/invitationViews?count=1&includeInsights=true&q=receivedInvitation&start=0', [], $headers);
			$connect = explode('fromMemberId', $content);
			if(count($connect) > 1)
				$connect = $connect[1];
			else
				return null;
			$sharedSecret = $this->fetch_value($connect, 'sharedSecret":"', '"');
			$invitationId = $this->fetch_value($connect, 'urn:li:invitation:', '"');
			$profile_id = $this->fetch_value($connect, '":"', '"');

			$payload = array(
				"invitationSharedSecret" => "$sharedSecret",
				"invitationId" => "$invitationId",
			);
			// accept connect
			$this->page('voyager/api/relationships/invitations/'.$invitationId.'?action=accept', json_encode($payload), $headers, false, false);
			saveConnectedTo($profile_id, $this->_accountID);
			return $profile_id;
		}

		public function search($search, $page = 1, $tab = 'people'){
			if(!in_array($tab, array('index', 'people', 'jobs', 'companies', 'groups', 'school')))
				return('This tab doesn\'t exist.');
			if($tab === 'jobs')
				$url = 'jobs/search';
			else
				$url = 'search/results/'.$tab;
			return $this->page($url.'/?keywords='.urlencode($search).'&page='.$page);
		}

		public function search_to_array($search, $page = 1, $tab = 'people'){	// get an associative array name -> id
			$html = html_entity_decode($this->search($search, $page, $tab));
			// we search all ids named SharedConnectionsInfo
			$ids_array = explode('sharedConnectionsInfo:', $html);
			$ids_list = [];
			foreach ($ids_array as $key => $id) {
				if($key == 0)	// the first contains <!DOCTYPE> to the real first
					continue;

				// if not already find
				$id = explode(',',$id)[0];
				$id = rtrim($id, '/');
				$id = rtrim($id, "\\\"");		// specials
				if(!in_array($id, $ids_list)){
					array_push($ids_list, $id);
				}
			}
			// now we can find url for those ids
			$search_result = [];
			foreach ($ids_list as $key => $id) {
				$member_id = substr($html, strpos($html, 'member:'.$id));		// cut to have exactly what we want
				$member_id = substr($member_id, 0, strpos($member_id, '}'));
				array_push($search_result, $this->fetch_value($member_id, 'miniProfile:', '",'));
			}

			return $search_result;
		}

		/**
		* Send a message to an user
		*
		* @param string $profile_id The user profile
		*
		* @param string $msg The msg to send or the template (as an array)
		*
		* @param bool $watson (optional) True if waton msg
		*
		* @param string $context (optional) The watson context (STRING AND NOT OBJECT !)
		*/
		public function sendMsg($profile_id, $msg, $watson=0, $context=null){
			if(count($profile_id) == 0)
				return false;
			
			if(is_array($msg)){	// if send template, to user with the getTemplate($id) function
				$template = $msg['ID'];
				$msg = str_replace('<br />', '\n', $msg['msg']);
			}else{
				$template = 0;
			}

			// we have to delete every slashes (/)
			$profile_id = trim($profile_id, '/');
			$profile_id = rtrim($profile_id, '/');

			$payload = '{"conversationCreate":{"eventCreate":{"value":{"com.linkedin.voyager.messaging.create.MessageCreate":{"body":"'.$msg.'","attachments":[]}}},"recipients":["'.$profile_id.'"],"subtype":"MEMBER_TO_MEMBER","name":""}}';

			$headers = $this->getHeaders();

			$sending = $this->page('voyager/api/messaging/conversations?action=create', $payload, $headers, false, false);
			$infos = explode(',', $this->fetch_value($sending, 'urn:li:fs_event:(', ')'));
			if(count($infos) < 2){	// conv not found
				return null;
			}
			$conv_id = $infos[0];
			$msg_id = $infos[1];

			$this->markConversationAsRead($conv_id);

			//saving in DB
			saveMsgSent($profile_id, $msg, $conv_id, $msg_id, time(), $template, $watson, $context, $this->_accountID);

			return $sending;
		}


		/**
		* @param string $msg A message formated from getAllMsg()
		*/
		public function receiveMsg($msg){
			// check if not saved yet and not msg sent
			if(getMsgReceived(null, null, $msg['msg_id']) == null && $msg['by']!='bot'){
				// previous msg from the bot
				$prev = getLastMsg($msg['conv_id']);
				$template = $prev['template_msg']??0;
				$watson = $prev['watson']??0;
				saveMsgReceived($msg['profile_id'], $msg['msg'], $msg['conv_id'], $msg['msg_id'], $msg['date'], $template, $watson, $this->_accountID);
			}
		}


		/**
		* Save a msg, whatever if it's the bot or not
		*
		* @param string $msg A message formated from getAllMsg()
		*/
		public function saveMsg($msg){
			if($msg['msg'] == '')
				return;
			if($msg['by'] == 'bot'){
				// already saved
				if(getMsgSent($this->_accountID, null, null, $msg['msg_id']) != null)
					return;
				// not template or Watson if we just discover this msg sent by the bot (old msg)
				saveMsgSent($msg['profile_id'], $msg['msg'], $msg['conv_id'], $msg['msg_id'], $msg['date'], 0, 0, null, $this->_accountID);
			}else{
				$this->receiveMsg($msg);
			}

		}

		public function markConversationAsRead($conv_id){
			$payload = '{"patch":{"$set":{"read":true}}}';
			$headers = $this->getHeaders();
			array_push($headers, 'referer: https://www.linkedin.com/messaging/thread/'.$conv_id.'/');
			$this->page('voyager/api/messaging/conversations/'.$conv_id, $payload, $headers, false, false);
			setRead($conv_id);
		}

		public function checkNewConnections(){
			$headers = $this->getHeaders();
			$run = true;

			$count = 0;
			$newConnections = [];
			$notFound = 25;
			while($run){
				$connection = $this->page('voyager/api/relationships/connections?count=1&sortType=RECENTLY_ADDED&start='.$count, [], $headers);
				sleep(1);
				$id = $this->fetch_value($connection, 'miniProfile:', '"');

				if($notFound > 50){	// to save all the first time this function is called -> if 50 null following, means you got all!
					$run = false;
				}

				if($id == null){
					$notFound++;
				}else{
					$notFound = 0;	// if we find one, go back to 0 for notFound.
				}

				if(isConnectedTo($id, $this->_accountID)===false){		// connected with but not saved yet in DB --> new connections
					saveConnectedTo($id, $this->_accountID);
					array_push($newConnections, $id);
				}else if(isConnectedTo($id, $this->_accountID)!==false && $id != null){	// already saved (and not a bug) = last time this function ran, it stopped at this id
					$run = false;
				}
				$count++;
			}
			return $newConnections;
		}


		/**
		* @param @createdBefore (optional) a date in LinkedIn format
		*/
		function getAllConversations($createdBefore=null){	// to use carrefully, could be long...
			$headers = $this->getHeaders();
			$createdBefore = $createdBefore??$this->timestampToLinkedinDate(time());

			$content = $this->page('voyager/api/messaging/conversations?createdBefore='.$createdBefore, [], $headers, false, false);
			$conversations = explode('urn:li:fs_event:(', $content);
			unset($conversations[0]);
			$conversation_list = array();
			foreach ($conversations as $conv) {
				if(!in_array(intval($conv), $conversation_list)){
					array_push($conversation_list, intval(explode(',', $conv)[0]));
				}
			}
			return $conversation_list;
		}

		function getUnreadConversations(){
			$headers = $this->getHeaders();
			array_push($headers, 'x-restli-protocol-version: 2.0.0');
			array_push($headers, 'accept: application/vnd.linkedin.normalized+json');
			$content = $this->page('voyager/api/messaging/conversations?filters=List(UNREAD)&q=search', [], $headers, false, false);
			$conversations = explode('urn:li:fs_event:(', $content);
			unset($conversations[0]);
			$conversation_list = array();
			foreach ($conversations as $conv) {
				if(!in_array(intval($conv), $conversation_list))
					array_push($conversation_list, intval(explode(',', $conv)[0]));
			}
			return $conversation_list;
		}

		/**
		* Return all msg from a conversation
		*
		* @param string $conv The conversation ID
		*
		* @return An array of array with key 'bot' or 'user_id' : [0=>[by=>bot, date=>$d, msg=>$m, $msg_id=>$id], 1=>[by=>user, date=>$d2, msg=>$m2], 2=>[]]
		*/ 
		function getAllMsg($conv){
			$headers = $this->getHeaders();
			array_push($headers, 'referer: https://www.linkedin.com/messaging/thread/'.$conv.'/');
			$content = $this->page('voyager/api/messaging/conversations/'.$conv.'/events', [], $headers, false);
			
			$msgs = explode('createdAt":', $content);
			unset($msgs[0]);
    		
    		$replaces   = array("\r\n", "\n", "\r");

			$msg_list = array(); $memory = array();
			$profile_id = $this->getIdByConversation($conv);
			$user = '';
			foreach ($msgs as $msg) {
				$timelinkedin = explode(',', $msg)[0];
				if(!in_array($timelinkedin, $memory)){
					array_push($memory, $timelinkedin);
					$date = ''.date('Y-m-d H:i:s', $this->linkedinDateToTimestamp($timelinkedin));
					$msg_text = preg_replace('#(\\\r|\\\r\\\n|\\\n)#', '<br/>', $this->fetch_value($msg, 'body":"', '",'));
					$msg_id = explode(',', $this->fetch_value($msg, 'urn:li:fs_event:(', ')'))[1];
					$authorId = $this->fetch_value($msg, 'urn:li:fs_miniProfile:', '"');
					if($authorId != $this->_myProfileId && $user == ''){	// just once
						$user = $this->getUserInformations($authorId);
						$userName = $user['firstName'].' '.$user['lastName'];
					}
					$authorId = $authorId==$this->_myProfileId?'bot':$userName;
					array_push($msg_list, array('profile_id'=>$profile_id, 'by'=> $authorId, 'date'=>$date, 'msg'=>nl2br($msg_text), 'msg_id'=>$msg_id, 'conv_id'=>$conv, 'linkedinDate'=>$timelinkedin));
				}
			}
			return $msg_list;
		}

		public function getUserInformations($profile_id){
			$profile = $this->page('in/'.$profile_id.'/');
			$content = explode('li:fs_profile:'.$profile_id, $profile);
			unset($content[0]);
			$firstName = ''; $lastName = ''; $job = '';
			foreach ($content as $key=>$val) {
				if($firstName == ''){
					$firstName = $this->fetch_value($val, 'firstName&quot;:&quot;', '&quot;');
				}
				if($lastName == ''){
					$lastName = $this->fetch_value($val, 'lastName&quot;:&quot;', '&quot;');
				}
				if($job == ''){
					$job = $this->fetch_value($val, 'headline&quot;:&quot;', '&quot;');
				}
			}
			return array('firstName'=>$firstName, 'lastName'=>$lastName, 'job'=>$job);
		}

		public function getIdByConversation($conv_id){
			// return the user ID of this conv
			$conv = $this->page('messaging/thread/'.$conv_id.'/');
			$conv = explode('urn:li:fs_miniProfile', $conv);
			// the good id is the last one because linkedin load conversation from today to the one you want (or something like that)
			do{
				$id = $this->fetch_value(end($conv), ':', '&quot;');
				unset($conv[count($conv)-1]);
			}while($id == $this->_myProfileId || $id == '');
			return $id;
		}

		public function getBotDetected(){
			if(!is_file(ROOTPATH.'/cookie_'.$this->_accountID.'.txt') || !is_file(ROOTPATH.'/id_'.$this->_accountID))
				return true;
			$cookie = file_get_contents(ROOTPATH.'/cookie_'.$this->_accountID.'.txt');
			if(strpos($cookie, 'delete me') == false && file_get_contents(ROOTPATH.'/id_'.$this->_accountID) != ""){ 
			    return false;	// not detected
			}else{
			    return true;
			}

		}

		public function close(){
		    curl_close($this->_ch);
		}

		private function getHeaders(){	// return the array of headers needed for actions (connect, sending msg, etc);
			$cookies = file_get_contents(ROOTPATH.'/cookie_'.$this->_accountID.'.txt');	// all cookies
			$cookie = $this->fetch_value($cookies, "JSESSIONID\t\"ajax:", "\"\n");	// the one we want

			return array(
					'origin: https://www.linkedin.com',
					"cookie: JSESSIONID='ajax:$cookie';",
				    "csrf-token: ajax:$cookie",
				);
		}

		public function linkedinDateToTimestamp($linkDate){
			// date reference from me : 14 September 2017 - 20:14 --> 1505412842611 / Timestamp = 1505412840
			$ref_timestamp = 1505412840;
			$ref_timelinkedin = 1505412842611;
			return round($linkDate*$ref_timestamp/$ref_timelinkedin);
		}
		public function timestampToLinkedinDate($timestamp){
			// date reference from me : 14 September 2017 - 20:14 --> 1505412842611 / Timestamp = 1505412840
			$ref_timestamp = 1505412840;
			$ref_timelinkedin = 1505412842611;
			return round($timestamp*$ref_timelinkedin/$ref_timestamp);
		}


		// public function to get login values (just in case some linkedin variables change)
		public function fetch_value($str, $find_start = '', $find_end = ''){
		    if ($find_start == ''){
		        return '';
		    }
		    $start = strpos($str, $find_start);
		    if ($start === false){
		        return '';
		    }
		    $length = strlen($find_start);
		    $substr = substr($str, $start + $length);
		    if ($find_end == ''){
		        return $substr;
		    }
		    $end = strpos($substr, $find_end);
		    if ($end === false){
		        return $substr;
		    }
		    return substr($substr, 0, $end);
		}
	}