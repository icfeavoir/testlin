<?php
    require_once('class/Linkedin.php');
    require_once('class/Watson.php');
    require_once('const.php');
    require_once('db.php');

    $watson = new Watson(WATSON_USERNAME, WATSON_PASSWORD, WATSON_CONVERSATION);

    $li = new Linkedin(1);
    // WATSONIZE !!!
    $watsonize = (directQuery('SELECT * FROM msg_conversation WHERE by_bot=0 AND template_msg != 0'));

    foreach ($watsonize as $key => $msg) {
    	$previous = directQuery('SELECT * FROM msg_conversation WHERE conv_id = '.$msg['conv_id'].' AND ID != '.$msg['ID'].' ORDER BY ID DESC LIMIT 1');
    	$watson = $previous[0]["watson_context"];
    	$statement = $db->prepare('UPDATE msg_conversation SET watson_context = :watson WHERE ID = :ID');
		  $statement->execute(array(':ID'=>$msg['ID'], ':watson'=>$watson));
    }

    $li->close();