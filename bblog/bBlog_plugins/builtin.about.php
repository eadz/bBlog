<?php
/**
 * builtin.about.php - shows the credits
 *
 * @package bBlog
 * @author Eaden McKee - <email@eadz.co.nz> - last modified by $LastChangedBy: $
 * @version $Id: $
 * @copyright The bBlog Project, http://www.bblog.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

function identify_admin_help () {
  return array (
    'name'           =>'about',
    'type'           =>'builtin',
    'nicename'       =>'About',
    'description'    =>'Displays bBlog infomation',
    'authors'         =>'Eaden McKee <email@eadz.co.nz>',
    'licence'         =>'GPL'
  );
}
include BBLOGROOT.'inc/credits.php';
$bBlog->smartyObj->assign('credits',$credits);
$bBlog->smartyObj->assign('title','About bBlog '.BBLOG_VERSION);

ob_start();
include BBLOGROOT.'docs/LICENCE.txt';
$bBlog->smartyObj->assign('licence',ob_get_contents());
ob_end_clean();

ob_start();
include BBLOGROOT.'make_bookmarklet.php';
$bBlog->smartyObj->assign('bookmarklet',ob_get_contents());
ob_end_clean();

$bBlog->display("about.html");
?>
