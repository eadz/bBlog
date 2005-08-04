<?php
/**
 * builtin.diagnostic.php - a diagnostic tool
 * <p>
 * @copyright Copyright (C) 2003  Eaden McKee <email@eadz.co.nz>
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 * @package bblog
 */
 
function identify_admin_diagnostic () {
  return array (
    'name'           =>'options',
    'type'           =>'builtin',
    'nicename'       =>'Options',
    'description'    =>'Allows you to change options',
    'authors'         =>'Eaden McKee',
    'licence'         =>'GPL',
    'help'            =>''
  );
}

if (!empty($_POST)){
	global $bBlog;
	$bBlog->search->index_all();
}

// test nice url support
$tester = @file_get_contents(BLOGURL . 'error.tester')=='ok' ? true : false;
$bBlog->smartyObj->assign('state_404', $tester);

// gd
$gdtester = function_exists('ImageCreateFromJPEG') ? true : false;
$bBlog->smartyObj->assign('state_gd', $gdtester);

// extensions
$bBlog->smartyObj->assign('extensions', get_loaded_extensions());

// search
$bBlog->smartyObj->assign('records_count',$bBlog->search->records_count());

// security
$security = file_exists(BBLOGROOT . 'install') ? false : $security;
$bBlog->smartyObj->assign('security', $security);

$bBlog->display("diagnostic.html");
?>
