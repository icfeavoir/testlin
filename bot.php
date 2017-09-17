<?php
	require_once('Linkedin.php');
    require_once('const.php');
    require_once('db.php');

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

	