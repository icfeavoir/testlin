<?php
	require_once('Linkedin.php');
    require_once('const.php');
    require_once('db.php');

    function do_sleep($time=null){
    	if($time != null){
    		sleep($time);
    	}else{
	    	$max_time_sleep = 30; //seconds
	    	sleep(rand(1, $max_time_sleep));
	    }
    }

    // INITIALIZATION
    $key_words_count = 0;

    $li = new Linkedin();

    while(true){
	    if(getIsOn()){
    		if($li->getBotDetected()){
    			do_sleep(172800);	// if bot detected, sleep 48 hours
    			$li = new Linkedin(USERNAME, PASSWORD);
    		}
            
    	    $key_words_list = getKeyWords();
    	    // CONNECT
    	    $key_words_count %= count($key_words_list);
    	    $key_word = $key_words_list[$key_words_count]['key_word'];
    	    $key_words_count++;
    	    $page = 1;
    	    // search people with this key word
    	    do{
    	    	$result = $li->search_to_array($key_word, $page);
    	    	$page++;
    	    	// sending connnect request if not already sent
    	    	foreach ($result as $profile_id) {
    	    		$li->connect_to($profile_id);
    	    		do_sleep();
    	    	}
    	    	do_sleep();
    	    }while(!empty($result));
            
    	    // NEW CONNECTIONS
            // accept new connections
            $li->acceptLastConnectionRequest();

            //send msg to new connections    	    
    		$newConnections = $li->checkNewConnections();
    		foreach ($newConnections as $key => $profile_id) {
    			// send msg to new connections
    			$li->send_msg($profile_id, getDefaultMsg()['msg']);
    			do_sleep();
    		}
	    }else{
	    	do_sleep(30);
	    }
    }

    $li->close();

    /*
		ALGO

		check if linkedin disconnect the bot 
			NO -> continue
			YES -> wait for 2 days!
		=====

		1. Key words
		2. search_to_array()
		3. connect_to (if not yet of course)
		=====

		1. check for last connections accepted
		2. send msg to new connections
		====
    */