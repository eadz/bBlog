<?php

/**
 * The root bBlog index file.
 *
 * This is where we all start. The index checks to see if you're on a
 * new install or not, it loads all the neccessary files, and calls the
 * desired template.
 *
 * @package bBlog
 * @author bBlog Weblog, http://www.bblog.com/ - last modified by $LastChangedBy: $
 * @version $Id: $
 * @copyright The bBlog Project, http://www.bblog.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

// start output buffering
ob_start();

/**
 * @todo xushi: flyspray #55: make sure install/ is deleted
 */
if (file_exists("bblog/install/")) {
    //die("Error: Make sure the folder bblog/install is deleted.");
}

if(file_exists('bblog/config.php') && (filesize('bblog/config.php') > 0))
{
    include "bblog/config.php";
}
else
{
    header('Location: bblog/install/');
    header('Pragma: no-cache'); // Opera
}

$q = "
    SELECT
        `postid`
    FROM
        `".T_POSTS."`
    WHERE
            (`fancyurl` = '".@$parsed."')
        AND
            (`fancyurl` != '')
";
$fancyid = 0;
$fancyid = $bBlog->get_var($q);

// performance
if(strstr(@$_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') && (strstr(@$_SERVER['HTTP_USER_AGENT'], 'Gecko') || strstr(@$_SERVER['USER-AGENT'], 'Opera')))
{
    $bBlog->smartyObj->load_filter('output', 'gzip');
    $bBlog->gzip = true;
}

// search
$bBlog->smartyObj->assign('search', @$_GET['search']);

if(is_numeric(@$_GET['postid']) || !empty($fancyid))
{
    $postid = empty($fancyid) ? $_GET['postid'] : $fancyid;
    if(@$_COOKIE['bBcomment'])
    {
        $cdata = unserialize(base64_decode($_COOKIE['bBcomment']));
        $bBlog->smartyObj->assign('cdata', $cdata);
    }
    $bBlog->smartyObj->assign('postid', $postid);
    $bBlog->show_post = $postid;
    $bBlog->display('post.html', true, true);
    die;
}

if(is_numeric(@$_GET['sectionid']))
{
    $bBlog->smartyObj->assign('sectionid', $_GET['sectionid']);
    $bBlog->smartyObj->assign('sectionname', @$bBlog->sect_by_name[$_GET['sectionid']]);
    $bBlog->smartyObj->assign('sectionnicename', $bBlog->sect_nicename[$_GET['sectionid']]);
    $bBlog->show_section = $_GET['sectionid'];
}
else
{
    define('ONHOME', TRUE);
}

$bBlog->display('index.html');

?>
