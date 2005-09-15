<?php
/**
 * builtin.options.php - the option panel, allows you to change options
 *
 * @package bBlog
 * @author Eaden McKee - <email@eadz.co.nz> - last modified by $LastChangedBy: $
 * @version $Id: $
 * @copyright The bBlog Project, http://www.bblog.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */



function identify_admin_options () {
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

if ((isset($_POST['submit'])) && ($_POST['submit'] == 'Save Options')) { // saving options..
    bBlogConfig::saveConfiguration($bBlog->db);
    $bBlog->smartyObj->assign("showmessage",TRUE);
    $bBlog->smartyObj->assign("showoptions",'no');
    $bBlog->smartyObj->assign("message_title","Options Updated");
    $bBlog->smartyObj->assign("message_content","Your changes have been saved.<br><a href='index.php?b=options&r=".rand(20,214142124)."'>Click here to continue</a>");
} else {
    $optionrows = bBlogConfig::showConfigForm($bBlog->db);
    $bBlog->smartyObj->assign("optionrows",$optionrows);
} // end of else
$bBlog->display("options.html");
?>

