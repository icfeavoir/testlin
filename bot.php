<?php
    require_once('class/Linkedin.php');
    require_once('class/Watson.php');
    require_once('const.php');
    require_once('db.php');

    function do_sleep($time=null){
    	if($time != null){
    		sleep($time);
    	}else{
	    	$max_time_sleep = 60; //seconds
	    	sleep(rand(1, $max_time_sleep));
	    }
    }

    // INITIALIZATION
    $key_words_count = 0;
    $page = 1;

    setAction('The bot is connecting to the account.');
    $li = new Linkedin(USERNAME, PASSWORD);
    $li->close();   //save cookies
    $li = new Linkedin();

    $watson = new Watson(WATSON_USERNAME, WATSON_PASSWORD, WATSON_CONVERSATION);

    while(true){
	    if(getIsOn() && !$li->getBotDetected() && intval(date('G', time())) >= 7 && intval(date('H', time())) < 23){ // good hour :)
    	    $key_words_list = getKeyWords();
    	    // SEND CONNECT REQUESTS
    	    $key_words_count %= count($key_words_list);
    	    $key_word = $key_words_list[$key_words_count]['key_word'];
    	    
    	    // search people with this key word
            $sendConnect = true;
            $countConnect = 0;
    	    while($sendConnect){
    	    	$result = $li->search_to_array($key_word, $page);
                setAction('The bot is doing a search with this key word: '.$key_word.' (page '.$page.').');
                do_sleep();
    	    	// sending connnect request if not already sent
                if(!empty($result)){
                    $page = 1;  //reinit;
                    $sendConnect = false;
                    $key_words_count++; // new key word
                }
    	    	foreach ($result as $profile_id) {
                    setAction('The bot is sending a connect request to this ID: '.$profile_id);
    	    		$already = $li->connectTo($profile_id);
                    if($already != null){   // if sent, else means that we already asked this user so we can skip it
                        $countConnect++;
        	    		do_sleep();
                    }
    	    	}
                if($countConnect>100){  // stop connect, let's chat and we continue next time so page don't change
                    $sendConnect=false;
                }
                $page++;
    	    };&
            
    	    // NEW CONNECTIONS
            //check and save new connections  
    		$newConnections = $li->checkNewConnections();
            setAction('The bot is saving all users who accepted the connection.');
            // accept connection requests
            if($new = $li->acceptLastConnectionRequest()){   // will add if not null -> new connection
                array_push($newConnections, $new);
                setAction('The bot is accepting the connection requests that other users sent.');
            }
            //send msg to new connections
    		foreach ($newConnections as $key => $profile_id) {
    			// send msg to new connections
                $templates = getAllTemplates(true);
                $selectedTemplate = rand(0, count($templates)-1);
                setAction('The bot is sending default message n°'.$templates[$selectedTemplate]['ID'].' to this user ID: '.$profile_id.'.');   
                $li->sendMsg($profile_id, str_replace('<br />', '\n', $templates[$selectedTemplate]));
    			do_sleep();
    		}

            // CHECK UNREAD CONVERSATION in LinkedIn
            $unreadConv = $li->getUnreadConversations();
            setAction('The bot is checking new unread conversations.');
            do_sleep();
            foreach ($unreadConv as $key => $conv) {
                //if not already saved
                if(count(getConversation($conv)) == 0){
                    // saving all new msgs in database
                    $msgs = $li->getAllMsg($conv);
                    foreach ($msgs as $key => $msg) {
                        setAction('The bot is saving in database all new messages.');
                        $li->saveMsg($msg);
                    }
                    do_sleep();
                }
            }

            // all unread conversation in database watson didn't try to answer yet
            $convToAnswer = getMsgReceived(null, null, null, null, null, false, false);
            foreach ($convToAnswer as $key => $value) {
                $conv = getConversation($value['conv_id']);
                if(in_array($value['conv_id'], array('6313350241940750337',))){ // !!!!!!!!!!!!!!!!!!!!!!!!!!
                    $last = end($conv);
                    if($last['by_bot']){
                        setRead($conv['conv_id']);
                    }else{
                        setWatsonTry($last['msg_id']);
                        setAction('The bot is trying to answer a message with Watson.');
                        $watsonAnswer = $watson->chat($last['msg']);
                        if($watsonAnswer != false){    // watson can answer
                            $li->sendMsg($last['profile_id'], $watsonAnswer, true);
                        }
                        do_sleep();
                    }
                }
            }
	    }else{
            if(!getIsOn()){
                setAction('Please turn me on!');
            }else if($li->getBotDetected()){   // cookie not good!
                setIsOn(false);
                setIsDisconnect(true);
                setAction('The bot is disconnected, try to reconnect...');
                $li = new Linkedin(USERNAME, PASSWORD);
                $li->close();   // save cookies
                $li = new Linkedin();
                if($li->getBotDetected()){  // can't reconnect
                    setAction('The bot is disconnected, you have to reconnect!.');
                }else{
                    setAction('Reconnected with success!');
                    setIsDisconnect(false);
                    setIsOn(true);
                }
            }else{
                setAction('The bot is sleeping from 10 PM until 7 AM');
            }
	    	do_sleep(30);
	    }
    }

    setAction('THE BOT STOPPED, YOU HAVE TO RELAUNCH IT ON THE SERVER');

    $watson->close();
    $li->close();

    /*
		ALGO
        ====

		check if linkedin disconnect the bot 
			NO -> continue
			YES -> wait for 2 days!
		---------------------------------------
    
    I. KEY WORDS -> CONNECT
		1. Key words
		2. search_to_array()
		3. connect_to (if not yet of course)
		---------------------------------------

    II. NEW CONNECTION REQUESTS? -> ACCEPT
        1. Accept new connection request
        ---------------------------------------

    III. NEW CONNECTIONS? -> SEND MSG
        1. check for last connections accepted
        2. save in DB
        3. send msg (random in templates active) to new connections
        ---------------------------------------

    IV. CHECK UNREAD CONVERSATIONS -> ANSWER WITH WATSON
        1. get all unread conversations
        2. save in DB
        3. get the last message
            -> answer it if Watson has an answer to give
            -> else let the conversation unread
        ---------------------------------------
    */