<?php
/**
 * builtin.diagnostic.php - a diagnostic tool
 *
 * @package bBlog
 * @author Eaden McKee - <email@eadz.co.nz> - last modified by $LastChangedBy: $
 * @version $Id: $
 * @copyright The bBlog Project, http://www.bblog.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
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
$security = !file_exists(BBLOGROOT . 'install');
$bBlog->smartyObj->assign('security', $security);

$bBlog->display("diagnostic.html");
?>
