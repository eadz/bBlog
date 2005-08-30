<?php
session_start();
/**
 * lostp.php
 * <p>
 * Will retrieve the secret quesetion from the DB, and
 * secret answer from the user, and send it to getp.php
 * in order to retrieve the admin password.
 * <p>
 * @author Xushi <xushi.xushi@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

// the pot of gold..
include 'config.php';

// make sure the page is never cached -
// TODO: we should probally set no-cache headers also.
$bBlog->setmodifytime(time());


    // get question from DB
    $_SESSION['username'] = $_POST['username'];
    $_SESSION['authuser'] = 0;
    $_SESSION['userdb'] = $bBlog->get_results("SELECT email FROM `".T_AUTHORS."` WHERE nickname='".$_SESSION['username']."'");

    // see if user exists or not. If yes, get HIS sec question.
    if (isset($_SESSION['userdb'])) {
        $_SESSION['authuser'] = 1;
        //TODO: change to the specific user's sec question found in his table in T_AUTHORS
        $secQuestion = $bBlog->get_var("SELECT secret_question FROM ".T_AUTHORS." WHERE nickname='".$_SESSION['username']."'");
    }
    else {
        echo "Sorry. Please visit the bblog forum for more advise.";
        exit();
    }
?>
<html>
    <header>
        <link rel="stylesheet" type="text/css" title="Main" href="style/admin.css" media="screen" />
    </header>

    <body>
        <div id="header">
            <h1>bBlog</h1>
            <h2>Password Recovery</h2>
        </div>


        <div style="width: 500px; margin-left: auto; margin-right: auto; margin-top: 80px;">
        <form action="get-email-pass.php" method="post">
            <table border="0" class='list' cellpadding="4" cellspacing="0">
                <tr bgcolor="#FFFFF">
                    <td width="33%">Question:</td>
                    <td width="200"><?php echo $secQuestion; ?></td>
                </tr>
                <tr bgcolor="#FFFFF">
                    <td width="33%">Answer:</td>
                    <td width="200"><input type="password" name="pass" value="" /></td>
                </tr>
            </table>
            <p><input type="submit" name="submit" value="Submit" />
        </form>
        </div>

        <div id="footer">
            <a href="http://www.bBlog.com" target="_blank">
            bBlog 0.8</a> &copy; 2005 <a href="mailto:eaden@eadz.co.nz">Eaden McKee</a> &amp; <a href="index.php?b=about" target="_blank">Many Others</a>
        </div>
    </body>
</html>
