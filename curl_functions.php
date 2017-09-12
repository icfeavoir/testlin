<?php

	$linkedin_url = 'https://www.linkedin.com/';

	function login($ch, $username, $password, $headers = array()){
		global $linkedin_url;
	    // CREATE COOKIE FILE
	    if(!(is_file('cookie.txt'))){
	        touch('cookie.txt');
	    	chmod('cookie.txt', 0777);
	    }

	    // first access to the main page to get some linkedin post data values
	    $login_content = access_page($ch, '/uas/login', array(), array(), true, false);
	    if(curl_error($ch)) {
	    	return('error:' . curl_error($ch));
	    }
	    // post data to send
	    $var = array(
	        'isJsEnabled' => 'false',
	        'source_app' => '',
	        'tryCount' => '',
	        'clickedSuggestion' => 'false',
	        'session_key' => trim($username),
	        'session_password' => trim($password),
	        'signin' => 'Sign In',
	        'session_redirect' => '',
	        'trk' => '',
	        'fromEmail' => '',
	    );
	    // some data from the main page
	    $var['loginCsrfParam'] = fetch_value($login_content, 'type="hidden" name="loginCsrfParam" value="', '"');
	    $var['csrfToken'] = fetch_value($login_content, 'type="hidden" name="csrfToken" value="', '"');
	    $var['sourceAlias'] = fetch_value($login_content, 'input type="hidden" name="sourceAlias" value="', '"');
	    $post_array = array();
	    foreach ($var as $key => $value){
	        $post_array[] = urlencode($key) . '=' . urlencode($value);
	    }

	    // Now we can log in
	    $login = access_page($ch, '/uas/login-submit', $post_array, $headers, true, false);
	    // javascript to access to the page (because of nodeJS)
	    return $login;
	}


	// function to get login values (just in case some linkedin variables change)
	function fetch_value($str, $find_start = '', $find_end = '')
	{
	    if ($find_start == '')
	    {
	        return '';
	    }
	    $start = strpos($str, $find_start);
	    if ($start === false)
	    {
	        return '';
	    }
	    $length = strlen($find_start);
	    $substr = substr($str, $start + $length);
	    if ($find_end == '')
	    {
	        return $substr;
	    }
	    $end = strpos($substr, $find_end);
	    if ($end === false)
	    {
	        return $substr;
	    }
	    return substr($substr, 0, $end);
	}


	function access_page($ch, $page = '', $postdata = array(), $headers = array(), $data_to_string = true, $urlReplace = true){
		global $linkedin_url;

		while($page[0] === '/')
			$page = substr($page, 1);

	    // first main page to get some linkedin post data values
	    curl_setopt($ch, CURLOPT_URL, $linkedin_url.$page);
	    curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.113 Safari/537.36');
	    curl_setopt($ch, CURLOPT_REFERER, $linkedin_url);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	    curl_setopt($ch, CURLOPT_COOKIEJAR, realpath('cookie.txt'));
	    curl_setopt($ch, CURLOPT_COOKIEFILE, realpath('cookie.txt'));
	    curl_setopt($ch, CURLOPT_POST, false);
	    curl_setopt($ch, CURLOPT_HEADER, false);
	    if(count($postdata) > 0){
			curl_setopt($ch, CURLOPT_POST, true);
	    	if($data_to_string){
			    $post_string = implode('&', $postdata);
			    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
			}else{	// not changing the postdata var
			    curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
			}
		}
		if(count($headers) > 0){
			curl_setopt($ch, CURLOPT_HEADER, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		}

	    $content = curl_exec($ch);
	    if($urlReplace){
	    	echo '<script>
	    				history.pushState(null, null, "http://botlinkedin/");
	    				history.replaceState(null, "Linkedin", "'.$page.'");
	    		</script>';
	    }
	    
	    chmod('cookie.txt', 0777);
	    return $content;
	}

	function connect_to($ch, $profile_id){
		// we have to delete every slashes (/)
		while($profile_id[0] === '/')
			$profile_id = substr($profile_id, 1);
		$profile_id = rtrim($profile_id, '/');
		// first access to the profile to find the tracking ID
		$profile = access_page($ch, 'in/'.$profile_id);
		$trackingId = fetch_value($profile, "&quot;trackingId&quot;:&quot;", '&#61;&#61;&quot;').'==';
		// sending invitation to profileId account!
		$payload = '{"trackingId":"'.$trackingId.'","invitations":[],"excludeInvitations":[],"invitee":{"com.linkedin.voyager.growth.invitation.InviteeProfile":{"profileId":"'.$profile_id.'"}}}';

		$cookies = file_get_contents('cookie.txt');
		$cookie = fetch_value($cookies, "JSESSIONID\t\"ajax:", "\"\n");
		$headers = array(
		    'origin: https://www.linkedin.com',
		    'accept-encoding: deflate, br',
		    'x-li-lang: fr_FR',
		    'accept-language: fr-FR,fr;q=0.8,en-US;q=0.6,en;q=0.4',
		    'x-requested-with: XMLHttpRequest',
		    "cookie: JSESSIONID='ajax:$cookie';",
		    'pragma: no-cache',
		    'x-restli-protocol-version: 2.0.0',
		    'cache-control: no-cache',
		    'user-agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.113 Safari/537.36',
		    'x-li-page-instance: urn:li:page:d_flagship3_profile_view_base;58JbK1SnR7u93R20S/pycA==',
		    'content-type: application/json; charset=UTF-8',
		    'accept: text/plain, */*; q=0.01',
		    "csrf-token: ajax:$cookie",
		    'x-li-track: {"clientVersion":"1.0.*","osName":"web","timezoneOffset":2,"deviceFormFactor":"DESKTOP","mpName":"voyager-web"}',
		    'authority: www.linkedin.com',
		    'referer: https://www.linkedin.com/in/jan-nordwall-061b6b48/' ,
		);

		return access_page($ch, 'voyager/api/growth/normInvitations', $payload, $headers, false, false);
	}

	function search($ch, $search, $page = 1, $tab = 'people'){
		if(!in_array($tab, array('index', 'people', 'jobs', 'companies', 'groups', 'school')))
			exit('This tab doesn\'t exist.');
		if($tab === 'jobs')
			$url = 'jobs/search';
		else
			$url = 'search/results/'.$tab;
		return access_page($ch, $url.'/?keywords='.urlencode($search).'&page='.$page);
	}

	function search_to_array($ch, $search, $page = 1, $tab = 'people'){	// get an associative array name -> id
		$html = html_entity_decode(search($ch, $search, $page, $tab));
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
			array_push($search_result, fetch_value($member_id, 'miniProfile:', '",'));
		}

		return $search_result;
	}

	function search_all_to_array($ch, $search, $tab = 'people'){
		$page = 1;
		$all_search_result = [];
		do{
			$search_result = search_to_array($ch, $search, $page, $tab);
			$all_search_result = array_merge($all_search_result, $search_result);
			$page++;
		}while(!empty($search_result));

		return $all_search_result;
	}

	function send_msg($ch, $profile_id, $msg){
		// we have to delete every slashes (/)
		while($profile_id[0] === '/')
			$profile_id = substr($profile_id, 1);
		$profile_id = rtrim($profile_id, '/');

		$payload = '{"conversationCreate":{"eventCreate":{"value":{"com.linkedin.voyager.messaging.create.MessageCreate":{"body":"'.$msg.'","attachments":[]}}},"recipients":["'.$profile_id.'"],"subtype":"MEMBER_TO_MEMBER","name":""}}';

		$cookies = file_get_contents('cookie.txt');
		$cookie = fetch_value($cookies, "JSESSIONID\t\"ajax:", "\"\n");

		$headers = array(
				'origin: https://www.linkedin.com',
				"cookie: JSESSIONID='ajax:$cookie';",
			    "csrf-token: ajax:$cookie",
			);

		return access_page($ch, 'voyager/api/messaging/conversations?action=create', $payload, $headers, false, false);
	}

	function close_curl($ch){
	    curl_close($ch);
	    unlink('cookie.txt');
	}