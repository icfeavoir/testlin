<?php
    require_once('const.php');
    require_once('db.php');

    $post = json_decode(file_get_contents("php://input"));

    if(isset($post->api_db) && isset($post->function)){
        $function = $post->function;
        echo json_encode($function(...$post->data));
    }else{
        print_r($post);
    }
