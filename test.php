<?php

	// TEST THE DB API
	$ch = curl_init("http://botlinkedin/api_db.php");

	$postdata = array(
		'api_db'=>true,
		'function'=>'test',
		'data'=>array(
			1,
			5,
		),
	);

	$postdata = json_encode($postdata);

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);

	echo curl_exec($ch);

	echo "\n";