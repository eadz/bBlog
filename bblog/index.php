<?php

/**
 * index.php - bBlog admin interface
 *
 * @package bBlog
 * @author xushi - <xushi.xushi@gmail.com> - last modified by $LastChangedBy: $
 * @version $Id: $
 * @copyright The bBlog Project, http://www.bblog.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 * @todo xushi: flyspray #55: make sure install/ is deleted
 */

if (file_exists("install/"))
{
    //die("Error: Make sure the folder bblog/install is deleted.");
}

$loggedin = FALSE;

/**
* need description of this constant
*
* @name IN_BBLOG_ADMIN
*/
define('IN_BBLOG_ADMIN',TRUE);

// include the config and main code
if(file_exists('config.php') && (filesize('config.php') > 0))
{
    include "config.php";
}
else
{
    header('Location: install/index.php');
    header('Pragma: no-cache'); // Opera
}

include BBLOGROOT.'inc/taglines.php';

// default title
$title = 'Admin';

// make sure the page is never cached - we should probally set no-cache headers also.
$bBlog->setmodifytime(time());

$smartyObj->assign_by_ref('title', $title);

// we will store the rss templates in the inc/admin_templates dir, becasue almost noone will need to change them, - reduce clutter in the templates/* directory.
$smartyObj->template_dir = BBLOGROOT . 'inc/admin_templates';
$smartyObj->compile_id = 'admin';

// check to see if we're not logged in
if(!$bBlog->admin_logged_in())
{
    if(isset($_POST['username']) && isset($_POST['password']))
    {
        // we're trying to log in.
        $loggedin = $bBlog->userauth($_POST['username'], $_POST['password'], TRUE);
    }
}
else
{
    // we're already logged in.
    $loggedin = TRUE;
}

if((isset($_POST['submit'])) && ($_POST['submit'] == 'Login'))
{
    $bBlog->smartyObj->assign('tried2login', TRUE);
}

if(!$loggedin)
{
    // we are not logged in! Display the login page
    $menu[0]['url'] = 'index.php';
    $menu[0]['name'] = 'Login';
    $menu[0]['active'] = TRUE;
    $smartyObj->assign_by_ref('menu', $menu);
    $title = 'Login';
	
	if(!empty($_SERVER['QUERY_STRING']))  
    {
        // tried to go somewhere but was presumably kicked out as session timed out.
        // so when they login we'll redirect them.
        $bBlog->smartyObj->assign('redirect', base64_encode($_SERVER['REQUEST_URI']));
    }
    $bBlog->display("login.html");
    exit;
}

// seems this could be a reason for the blank page problem
// I think the problem was that redirect was always set after login. Even when the redirect url was ""
if (isset($_REQUEST['redirect']) && (strlen($_REQUEST['redirect']) > 0))
{
    header('Location: ' . base64_decode($_REQUEST['redirect']));
    exit;
}

// we're logged in, Hoorah!
// set up the menu

// @todo xushi: restrict certain parts if user isn't an admin. Probably with a 'if( isadmin = true)

$menu[0]['name'] = 'Post';
$menu[0]['url'] = 'index.php?b=post';
$menu[0]['title'] = 'Post a blog entry';
$bindex['post'] = 0;

$menu[1]['name'] = 'Archives';
$menu[1]['url'] = 'index.php?b=archives';
$menu[1]['title'] = 'Edit past entries and change properties';
$bindex['archives'] = 1;

$plugins = $bBlog->get_results("SELECT * FROM " . T_PLUGINS . " WHERE type='admin' ORDER BY ordervalue");
$i = 2;
if ($plugins)
{
    foreach($plugins as $plugin)
    {
        $menu[$i]['name'] = $plugin->nicename;
        $menu[$i]['url']  = 'index.php?b=plugins&amp;p='.$plugin->name;
        $menu[$i]['title'] = $plugin->description;
        $pindex[$plugin->name] = $i;
        $i++;
    }
}

