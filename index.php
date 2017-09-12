<?php

    require_once('const.php');
    require_once('curl_functions.php');

    $ch = curl_init();

    if(!is_file('cookie.txt'))
        login($ch, USERNAME, PASSWORD);

    echo access_page($ch, 'messaging/');
    close_curl($ch);


