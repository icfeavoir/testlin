<?php
	$accounts = array(
			'hirehore@zipcad.com'		=> 'testlinkedin01',	//Last Account

			'karina@youpic.com'			=> 'wilfagamechange2015	',		// REAL ACCOUNT
		);

	$mails = array_keys($accounts);
	$pass = array_values($accounts);

	$selected_account = 1;

	define('USERNAME', $mails[$selected_account]);
	define('PASSWORD', $pass[$selected_account]);

	define('HOST', 'localhost');
	define('DB_NAME', 'linkedinBot');
	define('DB_USER', 'root');
	define('DB_PASSWORD', 'root');