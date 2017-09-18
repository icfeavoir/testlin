<?php
	require_once('Linkedin.php');
    require_once('const.php');
    require_once('db.php');

    $li = new Linkedin();

    print_r($li->getUnreadConversations());

    $li->close();    