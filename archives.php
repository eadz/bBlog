<?php
/**
 * Archives - Archive all the posts, but call from template.
 *
 * @package bBlog
 * @author bBlog Weblog, http://www.bblog.com/ - last modified by $LastChangedBy: $
 * @version $Id: $
 * @copyright The bBlog Project, http://www.bblog.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

/**
 * @todo xushi: flyspray #55: make sure install/ is deleted
 */
if (file_exists("bblog/install/"))
{
	// TODO: Make sure to uncomment before new release.
    //die("Error: Make sure the folder bblog/install is deleted.");
}

include "bblog/config.php";
$bBlog->smartyObj->assign('year', $_GET['year']);
$bBlog->smartyObj->assign('month', sprintf("%02s", $_GET['month']));
$bBlog->display('archives.html');

?>
