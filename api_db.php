<?php
    require_once('const.php');
    require_once('db.php');

    $post = json_decode(file_get_contents("php://input"));



    if(isset($post->api_db) && isset($post->function)){
        // $data = "";
        // if(isset($post->data)){
        //     foreach ($post->data as $key => $value) {
        //         $data .= $value.",";
        //     }
        // }
        $function = $post->function;
        echo $function(...$post->data);
    }else{
        print_r($post);
    }


    function test($p1, $p2){
        return $p1+$p2;
    }
