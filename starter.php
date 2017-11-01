<?php
    require_once('const.php');
    require_once('db.php');

    /* INDEX */

    $accounts = getAllAccounts();
    $list = array();
    foreach ($accounts as $key => $value) {
        array_push($list, '(nohup php bot.php '.$value['ID'].' &)');
    }
    $exec = implode(' && ', $list);
    exec('cd && cd '.FULL_PATH.' && '.$exec.' &> log.txt');