$menu[$i]['name'] = 'Plugins';
$menu[$i]['url']  = 'index.php?b=plugins';
$menu[$i]['title'] = 'View information about plugins, and scan for new ones.';
$bindex['plugins'] = $i;

$menu[$i+1]['name'] = 'Options';
$menu[$i+1]['url']  = 'index.php?b=options';
$menu[$i+1]['title'] = 'Edit imporntant bBlog options';
$bindex['options'] = $i + 1;

$menu[$i+2]['name'] = 'Diagnostic';
$menu[$i+2]['url'] = 'index.php?b=diagnostic';
$menu[$i+2]['title'] = 'bBlog diagnostic tools';
$bindex['diagnostic'] = $i + 2;

$menu[$i+3]['name'] = 'About';
$menu[$i+3]['url']  = 'index.php?b=about';
$menu[$i+3]['title'] = 'About bBlog';
$bindex['about'] = $i + 3;

$menu[$i+4]['name'] = 'Docs';
$menu[$i+4]['url'] = 'http://www.bblog.com/docs/" target="_blank'; // NASTY hack!
$menu[$i+4]['title'] = 'Link to the online documentation at bBlog.com';
$bindex['docs'] = $i + 4;

$menu[$i+5]['name'] = 'Bugs';
$menu[$i+5]['url'] = 'http://www.bblog.com/forum.php" target="_blank'; // NASTY hack! (again)
$menu[$i+5]['title'] = 'Link to the online bug tracker at bBlog.com';
$bindex['bugs'] = $i + 5;

$smartyObj->assign_by_ref('menu', $menu);

// Custom URLS? Needed for admin template
if(C_CUSTOMURLS == 'true') $bBlog->smartyObj->assign('custom_urls', TRUE);

if(isset($_REQUEST['p']))
{
    $menu[$pindex[$_REQUEST['p']]]['active'] = TRUE; // now that's an array
}
else
{
    // @todo Need's a fix here, in the case $_REQUEST['b'] doesn't exists.
    // @ is shut-up mode
    @$m = $bindex[$_REQUEST['b']];

    if($m < 1)
    {
        // prevent against null values
        $m = 0;
    }

    @$menu[$m]['active'] = TRUE;
}

if(isset($_GET['b']))
{
    $b = $_GET['b'];
}
else
{
    $b = 'post';
}

if(isset($_POST['b']))
{
    $b = $_POST['b'];
}

if($b == 'login')
{
    // the default action when just logged in
    $b = 'post';
}

switch ($b)
{
    case 'post' :
         $title = 'Post Entry';
         include BBLOGROOT . 'bBlog_plugins/builtin.post.php';
         break;

    case 'archives' :
         $title = 'Archives';
         include BBLOGROOT . 'bBlog_plugins/builtin.archives.php';
         break;

    case 'options' :
         $title = 'Options';
         include BBLOGROOT . 'bBlog_plugins/builtin.options.php';
         break;

    case 'plugins' :
         if (!isset($_GET['p']))
         {
             $_GET['p']  = '';
         }
         if (!isset($_POST['p']))
         {
             $_POST['p'] = '';
         }
         $title = 'Plugins';
         include BBLOGROOT . 'bBlog_plugins/builtin.plugins.php';
         break;

    case 'help' :
         $title = 'Help';
         include BBLOGROOT . 'bBlog_plugins/builtin.help.php';
         break;

    case 'about' :
         $title = 'About bBlog ' . BBLOG_VERSION;
         include BBLOGROOT . 'bBlog_plugins/builtin.about.php';
         break;

    case 'diagnostic' :
         $title = 'Diagnostic';
         include BBLOGROOT . 'bBlog_plugins/builtin.diagnostic.php';
         break;

    case 'upload' :
         $title='Upload image';
         include BBLOGROOT.'bBlog_plugins/builtin.upload.php';
         break;

    case 'logout' :
         $bBlog->admin_logout();
         header('Location: index.php');
         break;

    default :
          $smartyObj->assign('errormsg', 'Unknown b value in admin index.php');
          $title = 'Error';
          $bBlog->display('error.html');
          break;
}

?>