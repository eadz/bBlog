<?php
/**
 * bblogMailer.class.php - extends the PHPMailer class
 *
 * Here's a class that extends the PHPMailer class
 * and sets the defaults for our bBlog site.
 * This saves us the trouble of hacking the original phpMaier,
 * to ease upgrading and make our life easier :)
 *
 * @package bBlog
 * @author Xushi - <xushi.xushi@gmail.com> - last modified by $LastChangedBy: $
 * @version $Id: $
 * @copyright The bBlog Project, http://www.bblog.com/
 * @source phpmailer - <http://phpmailer.sourceforge.net/>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

require("class.phpmailer.php");

class bblogMailer extends PHPMailer {

    // Set default variables for all new objects
    var $From = C_MAILFROM;
    var $FromName = C_MAILNAME;
    var $Host = C_HOST;
    var $Mailer = C_MAILER;
    var $WordWrap = 75;
    var $Reciever = ".";

     /**
     * Replace the default error_handler
     * @param string $msg
     * @return void
     */
    function error_handler($msg) {
        print("bBlog Mailer Error");
        print("Description: ... err, something wrong happened...");
        printf("%s", $msg);
        exit;
    }

}
?>
