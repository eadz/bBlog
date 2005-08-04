<?php
/**
 * admin.archives.php - handles showing a list of entries to edit/delete
 * <p>
 * @copyright Copyright (C) 2003  Eaden McKee <email@eadz.co.nz>
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 * @package bblog
 */

// now it may be an idea to do a if(!defined('IN_BBLOG')) die "hacking attempt" type thing but
// i'm not sure it's needed, as without this file being included it hasn't connected to the
// database, and all the functions it calls are in the $bBlog object.
function identify_admin_archives ()
{
  return array (
    'name'           =>'archives',
    'type'           =>'builtin',
    'nicename'       =>'Archives Admin',
    'description'    =>'Edit archives',
    'authors'         =>'Eaden McKee, Tobias Schlottke',
    'licence'         =>'GPL'
  );
}

$bBlog->smartyObj->assign('form_type','edit');
$bBlog->get_modifiers();

if (isset($_GET['delete']) or isset($_POST['delete']))
    {
    //clear the cache out as the content has changed
    $bBlog->smartyObj->clear_all_cache();
    if ($_POST['confirm'] == "cd".$_POST['delete'] && is_numeric($_POST['delete']))
    {
        $res = $bBlog->delete_post($_POST['delete']);
        $bBlog->smartyObj->assign('showmessage',TRUE);
        $bBlog->smartyObj->assign('message_title','Message Deleted');
        $bBlog->smartyObj->assign('message_content','The message you selected has now been deleted'); // -1 Redundant  ;)
    }
    else
    {
        $bBlog->smartyObj->assign('showmessage',TRUE);
        $bBlog->smartyObj->assign('message_title','Are you sure you want to delete it?');
        $bBlog->smartyObj->assign('message_content',"
            <form action='index.php' method='POST'>
            <input type='hidden' name='b' value='archives'/>
            <input type='hidden' name='confirm' value='cd".$_POST['delete']."'/>
            <input type='hidden' name='delete' value='".$_POST['delete']."'/>
            <center><input type='submit' class='bf' name='submit' value='Delete it'/></center>
            </form>
        ");
    }
}

if (isset($_POST['edit']) && is_numeric($_POST['edit']))
{
    $epost = $bBlog->get_post($_POST['edit'],TRUE,TRUE);
    $bBlog->smartyObj->assign('title_text',htmlspecialchars($epost->title));
    $bBlog->smartyObj->assign('pagename',htmlspecialchars($epost->pagename));
    $bBlog->smartyObj->assign('body_text',htmlspecialchars($epost->body));
        $bBlog->smartyObj->assign('fancyurl',htmlspecialchars($epost->fancyurl));
    $bBlog->smartyObj->assign('selected_modifier',$epost->modifier);
    $bBlog->smartyObj->assign('editpost',TRUE);
    $bBlog->smartyObj->assign('showarchives','no');
    $bBlog->smartyObj->assign('postid',$_POST['edit']);
    $bBlog->smartyObj->assign('timestampform',timestampform($epost->posttime));
        $bBlog->smartyObj->assign('wysiwyg', C_WYSIWYG);
        $bBlog->smartyObj->assign('fancy', C_FANCYURL);

    // to hide a post from the homepage
    if($epost->hidefromhome == 1) $bBlog->smartyObj->assign('hidefromhomevalue'," checked='checked' ");

    // to disable comments either now or in the future
    if($epost->allowcomments == 'timed') $bBlog->smartyObj->assign('commentstimedvalue'," checked='checked' ");
    elseif($epost->allowcomments == 'disallow') $bBlog->smartyObj->assign('commentsdisallowvalue'," checked='checked' ");
    else $bBlog->smartyObj->assign('commentsallowvalue'," checked='checked' ");


    if($epost->status == 'draft') $bBlog->smartyObj->assign('statusdraft','checked="checked"');
    else $bBlog->smartyObj->assign('statuslive','checked="checked"');

    $_post_secs = explode(":",$epost->sections);

    if(is_array($_post_secs))
    {
        foreach($_post_secs as $_post_sec)
        {
            $editpostsections[$_post_sec] = TRUE;
        }
        $bBlog->smartyObj->assign('editpostsections',$editpostsections);
    }

    $sects = $bBlog->sections;
    $nsects = array();

    foreach($sects as $sect)
    {
       if(isset($editpostsections[$sect->sectionid])) $sect->checked = TRUE;
       $nsects[] = $sect;
    }

    $bBlog->smartyObj->assign("sections",$nsects);
    $bBlog->smartyObj->assign_by_ref("sections",$nsects);
}

if ((isset($_POST['postedit'])) && ($_POST['postedit'] == 'true'))
{
    //clear the cache out as the content has changed
    $bBlog->smartyObj->clear_all_cache();

    // a post to be edited has been submitted
    if ((isset($_POST['postedit'])) && (!is_numeric($_POST['postid'])))
    {
        echo "Provided PostID value is not a Post ID. (Fatal error)";
        die;
    }

    $newsections = '';

    if ((isset($_POST['sections'])) && (sizeof($_POST['sections']) > 0))
    {
        $newsections = implode(":",$_POST['sections']);
    }

    if ((isset($_POST['edit_timestamp'])) && ($_POST['edit_timestamp'] == 'TRUE'))
    {
        // the timestamp will be changed.
        if (!isset($_POST['ts_day']))       { $_POST['ts_day']      = 0;    }
        if (!isset($_POST['ts_month']))     { $_POST['ts_month']    = 0;    }
        if (!isset($_POST['ts_year']))      { $_POST['ts_year']     = 0;    }
        if (!isset($_POST['ts_hour']))      { $_POST['ts_hour']     = 0;    }
        if (!isset($_POST['ts_minute']))    { $_POST['ts_minute']   = 0;    }

        $timestamp = maketimestamp($_POST['ts_day'],$_POST['ts_month'],$_POST['ts_year'],$_POST['ts_hour'],$_POST['ts_minute']);
    }
    else
    {
        $timestamp = FALSE;
    }

    if($_POST['hidefromhome'] == 'hide') $hidefromhome='hide';
        else $hidefromhome='donthide';
    // there is a reason for not using booleans here.
    // is because the bBlog->edit_post function needs to know if to change it or not.

     $disdays = (int)$_POST['disallowcommentsdays'];
     $time = (int)time();
     $autodisabledate = $time + $disdays * 3600 * 24;


    $params = array(
        "postid"    => $_POST['postid'],
        "title"     => my_addslashes($_POST['title_text']),
        "body"      => my_addslashes($_POST['body_text']),
        "modifier"  => my_addslashes($_POST['modifier']),
        "status"    => my_addslashes($_POST['pubstatus']),
        "pagename"    => my_addslashes($_POST['pagename']),
        "edit_sections" => TRUE,
    "hidefromhome" => $hidefromhome,
    "allowcomments" => my_addslashes($_POST['commentoptions']),
    "autodisabledate" => $autodisabledate,
        "sections"  => $newsections,
        "timestamp" => $timestamp,
                "fancyurl" => $_POST['fancyurl']
    );

    $bBlog->edit_post($params);

    include "./inc/photobblog.php";
    if($_POST['image']=='none')
    {
        photobblog_delete($bBlog, $_POST['postid']);
    }
    elseif($_POST['image']=='server')
    {
        $result=$bBlog->get_var("select postid from ".TBL_PREFIX."photobblog where postid=".$_POST['postid']);
        if($result)
            photobblog_update($bBlog, $_POST['postid'], $_POST['serverimage'], $_POST['caption']);
        else
            photobblog_post_photo($bBlog, $_POST['postid'], $_POST['serverimage'], $_POST['caption']);
    }
    elseif($_POST['image']=='upload')
    {
        $savedir="pbimages";
        $maxwidth=640;
        $maxheight=480;
        $thumbwidth=128;
        $thumbheight=96;
        $quality=70;
        $file=$_FILES['uploadimage']['tmp_name'];
        $fname=$_FILES['uploadimage']['name'];
        //not going to go by MIME type - don't trust the browser
        $extension=strtolower(substr($fname, strlen($fname)-4, 4));
        if ($extension=='.gif') {
            $extension = '.gif';
        } else {
            $extension = '.jpg';
        }
        $finalname=$_POST['postid'];
        imageHandler($file, $finalname.$extension, $savedir, $maxwidth, $maxheight, $quality, $extension);
        imageHandler($file, "thumb_".$finalname.$extension, $savedir, $thumbwidth, $thumbheight, $quality, $extension);
        $result=$bBlog->get_var("select postid from ".TBL_PREFIX."photobblog where postid=".$_POST['postid']);
        if($result)
            photobblog_update($bBlog, $_POST['postid'], $_POST['postid'].$extension, $_POST['caption']);
        else
            photobblog_post_photo($bBlog, $_POST['postid'], $_POST['postid'].$extension, $_POST['caption']);
    }

    if ((isset($_POST['send_trackback'])) && ($_POST['send_trackback'] == "TRUE"))
    {
        // send a trackback
        include "./trackback.php";

        if (!isset($_POST['title_text']))   { $_POST['title_text']  = ""; }
        if (!isset($_POST['excerpt']))      { $_POST['excerpt']     = ""; }
        if (!isset($_POST['tburl']))        { $_POST['tburl']       = ""; }
        send_trackback($bBlog->_get_entry_permalink($_POST['postid']), $_POST['title_text'], $_POST['excerpt'], $_POST['tburl']);
    }
}

if ((isset($_POST['filter'])) && ($_POST['filter'] == 'true'))
{
    if ((isset($_POST['shownum'])) && (is_numeric($_POST['shownum'])))
    {
        $num = $_POST['shownum'];
    }
    else
    {
        $num=20;
    }

    $searchopts['num'] = $num;
    $searchopts['wherestart'] = ' WHERE 1 ';

    if(is_numeric($_POST['showsection']))
    {
        $searchopts['sectionid'] = $_POST['showsection'];
    }

    if($_POST['showmonth'] != 'any')
    {
        $searchopts['month'] = substr($_POST['showmonth'],0,2);
        $searchopts['year']  = substr($_POST['showmonth'],3,4);
    }
    //print_r($searchopts);
    $q = $bBlog->make_post_query($searchopts);
    //echo $q;
    $archives = $bBlog->get_posts($q);
}
else
{
    $searchopts['wherestart'] = ' WHERE 1 ';
    $q = $bBlog->make_post_query($searchopts);
    $archives = $bBlog->get_posts($q); // ,TRUE);
}

$bBlog->smartyObj->assign('postmonths',get_post_months());
$bBlog->smartyObj->assign_by_ref('archives',$archives);
$bBlog->display('archives.html');

function get_post_months()
{
    global $bBlog;
    $months_tmp = $bBlog->get_results("SELECT FROM_UNIXTIME(posttime,'%Y%m') yyyymm,  posttime from ".T_POSTS." group by yyyymm order by yyyymm");
    $months=array();
    foreach($months_tmp as $month)
    {
        $nmonth['desc'] = date('F Y',$month->posttime);
        $nmonth['numeric'] = date('m-Y',$month->posttime);
        $months[]  = $nmonth;
    }
    return $months;
}

function timestampform($ts)
{
    $day = date('d',$ts);
    $month = date('m',$ts);
    $year = date('Y',$ts);
    $hour = date('h',$ts);
    $minute = date('i',$ts);
    $o  = "<span class='ts'>Day</span> /
           <span class='ts'>Month</span> /
           <span class='ts'>Year</span> @
           <span class='ts'>24hours</span> :
           <span class='ts'>Minutes</span><br />
           <input type='text' name='ts_day' value='$day' class='ts' size='5'/> /
           <input type='text' name='ts_month' value='$month' class='ts' size='5'/> /
           <input type='text' name='ts_year' value='$year' class='ts' size='7'/> @
           <input type='text' name='ts_hour' value='$hour' class='ts' size='5'/> :
           <input type='text' name='ts_minute' value='$minute' class='ts' size='5'/>
           ";
    return $o;
}

function maketimestamp($day,$month,$year,$hour,$minute)
{
    return mktime($hour, $minute, 00, $month, $day, $year);
}

?>
