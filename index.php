<?php
	require_once('Linkedin.php');
    require_once('const.php');
    require_once('db.php');

    $li = new Linkedin(USERNAME, PASSWORD);

    $who = $li->search_to_array('fejhz fbqzejksbkjesdnvjkdbvjkqenvd vjzev qsjkvns jkvnejkn vjknvjqzenvjenvjk wdbj jksvjkbvzejhberhbl');
    $li->connect_to($who[0]);
    $li->close_curl();


    // TODO : new connect request from other to bot?


    