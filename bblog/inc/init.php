<?php
// init.php - Start the bBlog engine, include needed files
// init.php - author: Eaden McKee <email@eadz.co.nz>
// $Id: init.php,v 1.34 2005/07/16 15:08:11 xushi Exp $
/*
** bBlog Weblog http://www.bblog.com/
** Copyright (C) 2003  Eaden McKee <email@eadz.co.nz>
**
** This program is free software; you can redistribute it and/or modify
** it under the terms of the GNU General Public License as published by
** the Free Software Foundation; either version 2 of the License, or
** (at your option) any later version.
**
** This program is distributed in the hope that it will be useful,
** but WITHOUT ANY WARRANTY; without even the implied warranty of
** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
** GNU General Public License for more details.
**
** You should have received a copy of the GNU General Public License
** along with this program; if not, write to the Free Software
** Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/
if ( ! is_dir(BBLOGROOT) ) {
 
  // throw meaningful error here ( OK tim ! )
  echo "There was an error : BBLOGROOT is not a directory. Please check that you have configured bBlog correctly by checking values in config.php";
  die();
   
}

// character set
header('Content-type: text/html; charset=utf-8');

// define CVS/release date - this is updated automatically by CVS
$release_date_string = '$Id: init.php,v 1.34 2005/07/16 15:08:11 xushi Exp $';
@$release_date_ar = explode(' ',$cvs_date);
@$release_date = $release_date_ar[3];
    
// define the table names
define('T_CONFIG',TBL_PREFIX.'config');
define('T_POSTS',TBL_PREFIX.'posts');
define('T_SECTIONS',TBL_PREFIX.'sections');
define('T_MODIFIERS',TBL_PREFIX.'modifiers');
define('T_PLUGINS',TBL_PREFIX.'plugins');
define('T_COMMENTS',TBL_PREFIX.'comments');
define('T_AUTHORS',TBL_PREFIX.'authors');
define('T_LINKS',TBL_PREFIX.'links');
define('T_CATEGORIES',TBL_PREFIX.'categories');
define('T_RSS',TBL_PREFIX.'rss');
define('T_CHECKCODE',TBL_PREFIX.'checkcode');
define('T_SEARCH',TBL_PREFIX.'search');
define('T_SEARCH_TMP',TBL_PREFIX.'search_tmp');
define('T_EXT_CONTENT',TBL_PREFIX.'external_content');
define('T_PHOTOBLOG',TBL_PREFIX.'photoblog');

// legacy
define('C_BLOGURL',BLOGURL);
define('IMAGESUPLOADROOT', BBLOGROOT . 'pbimages' . DIRECTORY_SEPARATOR);
define('IMAGESUPLOADURL', BBLOGURL . 'pbimages/');
define('UPLOADFILES', BBLOGROOT . 'files/');
define('UPLOADFILESURL', BBLOGURL . 'files/');
define('C_FANCY_URL_POST', BLOGURL . '%pagename%');

// prevent errors when _open_basedir is set
ini_set('include_path','./:../');

// Smarty inclusion is fixed so it will work in environments that already has set up Smarty
if (!defined('SMARTY_DIR')) {
    define('SMARTY_DIR', BBLOGROOT.'libs/');
}
include_once SMARTY_DIR.'Smarty.class.php';

// include  needed files
include BBLOGROOT.'libs/ez_sql.php';
include BBLOGROOT.'libs/authimage.class.php';
include BBLOGROOT.'libs/czech.class.php';
include BBLOGROOT.'libs/php5_emulator.php';
include BBLOGROOT.'inc/bBlog.class.php';
include BBLOGROOT.'inc/functions.php';
include BBLOGROOT.'inc/templates.php';

// start your engines
$smartyObj = new Smarty();
$bBlog = new bBlog($smartyObj);

// include after database connection established
include BBLOGROOT.'libs/search.class.php';
$bBlog->search = new article_search();

// Store the bBlog object in the Smarty one. So we can easily get it in Smarty functions.
$smartyObj->assign_by_ref("bBlog_object", $bBlog);

$mtime = explode(" ", microtime());
$bBlog->begintime = $mtime[1] + $mtime[0];

// this is only here until I work out the best way to do theming.
//$smartyObj->clear_compiled_tpl();

$smartyObj->template_dir = BBLOGROOT.'templates/'.C_TEMPLATE;
$smartyObj->compile_dir = BBLOGROOT.'compiled_templates/';

// A couple of changes to make sure we don't overwrite a smarty object initialized somewhere else
// Just to make integration a bit easier - Tiran Kenja
if (!is_array($smartyObj->plugins_dir)) {
  $smartyObj->plugins_dir = array ();
}
array_push($smartyObj->plugins_dir, BBLOGROOT.'bBlog_plugins', BBLOGROOT.'smarty_plugins');

if (defined('IN_BBLOG_ADMIN')) {
    $smartyObj->compile_id = 'admin';
} else {
    $smartyObj->compile_id = C_TEMPLATE;
}

$smartyObj->use_sub_dirs = FALSE; // change to true if you have a lot of templates

define('BBLOG_VERSION',"CVS $release_date");
$bBlog->smartyObj->assign("bBlog_version",BBLOG_VERSION);

// if you want debugging, this is the place
// you'd turn on debugging by adding ?gdb=true to the end of a url
// it's disabled by default for security reasons
// if($_GET['gdb']) $bBlog->debugging=TRUE;

// if you want to use php in your templates
// $bBlog->php_handling=SMARTY_PHP_ALLOW;

?>
