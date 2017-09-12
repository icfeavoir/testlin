<?php

    require_once('const.php');
    require_once('curl_functions.php');

    $ch = curl_init();
    $headers = array();

    if(!is_file('cookie.txt'))
        login($ch, USERNAME, PASSWORD, $headers);

    // $headers = array(
    //         'Access-Control-Allow-Origin: *',
    //         'pragma: no-cache',
    //         'accept-encoding: gzip, deflate, br',
    //         'accept-language: fr-FR,fr;q=0.8,en-US;q=0.6,en;q=0.4',
    //         'upgrade-insecure-requests: 1',
    //         'user-agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.113 Safari/537.36',
    //         'accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
    //         'cache-control: no-cache',
    //         ':authority: www.linkedin.com',
    //         'cookie: bscookie="v=1&20170906141844bf125dc7-16f1-4eb0-81d0-ba5ebd513bc8AQHZ1Tol1CS2XLaBELHKTARYMIgpMQtp"; visit="v=1&M"; bcookie="v=2&45e939ed-ae9c-4d72-8487-1b20541deff4"; li_at=AQEDASQrJ2AE96jIAAABXmDIwgcAAAFehNVGB04Awo-b7dIt6wk9u7hPv1pHC4NoH3wrZddfq-6gLgW4HEWzsa4Ot0naFISTQOPqYtJUrejI8Jfp6rfa9hbUSgsK2sS1_U-KSyFU0ibMX16qdpEY_bi9; liap=true; sl=v=1&SlB_y; JSESSIONID=ajax:7005973901477227315; lang="v=2&lang=fr-fr"; _gat=1; _ga=GA1.2.693165495.1504820648; _lipt=CwEAAAFeYPxDzufbifjjqrYSPSSYhbLfWuFQsxODCv65qwrf-kyMO8kATeOaouAXfZKYNsbIDm7N9oBgC1I8bUqQTY7vGTD2L1NggbEkS1_PzvW2Kxw6j1vtbpE; lidc="b=TGST02:g=531:u=1:i=1504865719:t=1504948723:s=AQEm4ux00tq2mRUuevGDvR09WxEnIdW_"',
    //     );

    echo access_page($ch, '');

    close_curl($ch);