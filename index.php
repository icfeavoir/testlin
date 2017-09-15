<?php
	require_once('Linkedin.php');
    require_once('const.php');
    require_once('db.php');

    $li = new Linkedin();

    $me=$li->search_to_array('Pierre Leroy Youpic')[0];
    // $li->send_msg($me, 'Hello there!');
    print_r(Linkedin::noInst()->getAllMsg('6313350241940750337'));
    $li->close_curl();


    // TODO : new connect request from other to bot?


    /*
		ALGO
		----
		1. Key words
		2. search_all_to_array()
		3. connect_to (if not yet of course)
		=====

		in //
		-----
		1. check for last connections accepted
		2. send msg to new connections
		====

		in //
		-----
		1. check for last connections asked
		2. accept connect
		2. send msg
		====

		in //
		-----
		1. check for unread messages
		2. notify us
    */