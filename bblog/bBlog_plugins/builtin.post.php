<?php
/**
 * admin.post.php - Handles posting an entry
 * <p>
 * @copyright Copyright (C) 2003  Eaden McKee <email@eadz.co.nz>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 * @package bblog
 */

function identify_admin_post () {
    return array (
    'name'         =>'post',
    'type'         =>'builtin',
    'nicename'     =>'Post',
    'description'  =>'Post in your blog',
    'authors'      =>'Eaden McKee <eadz@bblog.com>',
    'licence'      =>'GPL',
    'help'         =>''
  );
}

$bBlog->smartyObj->assign('wysiwyg', C_WYSIWYG);
$bBlog->smartyObj->assign('fancy', C_FANCYURL);
$bBlog->smartyObj->assign('form_type','post'); // used in the template post_edit.html

// Setup default form values
$bBlog->smartyObj->assign('selected_modifier', C_DEFAULT_MODIFIER);
$bBlog->smartyObj->assign('commentsallowvalue', " checked='checked' ");
$bBlog->smartyObj->assign('statusdraft', C_DEFAULT_STATUS == 'draft' ? 'checked="checked"' : '');
$bBlog->smartyObj->assign('statuslive',  C_DEFAULT_STATUS != 'draft' ? 'checked="checked"' : '');

// Determine the action being attempted
if ((isset($_POST['dopreview'])) && ($_POST['dopreview'] == 'true')) {
    // Preview what we've written so far
    $post = prep_new_post();

    // Include the modifier and apply to message body
    require_once $bBlog->smartyObj->_get_plugin_filepath('modifier', $post->modifier);
    $prepped = $bBlog->prep_post($post);

    $bBlog->smartyObj->assign('preview',               'true');
    $bBlog->smartyObj->assign('preview_text',          stripslashes($prepped['body']));

    // Regurgitate user-supplied form values
    $bBlog->smartyObj->assign('title_text',            stripslashes($post->title));
    $bBlog->smartyObj->assign('body_text',             stripslashes($post->body));
    $bBlog->smartyObj->assign('selected_modifier',     stripslashes($post->modifier));
    $bBlog->smartyObj->assign('statusdraft',           $post->status == 'draft' ? 'checked="checked"' : '');
    $bBlog->smartyObj->assign('statuslive',            $post->status != 'draft' ? 'checked="checked"' : '');
    $bBlog->smartyObj->assign('hidefromhomevalue',     $post->hidefromhome == 'hide' ? 'checked="checked"' : '');
    $bBlog->smartyObj->assign('commentsallowvalue',    $post->allowcomments == 'allow'    ? 'checked="checked"' : '');
    $bBlog->smartyObj->assign('commentstimedvalue',    $post->allowcomments == 'timed'    ? 'checked="checked"' : '');
    $bBlog->smartyObj->assign('commentsdisallowvalue', $post->allowcomments == 'disallow' ? 'checked="checked"' : '');

    // update the sections to include a checked flag
    $sections = array();
    foreach ($bBlog->smartyObj->_tpl_vars['sections'] as $section)
    {
        $test = false;
        foreach ($post->sections as $selected)
        {
            $test = ($section->sectionid == $selected) ? true : $test;
        }
        $section->checked = $test;
        $sections[] = $section;
    }
    $bBlog->smartyObj->clear_assign('sections');
    $bBlog->smartyObj->assign('sections', $sections);
}
elseif ((isset($_POST['newpost'])) && ($_POST['newpost'] == 'true'))
{
    // we have a poster
      //clear the cache out as the content has changed
      $bBlog->smartyObj->clear_all_cache();
      // make the data sql save
      $post = prep_new_post();
      $res = $bBlog->new_post($post);
      if(is_numeric($res)) {
             $bBlog->smartyObj->assign('post_message',"Post #$res Added :)");

         if(strlen(C_PING)>0) {
             include BBLOGROOT.'libs/rpc.php'; // include stuff needed to ping
             register_shutdown_function('ping'); // who wants to wait for 4
                // requests before the page loads ?
         }

         if ((isset($_POST['send_trackback'])) && ($_POST['send_trackback'] == "TRUE")) {
            // send a trackback
        include "./trackback.php";
        send_trackback($bBlog->_get_entry_permalink($res), $_POST['title_text'], $_POST['excerpt'], $_POST['tburl']);
         }

        include "./inc/photobblog.php";
        if($_POST['image'] == "upload")
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
                $finalname=$res;
                imageHandler($file, $finalname.$extension, $savedir, $maxwidth, $maxheight, $quality, $extension);
                imageHandler($file, "thumb_".$finalname.$extension, $savedir, $thumbwidth, $thumbheight, $quality, $extension);
                photobblog_post_photo($bBlog, $res, $finalname.$extension, $_POST['caption']);
             }
        elseif($_POST['image']=="server")
             {
                $imageLoc=$_POST['serverimage'];
                photobblog_post_photo($bBlog, $res, $imageLoc, $_POST['caption']);
             }
      } else $bBlog->smartyObj->assign('post_message',"Sorry, error adding post: ".mysql_error());

}

// get modifiers
$bBlog->get_modifiers();

if ((isset($_REQUEST['popup']) && ($_REQUEST['popup'] == 'true'))) {
    include 'inc/bookmarkletstuff.php';
    $bBlog->display('popuppost.html');
} else {
    $bBlog->display('post.html');
}

////
// !makes sure post data is sql safe
// and in a nice format
function prep_new_post () {
    $post->title    = my_addslashes($_POST['title_text']);
    $post->body     = my_addslashes($_POST['body_text']);
    $post->fancyurl = my_addslashes($_POST['fancyurl']);

    // there has to be abetter way that this but i'm tired.
    if(!isset($_POST['modifier'])) $post->modifier = C_DEFAULT_MODIFIER;
    else $post->modifier = my_addslashes($_POST['modifier']);

    if(!isset($_POST['pubstatus'])) $post->status = C_DEFAULT_STATUS;
    else $post->status = my_addslashes($_POST['pubstatus']);

    if (isset($_POST['sections']))
    {
        $_tmp_sections = (array) $_POST['sections'];
    }
    else
    {
        $_tmp_sections = null;
    }
    $post->pagename = my_addslashes($_POST['pagename']);
    $post->sections = array();
    $post->providing_sections = TRUE; // this is so that bBlog knows to delete sections if there are none.

    if (!is_null($_tmp_sections))
    {
        foreach ($_tmp_sections as $_tmp_section)
        {
            if(is_numeric($_tmp_section))
            {
                $post->sections[] = $_tmp_section;
            }
        }
    }

    if ((isset($_POST['hidefromhome'])) && ($_POST['hidefromhome'] == 'hide')) { $hidefromhome='hide'; }
    else { $hidefromhome='donthide'; }

    $post->hidefromhome = $hidefromhome;
    $post->allowcomments = $_POST['commentoptions'];

    if (isset($_POST['disallowcommentsdays'])) { $disdays = (int) $_POST['disallowcommentsdays']; } else { $disdays = 0; }

    $time = (int) time();
    $autodisabledate = $time + $disdays * 3600 * 24;

    $post->autodisabledate = $autodisabledate;

    return $post;
}
?>
