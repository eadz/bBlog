<?php
// mail.php - send notifications and the like
// mail.php - author: Eaden McKee

// first lets set some defaults

define('MAIL_HEADER','
Greetings,
You are receiving this notification because you have chosen to receive notifications from '.C_BLOGNAME.'.

');

define('MAIL_FOOTER','

Regards,
'.C_BLOGNAME.',
'.C_BLOGURL.'
');

define('MAIL_FROM','"'.htmlspecialchars(C_BLOGNAME).'"'.' <'.C_EMAIL.'>');

// function to notify the owner about a new comment or post. 
function notify_owner($subject,$message) { 
	// do they want notifications?
	if(C_NOTIFY == 'true') { 
		mail(C_EMAIL,$subject,MAIL_HEADER.$message.MAIL_FOOTER,"From: ".MAIL_FROM."\r\nErrors-To: ".MAIL_FROM."\r\n");

	}

}

// function to notify the poster that a reply has been posted to their comment. 
function notify_poster ($to,$subject,$message) {
 // not yet implimented.

}
?>
