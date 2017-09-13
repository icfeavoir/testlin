<?php
	require 'Linkedin.php';

    require_once('const.php');
    require_once('db.php');

    $li = new Linkedin();

    $me = $li->search_to_array('Pierre leroy youpic')[0];
    $msg = 'Hey, saving this in database again';

    $t = $li->connect_to($me, false);
    if($t ===null)
    	echo 'Already sent';

    $li->close_curl();