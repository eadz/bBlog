<?php
/**
 * mail.php - send notifications and the like
 *
 * PHP versions 4 and 5
 *
 * @package bBlog
 * @author Eaden McKee - <email@eadz.co.nz> - last modified by $LastChangedBy: $
 * @version $Id: $
 * @copyright The bBlog Project, http://www.bblog.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

// first let's set some defaults

define('MAIL_HEADER','
Greetings,
You are receiving this notification because you have chosen to receive notifications from '.C_BLOGNAME.'.

');

define('MAIL_FOOTER','

Regards,
'.C_BLOGNAME.',
'.C_BLOGURL.'
');

// we don't need this anymore as it is set by bBlogMailer class
//define('MAIL_FROM','"'.htmlspecialchars(C_BLOGNAME).'"'.' <'.C_EMAIL.'>');

// function to notify the owner about a new comment or post.
function notify_owner($subject,$message) {
    // do they want notifications?
    if(C_NOTIFY == 'true') {
        $to = C_EMAIL; 
        
        // fprosper: this calls notify_poster to centralize code 
        // maybe we should change the function name ?!
        notify_poster($to,$subject,$message);
    }
}

// function to notify someone of a reply to their message
function notify_poster($to,$subject,$message) {
    require_once(BBLOGROOT.'inc/bBlogMailer.class.php');

    // instantiates bBlogMailer class
    $mail = new bBlogMailer();

    // specifies mail parameters
    $mail->AddAddress($to, "Anonymous"); // todo: the name should not be static
    $mail->Subject = $subject;
    $mail->Body    = MAIL_HEADER.$message.MAIL_FOOTER;

    // and finally sends the email ;)
    $mail->Send();
}

?>
