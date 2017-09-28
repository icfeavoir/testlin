<?php
    require_once('class/Linkedin.php');
    require_once('class/Watson.php');
    require_once('const.php');
    require_once('db.php');

    // INITIALIZATION
    $key_words_count = 0;
    $page = 1;
    $asked_connect_max = 10;

    setAction('The bot is connecting to the account.');
    $li = new Linkedin();

    $watson = new Watson(WATSON_USERNAME, WATSON_PASSWORD, WATSON_CONVERSATION);

    while(true){
	    if(getIsOn() && !checkBotDetected() && intval(date('G', time())) >= 7 && intval(date('H', time())) < 23){ // good hour :)
            
            // for each iteration, we close curl to save cookie and we re open it to know if the bot is detected.
            $li->close();
            $li = new Linkedin();
            if(checkBotDetected()){goto BotDetected;}

    	    $key_words_list = getKeyWords();
    	    // SEND CONNECT REQUESTS
            if(count($key_words_list) > 0){
                $key_words_count %= count($key_words_list);
        	    $key_word = $key_words_list[$key_words_count]['key_word'];
                $key_word_id = $key_words_list[$key_words_count]['ID'];
        	    
        	    // search people with this key word
                $sendConnect = true;
                $countConnect = 0;
        	    while($sendConnect){
                    if(checkBotDetected()){goto BotDetected;}

        	    	$result = $li->search_to_array($key_word, $page);
                    setAction('The bot is doing a search with this key word: <b>'.$key_word.'</b> (page '.$page.').');
                    do_sleep();
        	    	// sending connnect request if not already sent
                    if(count($result)==0){
                        $page = 1;  //reinit;
                        $sendConnect = false;
                        setKeyWordDone($key_word_id);
                        $key_words_count++; // new key word
                    }
        	    	foreach ($result as $profile_id) {
                        $friend = count(directQuery('SELECT ID FROM old_connect_asked WHERE profile_id="'.$profile_id.'"')) != 0 || count(directQuery('SELECT ID FROM old_connect_list WHERE profile_id="'.$profile_id.'"')) != 0;
                        if(!$friend){   //not already friend
            	    		$already = $li->connectTo($profile_id);
                            if($already != null){   // if sent, else means that we already asked this user so we can skip it
                                $countConnect++;
                                setAction('The bot found some users for the key word <b>'.$key_word.'</b> (page '.$page.').<br>It is sending a connect request to this ID: '.$profile_id.'.');
                	    		do_sleep();
                            }else{
                                setAction('Connection request already sent to those users.');
                            }
                        }
        	    	}
                    if($countConnect>$asked_connect_max){  // stop connect, let's chat and we continue next time so page don't change
                        $sendConnect=false;
                    }
                    $page++;
        	    }
            }

            if(checkBotDetected()){goto BotDetected;}
            
    	    // NEW CONNECTIONS
            //check and save new connections  
    		$newConnections = $li->checkNewConnections();
            setAction('The bot is saving all users who accepted the connection.');
            // accept connection requests
            $new = $li->acceptLastConnectionRequest();
            if($new != null){   // will add if not null -> new connection
                $newConnections = array_merge($newConnections, $new);
                setAction('The bot is accepting the connection requests that other users sent.');
            }

            if(checkBotDetected()){goto BotDetected;}

            //send default msg to new connections
    		foreach ($newConnections as $key => $profile_id) {
                if(count(directQuery('SELECT * FROM old_msg_conversation WHERE profile_id="'.$profile_id.'"') != 0)){
                    break;
                }

                $userInfos = $li->getUserInformations($profile_id);
                do_sleep();
                $defaultContext = $watson->chat('BotYoupicDefaultStart')->context;
                $defaultContext->firstName = $userInfos['firstName'];
                $defaultContext->lastName = $userInfos['lastName'];
                $defaultContext->job = $userInfos['job'];
    			// send msg to new connections
                $templates = getAllTemplates(true);
                $selectedTemplate = rand(0, count($templates)-1);
                setAction('The bot is sending default message n°'.$templates[$selectedTemplate]['ID'].' to this user ID: '.$profile_id.'.');   
                $li->sendMsg($profile_id, str_replace('<br />', '\n', $templates[$selectedTemplate]), 0, serialize($defaultContext));
    			do_sleep();
    		}

            if(checkBotDetected()){goto BotDetected;}

            // CHECK UNREAD CONVERSATION in LinkedIn
            $unreadConv = $li->getUnreadConversations();
            setAction('The bot is checking new unread conversations.');
            do_sleep();
            foreach ($unreadConv as $key => $conv) {
                // saving all new msgs in database
                $msgs = $li->getAllMsg($conv);
                foreach ($msgs as $key => $msg) {
                    setAction('The bot is saving in database all new messages of conversation n°'.$conv);
                    $li->saveMsg($msg);
                }
                do_sleep();
            }

            if(checkBotDetected()){goto BotDetected;}

            // all unread conversation in database watson didn't try to answer yet
            $convToAnswer = getMsgReceived(null, null, null, null, null, false, false);
            if(is_array($convToAnswer) || is_object($convToAnswer)){
                foreach ($convToAnswer as $key => $value) {
                    $conv = getConversation($value['conv_id']);
                if($value['conv_id'] == '6318828055766855680'){ // !!!!!!
                    $last = end($conv);
                    if($last['by_bot']){    // should not append but still...
                        setRead($last['conv_id']);
                    }else{
                        setAction('The bot is trying to answer a message with Watson.');
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

            BotDetected:    //are for goto botDetected and start again the loop

	    }else{
            if(!getIsOn()){
                setAction('Please turn me on!');
            }else if(checkBotDetected()){   // cookie not good!
                setIsOn(false);
                setIsDisconnect(true);
                setAction('The bot is disconnected but tries to reconnect...');
                // try to reconnect
                $li = new Linkedin(USERNAME, PASSWORD);
                $li->close();   // save cookies and see if connected
                $li = new Linkedin();
                if(checkBotDetected()){  // can't reconnect
                    setAction('The bot is disconnected, you have to reconnect (with a human way)!');
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

    function do_sleep($time=null){
        if($time != null){
            sleep($time);
        }else{
            $max_time_sleep = 150; //seconds
            sleep(rand(20, $max_time_sleep));
        }
    }

    function checkBotDetected(){
        global $li;
        $li->close();
        $li = new Linkedin();
        return $li->getBotDetected();
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