<?php
    require_once('class/Linkedin.php');
    require_once('class/Watson.php');
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
    $watson = new Watson(WATSON_USERNAME, WATSON_PASSWORD, WATSON_CONVERSATION);

    while(true){
	    if(getIsOn()){
    		if($li->getBotDetected()){
    			do_sleep(172800);	// if bot detected, sleep 48 hours
    			$li = new Linkedin(USERNAME, PASSWORD);
    		}
            
    	    $key_words_list = getKeyWords();
    	    // SEND CONNECT
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
            //check and save new connections  
    		$newConnections = $li->checkNewConnections();
            // accept connection requests
            if($new = $li->acceptLastConnectionRequest())   // will add if not null -> new connection
                array_push($newConnections, $new);
            //send msg to new connections
    		foreach ($newConnections as $key => $profile_id) {
    			// send msg to new connections
    			$li->sendMsg($profile_id, getDefaultMsg()['msg']);
    			do_sleep();
    		}

            // CHECK UNREAD CONVERSATION
            //... watson to do here !
	    }else{
	    	do_sleep(30);
	    }
    }

    /* WATSON

    $start = false;
    $new = [];
    while(getIsOn()){
        if(!$start){
            $new = $li->checkNewConnections();
            do_sleep();
        }

        if(count($new) == 0){
            do_sleep();
        }else{
            if(!$start){
                $li->sendMsg($user, 'Hello!');
                $start = true;
                do_sleep();
            }
            $conv = $li->getUnreadConversations()[0];
            $msgs = $li->getAllMsg($conv);
            if(count($msgs) > 0){
                $last = $msgs[count($msgs)-1];
                if($last['by'] != 'bot'){
                    $li->sendMsg($user, $watson->getResponse($watson->sendMsg($last['msg'])));
                    $msgs = [];
                }
            }
        }
        do_sleep();
    }

    */
    

    $watson->close();
    $li->close();

    /*
		ALGO
        ====

		check if linkedin disconnect the bot 
			NO -> continue
			YES -> wait for 2 days!
		---------------------------------------

		1. Key words
		2. search_to_array()
		3. connect_to (if not yet of course)
		---------------------------------------

        1. Accept new connection request
        ---------------------------------------

        1. check for last connections accepted
        2. send msg to new connections
        ---------------------------------------

        1. get a random unread conversation
        2. get the last message
            -> answer it if Watson has an answer to give
            -> else let the conversation unread
        ---------------------------------------
    */