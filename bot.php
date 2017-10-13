<?php
    require_once('class/Linkedin.php');
    require_once('class/Watson.php');
    require_once('const.php');
    require_once('db.php');

    // INITIALIZATION
    date_default_timezone_set('Europe/Stockholm');

    $account = getAccount(count($argv)>1 ? intval($argv[1]) : 0);
    if($account == null){
        exit('Not an account');
    }else if($account['active'] == false){
        exit('Account not activate');
    }

    $watson = new Watson(WATSON_USERNAME, WATSON_PASSWORD, WATSON_CONVERSATION);

    $asked_connect_max = 10;

    while(true){
        if(getIsOn() && getAccount($account['ID'])['active'] && intval(date('H', time())) >= 8 && intval(date('H', time())) < 20){ // good hour :)
            
            // check if account has not been inactivated or deleted
            $account = getAccount($account['ID']);
            if($account == null){
                exit('Account has been deleted');
            }else if($account['active'] == false){
                setAction('This account is not activated', $account['ID']);
                // goto BotDetected;
            }        
            setAction(time(), $account['ID']);

            do_sleep(2);
/*
            if($account['detected'] == 1){goto BotDetected;}

            $li = new Linkedin($account['ID']);
            // for each iteration, we close curl to save cookie and we re open it to know if the bot is detected.
            $li->close();
            $li = new Linkedin($account['ID']);

            if(checkBotDetected()){goto BotDetected;}

            setAction('The bot is connected.', $account['ID']);

            // all key words not done
    	    $key_words_list = getKeyWords(false, $account['ID']);
    	    // SEND CONNECT REQUESTS
            if(count($key_words_list) > 0){
                $key_word_selected = $key_words_list[0];
                $page = $key_word_selected['page'];
        	    $key_word = $key_word_selected['key_word'];
                $key_word_id = $key_word_selected['ID'];
        	    
        	    // search people with this key word
                $sendConnect = true;
                $countConnect = 0;
        	    while($sendConnect){
                    if(checkBotDetected()){goto BotDetected;}

        	    	$result = $li->search_to_array($key_word, $page);
                    setAction('The bot is doing a search with this key word: <b>'.$key_word.'</b> (page '.$page.').', $account['ID']);
                    do_sleep();
        	    	// sending connnect request if not already sent
                    if(count($result)==0){
                        $page = 1;  //reinit;
                        $sendConnect = false;
                        setKeyWordDone($key_word_id);
                        $key_words_count++; // new key word
                    }
        	    	foreach ($result as $profile_id) {
                        // old: karina's account, rest : actual account.
                        $friend = count(directQuery('SELECT ID FROM old_connect_asked WHERE profile_id="'.$profile_id.'"')) != 0 || count(directQuery('SELECT ID FROM old_connect_list WHERE profile_id="'.$profile_id.'"')) != 0 || count(directQuery('SELECT ID FROM connect_asked WHERE profile_id="'.$profile_id.'"')) != 0 || count(directQuery('SELECT ID FROM connect_list WHERE profile_id="'.$profile_id.'"')) != 0;
                        if(!$friend){   //not already friend
            	    		$already = $li->connectTo($profile_id);
                            if($already != null){   // if sent, else means that we already asked this user so we can skip it
                                $countConnect++;
                                setAction('The bot found some users for the key word <b>'.$key_word.'</b> (page '.$page.').<br>It is sending a connect request to this ID: '.$profile_id.'.', $account['ID']);
                	    		do_sleep();
                            }else{
                                setAction('Connection request already sent to those users.', $account['ID']);
                            }
                        }
        	    	}
                    if($countConnect>$asked_connect_max){  // stop connect, let's chat and we continue next time so page don't change
                        $sendConnect=false;
                    }
                    $page++;
                    setKeyWordPage($key_word_id, $page);
        	    }
            }

            if(checkBotDetected()){goto BotDetected;}
            
    	    // NEW CONNECTIONS
            //check and save new connections  
    		$newConnections = $li->checkNewConnections();
            setAction('The bot is saving all users who accepted the connection.', $account['ID']);
            do_sleep();
            // accept connection requests
            $new = $li->acceptLastConnectionRequest();
            if($new != null){   // will add if not null -> new connection
                $newConnections = array_merge($newConnections, $new);
                setAction('The bot is accepting the connection requests that other users sent.', $account['ID']);
            }
            do_sleep();

            if(checkBotDetected()){goto BotDetected;}

            //send default msg to new connections
    		foreach ($newConnections as $key => $profile_id) {
                if(count(directQuery('SELECT * FROM old_msg_conversation WHERE profile_id="'.$profile_id.'"')) != 0 || count(directQuery('SELECT * FROM msg_conversation WHERE profile_id="'.$profile_id.'"')) != 0){
                    continue;
                }

                $userInfos = $li->getUserInformations($profile_id);
                do_sleep();
                $defaultContext = $watson->chat('BotYoupicDefaultStart')->context;
                $defaultContext->firstName = $userInfos['firstName'];
                $defaultContext->lastName = $userInfos['lastName'];
                $defaultContext->job = $userInfos['job'];
    			// send msg to new connections
                $templates = getAllTemplates(true, $account['ID']);
                $selectedTemplate = rand(0, count($templates)-1);
                setAction('The bot is sending default message n°'.$templates[$selectedTemplate]['ID'].' to this user ID: '.$profile_id.'.', $account['ID']);   
                $li->sendMsg($profile_id, str_replace('<br />', '\n', $templates[$selectedTemplate]), 0, serialize($defaultContext));
    			do_sleep();
    		}

            if(checkBotDetected()){goto BotDetected;}

            // CHECK UNREAD CONVERSATION in LinkedIn
            $unreadConv = $li->getUnreadConversations();
            setAction('The bot is checking new unread conversations.', $account['ID']);
            do_sleep();
            foreach ($unreadConv as $key => $conv) {
                // saving all new msgs in database
                $msgs = $li->getAllMsg($conv);
                foreach ($msgs as $key => $msg) {
                    setAction('The bot is saving in database all new messages of conversation n°'.$conv, $account['ID']);
                    $li->saveMsg($msg);
                }
                do_sleep();
            }

            if(checkBotDetected()){goto BotDetected;}

            // all unread conversation in database watson didn't try to answer yet
            $convToAnswer = getMsgReceived($account['ID'], null, null, null, null, null, false, false);
            if(is_array($convToAnswer) || is_object($convToAnswer)){
                foreach ($convToAnswer as $key => $value) {
                    $conv = getConversation($value['conv_id']);
                if($value['conv_id'] == '6318828055766855680'){ // !!!!!! ME WITH KARINA
                    $last = end($conv);
                    if($last['by_bot']){    // should not append but still...
                        setRead($last['conv_id']);
                    }else{
                        setAction('The bot is trying to answer a message with Watson.', $account['ID']);
                        $context = unserialize(getLastContext($last['conv_id']));
                        $watsonAnswer = $watson->chat($last['msg'], $context);
                        if(isset($watsonAnswer->output->text[0])){    // watson can answer
                            $newContext = $watsonAnswer->context;
                            if($context == null){
                                // previous context null -> first msg
                                $userInfos = $li->getUserInformations($last['profile_id']);
                                do_sleep();
                                $newContext->firstName = $userInfos['firstName'];
                                $newContext->lastName = $userInfos['lastName'];
                                $newContext->job = $userInfos['job'];
                            }
                            $li->sendMsg($last['profile_id'], $watsonAnswer->output->text[0], true, serialize($newContext));
                            do_sleep();
                        }else{  // we put the context to "anything else" to avoid new bot msg for this user
                            setContext($last['msg_id'], serialize($watson->chat('anything_else')->context));
                        }
                        setWatsonTry($last['msg_id']);
                    }
                }
                }
            }

            BotDetected:    // goto botDetected and start again the loop
            if($li->getBotDetected()){   // cookie not good!
                setIsDisconnect(true, $account['ID']);
                setAction('This account is disconnected but tries to reconnect: '.$account['email'], $account['ID']);
                // try to reconnect
                $li = new Linkedin($account['ID']);
                $li->close();   // save cookies and see if connected
                $li = new Linkedin($account['ID']);
                if($li->getBotDetected()){  // can't reconnect
                    setAction('This account is disconnected, you have to reconnect (with a human way): '.$account['email'], $account['ID']);
                }else{
                    setAction('This account reconnected with success: '.$account['email'], $account['ID']);
                    setIsDisconnect(false, $account['ID']);
                }
            }*/
	    }else{
            if(!getIsOn()){
                setAllAction('Please turn me on!');
            }else if(getAccount($account['ID'])['active']){
                setAction('This account is not active', $acount['ID']);
            }else{
                setAction('The bot is sleeping until 8 AM', $account['ID']);
            }
	    	do_sleep(30);
	    }
    }

    setAction('An error occured with this account', $account['ID']);

    $watson->close();
    $li->close();

    function do_sleep($time=null){
        if($time != null){
            sleep($time);
        }else{
            $max_time_sleep = 120; //seconds
            sleep(rand(60, $max_time_sleep));
        }
    }

    function checkBotDetected(){
        global $li, $account;
        $li->close();
        $li = new Linkedin($account['ID']);
        // detected or off
        return $li->getBotDetected() || !getIsOn() || getAccount($account['ID']) == null || !getAccount($account['ID'])['active'];
    }

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