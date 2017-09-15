<?php

	class Linkedin{

		private $_linkedin_url = 'https://www.linkedin.com/';
		private $_ch;
		private $_username;
		private $_password;

		public function __construct($user = null, $pass = null){
			$this->_ch = curl_init();

			if($user != null && $pass != null){		// if new connection
				$this->_username = $user;
				$this->_password = $pass;

				$this->login();
			}
		}

		public static function noInst(){
			return new Linkedin();
		}

		private function login(){
			//DELETE PREVIOUS COOKIE FILE AND A NEW ONE
		    if(!(is_file('cookie.txt'))){
		        touch('cookie.txt');
		    }else{ 	// empty the file
		    	$f = @fopen("cookie.txt", "r+");
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
		public function page($page = '', $postdata = array(), $headers = array(), $data_to_string = true, $urlReplace = true){

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
		    curl_setopt($this->_ch, CURLOPT_COOKIEJAR, realpath('cookie.txt'));
		    curl_setopt($this->_ch, CURLOPT_COOKIEFILE, realpath('cookie.txt'));
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

		public function connect_to($profile_id, $check_in_db = true){
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

			$cookies = file_get_contents('cookie.txt');	//all cookies
			$cookie = $this->fetch_value($cookies, "JSESSIONID\t\"ajax:", "\"\n");	// the one we want
			$headers = array(
			    'origin: https://www.linkedin.com',
			    "cookie: JSESSIONID='ajax:$cookie';",
			    "csrf-token: ajax:$cookie",
			);

			//saving in DB
			saveConnectSent($profile_id);

			return $this->page('voyager/api/growth/normInvitations', $payload, $headers, false, false);
		}

		public function search($search, $page = 1, $tab = 'people'){
			if(!in_array($tab, array('index', 'people', 'jobs', 'companies', 'groups', 'school')))
				exit('This tab doesn\'t exist.');
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

		public function search_all_to_array($search, $tab = 'people'){
			$page = 1;
			$all_search_result = [];
			do{
				$search_result = $this->search_to_array($search, $page, $tab);
				$all_search_result = array_merge($all_search_result, $search_result);
				$page++;
			}while(!empty($search_result));

			return $all_search_result;
		}

		public function send_msg($profile_id, $msg){
			// we have to delete every slashes (/)
			while($profile_id[0] === '/')
				$profile_id = substr($profile_id, 1);
			$profile_id = rtrim($profile_id, '/');

			$payload = '{"conversationCreate":{"eventCreate":{"value":{"com.linkedin.voyager.messaging.create.MessageCreate":{"body":"'.$msg.'","attachments":[]}}},"recipients":["'.$profile_id.'"],"subtype":"MEMBER_TO_MEMBER","name":""}}';

			$headers = $this->getHeaders();

			$sending = $this->page('voyager/api/messaging/conversations?action=create', $payload, $headers, false, false);
			$infos = explode(',', $this->fetch_value($sending, 'urn:li:fs_event:(', ')'));
			$conv_id = $infos[0];
			$msg_id = $infos[1];

			// mark conversation as read
			$payload = '{"patch":{"$set":{"read":true}}}';
			array_push($headers, 'referer: https://www.linkedin.com/messaging/thread/'.$conv_id.'/');
			$this->page('voyager/api/messaging/conversations/'.$conv_id, $payload, $headers, false, false);

			//saving in DB
			saveMsgSent($profile_id, $msg, $conv_id, $msg_id);

			return $sending;
		}

		public function checkNewConnections(){
			$headers = $this->getHeaders();

			$count = 0;
			$newConnections = [];
			$notFound = 50;
			while(true){
				$connection = $this->page('voyager/api/relationships/connections?count=1&sortType=RECENTLY_ADDED&start='.$count, [], $headers);
				$id = $this->fetch_value($connection, 'miniProfile:', '"');

				if($notFound > 50){	// to save all the first time this function is called -> if 50 null following, means you got all!
					break;
				}

				if($id == null){
					$notFound++;
				}else{
					$notFound = 0;	// if not following, go back to 0.
				}

				if(isConnectedTo($id)===false){		// not connected so saving it
					saveConnectedTo($id);
					array_push($newConnections, $id);
				}elseif($id != null){	// already saved (and not a bug) = last time this function ran, it stopped at this id.
					break;
				}
				$count++;
			}
			return $newConnections;
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
			// date reference from me : 14 September 2017 - 20:14 --> 1505412842611 / Timestamp = 1505412840
			$ref_timestamp = 1505412840;
			$ref_timelinkedin = 1505412842611;

			$headers = $this->getHeaders();
			array_push($headers, 'referer: https://www.linkedin.com/messaging/thread/'.$conv.'/');
			$content = $this->page('voyager/api/messaging/conversations/'.$conv.'/events', [], $headers, false, false);
			$msgs = explode('createdAt":', $content);
			unset($msgs[0]);
			$msg_list = array(); $memory = array();
			foreach ($msgs as $msg) {
				$timelinkedin = explode(',', $msg)[0];
				if(!in_array($timelinkedin, $memory)){
					array_push($memory, $timelinkedin);
					$date = ''.date('Y-m-d H:i:s', round($timelinkedin*$ref_timestamp/$ref_timelinkedin));
					$msg_text = $this->fetch_value($msg, 'body":"', '",');
					$msg_id = explode(',', $this->fetch_value($msg, 'urn:li:fs_event:(', ')'))[1];
					$whoSendIt = isMsgSentId($conv, $msg_id);
					array_push($msg_list, array('by'=> $whoSendIt===false?'user':'bot', 'date'=>$date, 'msg'=>$msg_text, 'msg_id'=>$msg_id));
				}
			}
			return $msg_list;
		}


		public function close_curl(){
		    curl_close($this->_ch);
		}

		private function getHeaders(){	// return the array of headers needed for actions (connect, sending msg, etc);
			$cookies = file_get_contents('cookie.txt');	// all cookies
			$cookie = $this->fetch_value($cookies, "JSESSIONID\t\"ajax:", "\"\n");	// the one we want

			return array(
					'origin: https://www.linkedin.com',
					"cookie: JSESSIONID='ajax:$cookie';",
				    "csrf-token: ajax:$cookie",
				);
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