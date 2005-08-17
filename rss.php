<?php

/**
 * Need a description for this file
 *
 * @package bBlog
 * @author bBlog Weblog, http://www.bblog.com/ - last modified by $LastChangedBy: $
 * @version $Id: $
 * @copyright The bBlog Project, http://www.bblog.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

// start output buffering
ob_start();

if(!defined('CUSTOMRSS'))
{
    // so for example you could use this file but include it instead of calling it directly..
    include "bblog/config.php";
    $ver        = @$_GET['ver'];
    $num        = @$_GET['num'];
    $sectionid  = @$_GET['sectionid'];
    $section    = @$_GET['section'];
    $year       = @$_GET['year'];
    $month      = @$_GET['month'];
    $day        = @$_GET['day'];
}

$p = array();
if(is_numeric($num))
{
    $p['num'] = $num;
}
else
{
    $p['num'] = 10;
}

if(is_numeric($sectionid))
{
    $p['sectionid'] = $sectionid;
}

if(!empty($sectionname))
{
    $sid = $bBlog->sect_by_name[$sectionname];
    if(is_numeric($sid) && !empty($sid))
    {
        $p['sectionid'] = $sid;
    }
}

if(is_numeric($year))
{
    $p['year'] = $year;
    $p['month'] = $month;
    $p['day'] = $day;
}

$posts = $bBlog->get_posts($bBlog->make_post_query($p));
$bBlog->smartyObj->assign('posts', $posts);

$bBlog->smartyObj->template_dir = BBLOGROOT . 'inc/admin_templates';
$bBlog->smartyObj->compile_id = 'rss';

// Format last modification date for use in the header.
$hash = md5(serialize($posts));

// Set the Last-Modified and Etag headers.
header('Etag: '.$hash,true);
switch ($ver)
{
    case '2.0':
        header("Content-Type: application/rss+xml; charset=utf-8", true);
        $bBlog->display('rss20.html', false);
        break;
    case '1.0':
        header("Content-Type: application/rss+xml; charset=utf-8",true);
        $bBlog->display('rss10.html', false);
        break;
    case 'atom03':
        header('Content-type: application/atom+xml; charset=utf-8', true);
        $bBlog->display('atom.html', false);
        break;
    default:
        header("Content-Type: text/xml; charset=utf-8", true);
        $bBlog->display('rss092.html', false);
        break;
}

?>
