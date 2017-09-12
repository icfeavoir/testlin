<?php
	$accounts = array(
			'dujimo@morsin.com'			=> 'testlinkedin01',	//Have Invite
			'sibavacu@crusthost.com'	=> 'testlinkedin01',	//Hello Guys
			'vemi@nypato.com'			=> 'testlinkedin01',	//Vasry Kedja
			'yicupif@dndent.com'		=> 'testlinkedin01',	//Najae Hivosky
			'hirehore@zipcad.com'		=> 'testlinkedin01',	//Last Account

			'karina@youpic.com'			=> 'wilfagamechange2015	',		// REAL ACCOUNT
		);

	$mails = array_keys($accounts);
	$pass = array_values($accounts);

	$selected_account = 5;

	define('USERNAME', $mails[$selected_account]);
	define('PASSWORD', $pass[$selected_account]);