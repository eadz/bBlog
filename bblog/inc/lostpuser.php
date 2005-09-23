<?php
/**
 * lostpuser.php - Password retrieval: Stage 1
 *
 * Will ask for the username in which to get
 * the secret question for.
 *
 * @package bBlog
 * @author xushi - <xushi.xushi@gmail.com> - last modified by $LastChangedBy: $
 * @version $Id: $
 * @copyright The bBlog Project, http://www.bblog.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */
?>
<html>
    <header>
        <link rel="stylesheet" type="text/css" title="Main" href="../style/admin.css" media="screen" />
    </header>

    <body>
        <div id="header">
            <h1>bBlog</h1>
            <h2>Password Recovery</h2>
        </div>


        <div style="width: 500px; margin-left: auto; margin-right: auto; margin-top: 80px;">
        <form action="lostpquestion.php" method="post">
            <table border="0" class='list' cellpadding="4" cellspacing="0">
                <tr bgcolor="#FFFFF">
                    <td width="33%">Username:</td>
                    <td width="200"><input type="text" name="username" value="" /></td>
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
