<?php

/**
 * search.php - The root file to search the blog. 
 *
 * @package bBlog
 * @author bBlog Weblog, http://www.bblog.com/ - last modified by $LastChangedBy: $
 * @version $Id: $
 * @copyright The bBlog Project, http://www.bblog.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */


include "bblog/config.php";
$bBlog->smartyObj->assign('string', $_GET['string']);
$encoded = urlencode($_GET['string']);
$bBlog->smartyObj->assign('encodedstring', $encoded);
$bBlog->smartyObj->display('search.html');
?>
