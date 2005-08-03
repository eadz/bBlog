<?php
/**
 * builtin.about.php - shows the credits
 * <p>
 * @copyright Copyright (C) 2003  Eaden McKee <email@eadz.co.nz>
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 * @package bblog
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
