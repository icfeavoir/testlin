<?php
	$accounts = array(
			'user'=>'pass';
		);

	$mails = array_keys($accounts);
	$pass = array_values($accounts);

	$selected_account = 0;

	define('USERNAME', $mails[$selected_account]);
	define('PASSWORD', $pass[$selected_account]);

	define('HOST', '');
	define('DB_NAME', '');
	define('DB_USER', '');
	define('DB_PASSWORD', '');