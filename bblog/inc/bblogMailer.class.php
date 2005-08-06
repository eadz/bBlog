<?php
/**
 * bblogMailer.class.php
 * <p>
 * Here's a class that extends the PHPMailer class 
 * and sets the defaults for our bBlog site.
 * This saves us the trouble of hacking the original phpMaier,
 * to ease upgrading and make our life easier :)
 * <p>
 * @author xushi - <xushi.xushi@gmail.com>
 * @source phpmailer - <http://phpmailer.sourceforge.net/>
 * @licence GPL
 */

require("class.phpmailer.php");

class bblogMailer extends PHPMailer {

    // Set default variables for all new objects
    
    // TODO: i'd rather have the $Host be read from
    // the db instead of writing it here. Just incase
    // any robot would come, or any faggot wants to 
    // hack this file for spam reasons.
    var $From     = "bblog@bblog.com";
    var $FromName = "The bBlog Team";
    var $Host     = "xushi.co.uk;xushi.co.uk"; //currently mine.. change to bblog's later.
    var $Mailer   = "smtp";                         // Alternative to IsSMTP()
    var $WordWrap = 75;

    
    // Replace the default error_handler
    function error_handler($msg) {
        print("My Site Error");
        print("Description:");
        printf("%s", $msg);
        exit;
    }
}
?>
