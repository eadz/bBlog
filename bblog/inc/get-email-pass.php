<?php

/**
 * get-email-pass.php - checks password and sends email.
 *
 * Checks the secret answer, if all's good, sends email.
 *
 * @package bBlog
 * @author xushi - <xushi.xushi@gmail.com> - last modified by $LastChangedBy: $
 * @version $Id: $
 * @copyright The bBlog Project, http://www.bblog.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

require("bBlogMailer.class.php");
require("passwordReset.class.php");

// we could use $bBlog instead btw..
include '../config.php';
$mydb = new db(DB_USERNAME, DB_PASSWORD, DB_DATABASE, DB_HOST) or die("Error: config.php not found.");

// Instantiate your new class
$mail = new bblogMailer;
$passwd = new passwdManager;

/**
 * function to reset password
 *
 * compares the secret answer to the one in the db. If
 * the same, then it calls the mail and passwd classes
 * and sends an email to the user with the new password.
 *
 * @package bblog
 * @todo for security reasons, use global or instantiate within function?
 * @todo create dump or expand error message for failed mail send
 * @todo add the link to the users blog in the email message
 * @param void
 * @return void
 */
function getPass()
{
    global $mail;
    global $passwd;
    global $mydb;

    // collect output
    $output = '';

    // get the answer from db
    $cat = $mydb->get_var("select secret_answer from ".T_AUTHORS." where nickname='".$_SESSION['username']."'");
    $secAnswer = $_POST['pass'];

    // test if they're the same
    if($secAnswer == $cat)
    {
        // generate new password
        $p = $passwd->get_password($username);

        //encrypt and write the new passwd to the db.
        $passwd->set_password($passwd->encrypt_password($p));


        // Now, send an email to the user with the new (unhashed) password
        $mail->AddAddress($mydb->get_var("SELECT email from " . T_AUTHORS . " WHERE nickname='" . $_SESSION['username'] . "'"), $mydb->get_var("SELECT nickname from " . T_AUTHORS . " WHERE nickname='" . $_SESSION['username'] . "'"));
        $mail->Subject = "bBlog password recovery.";
        $mail->From = $mydb->get_var("SELECT value FROM " . T_CONFIG . " WHERE name='email'");
        $mail->Reciever = $mydb->get_var("SELECT fullname from " . T_AUTHORS . " WHERE nickname='" . $_SESSION['username']."'");
        $mail->Body = "Dear " . $mail->Reciever.",

Thank you for using bBlog.
You have requested for your password to be sent to you via email.
If you did not, then please reset your secret answer/question immediately.

Your username is : " . $_SESSION['username'] . "
Your new password is: $p

Click on the link below and make sure to change the password immediately.
TODO: link to his blog...

This is an automatic message. Please do not reply to it. For further enquiries, visit the forum at http://www.bblog.com/forum.php

Remember, the bBlog team will NEVER ask you for your password, so do NOT give it away to anyone.

Thank you,
The bBlog Team.";

        //$mail->AddAttachment("c:/temp/11-10-00.zip", "new_name.zip");  // optional

        // Send email, or display an error.
        if(!$mail->Send())
        {
            echo "There was an error sending the message";
            exit;
        }

        echo "Message was sent successfully.<br /><br /> You should recieve an email containing your new password shortly.";
    }
    else
    {
        echo "Error, invalid answer. Recovery aborted.";
        die();
    }
}

?>
<html>
<head>
<link rel="stylesheet" type="text/css" title="Main" href="../style/admin.css" media="screen" />
</head>
<body>

<div id="header">
    <h1>bBlog</h1>
    <h2>Password Recovery</h2>
</div>

<div style="width: 500px; margin-left: auto; margin-right: auto; margin-top: 80px;">
    <form action="index.php">
    <table border="0" class="list" cellpadding="4" cellspacing="0">
        <tr bgcolor="#ffffff">
            <td><?php echo "Connecting to DB to compare answers... ";?></td>
        </tr>
        <tr bgcolor="#ffffff">
            <td><?php getPass(); ?></td>
        </tr>
    </table>
    <p><input type="submit" name="submit" value="Return" /></p>
    </form>
</div>

<div id="footer">
    <a href="http://www.bBlog.com" target="_blank">bBlog 0.8</a> &copy; 2005 <a href="mailto:eaden@eadz.co.nz">Eaden McKee</a> &amp; <a href="index.php?b=about" target="_blank">Many Others</a>
</div>

</body>
</html>

