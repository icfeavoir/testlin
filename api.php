<?php
    require_once('const.php');
    require_once('db.php');

    if(isset($_POST['email'] && isset($_POST['password']))){
    	$request = $db->prepare('SELECT ID FROM accounts WHERE email=:email AND password=:password LIMIT 1');
    	$request->execute(array(':email'=>$_POST['email'], ':password'=>$_POST['password']));
    	if($request->rowCount == 0){
    		echo 'error';
    	}else{
    		echo $request->fetch()['ID'];
    	}
    }else{
    	echo 'error';
    }