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
	    $login_content = access_page($ch, '/uas/login');
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
	    $login = access_page($ch, '/uas/login-submit', $post_array, $headers);
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


	function access_page($ch, $page, $postdata = array(), $headers = array()){
		global $linkedin_url;

		while($page[0] === '/')
			$page = substr($page, 1);
	    // first main page to get some linkedin post data values
	    curl_setopt($ch, CURLOPT_URL, $linkedin_url.$page);
	    curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.0; en-US; rv:1.7.12) Gecko/20050915 Firefox/1.0.7)');
	    // curl_setopt($ch, CURLOPT_AUTOREFERER, true);
	    curl_setopt($ch, CURLOPT_REFERER, $linkedin_url);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
	    curl_setopt($ch, CURLOPT_COOKIEJAR, realpath('cookie.txt'));
	    curl_setopt($ch, CURLOPT_COOKIEFILE, realpath('cookie.txt'));
	    curl_setopt($ch, CURLOPT_POST, false);
	    if(count($postdata) > 0){
		    curl_setopt($ch, CURLOPT_POST, true);
		    $post_string = implode('&', $postdata);
		    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
		}
		if(count($headers) > 0){
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		}
	    
	    $content = curl_exec($ch);

	    return $content;
	}

	function close_curl($ch){
	    curl_close($ch);
	    // unlink('cookie.txt');
	}