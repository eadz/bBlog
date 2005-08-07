<?php
/**
 * bblogMailer.class.php - extends the PHPMailer class
 * <p>
 * Here's a class that extends the PHPMailer class 
 * and sets the defaults for our bBlog site.
 * This saves us the trouble of hacking the original phpMaier,
 * to ease upgrading and make our life easier :)
 * <p>
 * @author xushi - <xushi.xushi@gmail.com>
 * @source phpmailer - <http://phpmailer.sourceforge.net/>
 * @licence GPL <http://www.gnu.org/copyleft/gpl.html>
 */

require("class.phpmailer.php");

class bblogMailer extends PHPMailer {

    // Set default variables for all new objects
    
    // TODO: i'd rather have $Host be read from bb_config,
    // instead of writing it here. Just incase any robot 
    // would come, or any faggot wants to hack this file 
    // for spam reasons. Same goes with $From, to be read
    // from bb_config (the email there.)
    var $From     = "bblog@bblog.com";
    var $FromName = "The bBlog Team";
    var $Host     = "localhost;xushi.co.uk"; //currently mine.. change to bblog's or alternative later.
    var $Mailer   = "smtp";                         // Alternative to IsSMTP()
    var $WordWrap = 75;

    
    // Replace the default error_handler
    function error_handler($msg) {
        print("bBlog Mailer Error");
        print("Description: ... err, something wrong happened...");
        printf("%s", $msg);
        exit;
    }
}
?>