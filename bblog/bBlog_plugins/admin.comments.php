<?php
/**
 * admin.comments.php - administer comments
 *
 * @package bBlog
 * @author Eaden McKee - <email@eadz.co.nz> - last modified by $LastChangedBy: $
 * @version $Id: $
 * @copyright The bBlog Project, http://www.bblog.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

function identify_admin_comments () {
    return array (
    'name'           =>'comments',
    'type'             =>'admin',
    'nicename'     =>'Comments',
    'description'   =>'Remove, Approve or Edit comments',
    'authors'        =>'Eaden McKee <eadz@bblog.com>, Kenneth Power <kenneth.power@gmail.com>',
    'licence'         =>'GPL',
    'template' 	=> 'comments_admin.html',
    'help'    	=> ''
  );
}

/**
* Main function of plugin
*
* @param object $bBlog Instance of bBlog object
* @return void
*
*/
function admin_plugin_comments_run(&$bBlog) {
    // Again, the plugin API needs work.
    $commentAmount = 50;
    if(isset($_GET['commentdo'])){
        $commentdo = $_GET['commentdo'];
    }
    elseif (isset($_POST['commentdo'])){
        $commentdo = $_POST['commentdo'];
    }
    else{
        $commentdo = "";
    }

    switch($commentdo) {
        case "Delete" : // delete comments
            if(is_array($_POST['commentid'])){
              foreach($_POST['commentid'] as $key=>$val){
                deleteComment(&$bBlog, $val);
            }
          }
          break;
        case "Edit" :
            $commentid = intval($_GET['editComment']);
            $postid = intval($_GET['postid']);
            editComment(&$bBlog, $commentid, $postid);
            break;
        case "editsave" :
            saveEdit(&$bBlog);
            break;
        case "Approve":
            if(is_array($_POST['commentid'])){
            foreach($_POST['commentid'] as $key=>$val)
                $bBlog->query("UPDATE ".T_COMMENTS." SET onhold='0' WHERE commentid='".intval($val)."'");
            }
            break;
        case "25":
        case "50":
        case "100":
        case "150":
        case "200":
            $commentAmount = intval($commentdo);
            break;
        default : // show form
            break;
    }

    retrieveComments(&$bBlog, $commentAmount);
    populateSelectList(&$bBlog);

}

/**
* Delete a single comment
*
* Remove the comment specified, first checking whether child comments
* exist. If child comments exist mark the comment as deleted, which
* blocks it from displaying.
*
* @param object $bBlog Instance of the bBlog class
* @param integer $id ID of comment to delete
* @return void
*/
function deleteComment(&$bBlog, $id){
    $id = intval($id);
    $postid = $bBlog->get_var('select postid from '.T_COMMENTS.' where commentid="'.$id.'"');
    $childcount = $bBlog->get_var('select count(*) as c from '.T_COMMENTS .' where parentid="'.$id.'" group by commentid');
    if($childcount > 0) { // there are replies to the comment so we can't delete it.
        $bBlog->query('update '.T_COMMENTS.' set deleted="true", postername="", posteremail="", posterwebsite="", pubemail=0, pubwebsite=0, commenttext="Deleted Comment" where commentid="'.$id.'"');
    } else { // just delete the comment
        $bBlog->query('delete from '.T_COMMENTS.' where commentid="'.$id.'"');
    }
    $newnumcomments = $bBlog->get_var('SELECT count(*) as c FROM '.T_COMMENTS.' WHERE postid="'.$postid.'" and deleted="false" group by postid');
    $bBlog->query('update '.T_POSTS.' set commentcount="'.$newnumcomments.'" where postid="'.$postid.'"');
    $bBlog->modifiednow();
}

/**
* Retrieve comment details to allow editing
*
* @param object $bBlog Instance of bBlog class
* @param integer $commentid ID of comment to edit
* @param integer $postid ID of post to which comment is attached
*/
function editComment(&$bBlog, $commentid, $postid){
    $rval = true;
    if($commentid === 0 && $postid === 0)
        $rval = false;
    else{
        $comment = $bBlog->get_comment($postid,$commentid);
        if(!$comment)
            $rval = false;
        if($rval === true){
            $bBlog->assign('showeditform',TRUE);
            $bBlog->assign('comment',$comment[0]);
        }
    }
    return $rval;
}

/**
* Save the changes made to a comment
*
* @param object $bBlog Instance of bBlog class
*/
function saveEdit(&$bBlog){
    $rval = true;
    $cid = intval($_POST['commentid']);
    if($cid === 0)
        $rval = false;
    else{
        $title = my_addslashes($_POST['title']);
        $author = my_addslashes($_POST['author']);
        $email  = my_addslashes($_POST['email']);
        $websiteurl = my_addslashes($_POST['websiteurl']);
        $body = my_addslashes($_POST['body']);
        if($rval === true){
            $q = "update ".T_COMMENTS." set title='$title', postername='$author', posterwebsite='$websiteurl', posteremail='$email', commenttext='$body' where commentid='{$_POST['commentid']}'";
            if($bBlog->query($q) === true)
                $bBlog->assign('message', 'Comment <em>'.$title.'</em> saved');
        }
    }
    return $rval;
}

/**
* Retrieve a list of comments
*
* @param object $bBlog Instance of bBlog class
* @param integer $amount How many comments to retrieve
*/
function retrieveComments(&$bBlog, $amount){
    if ((isset($_POST['post_comments'])) && (is_numeric($_POST['post_comments']))) {
        $post_comments_q = "SELECT * FROM `".T_COMMENTS."` , `".T_POSTS."` WHERE `".T_POSTS."`.`postid`=`".T_COMMENTS."`.`postid` and deleted='false' and `".T_COMMENTS."`.`postid`='".$_POST['post_comments']."' order by `".T_COMMENTS."`.`posttime` desc";
        $bBlog->smartyObj->assign('comments',$bBlog->get_results($post_comments_q));
        $bBlog->smartyObj->assign('message','Showing comments for PostID '.$_POST['post_comments']);
    } else {
        $bBlog->smartyObj->assign('comments',$bBlog->get_results("SELECT * FROM `".T_COMMENTS."` , `".T_POSTS."` WHERE `".T_POSTS."`.`postid`=`".T_COMMENTS."`.`postid` and deleted='false' order by `".T_COMMENTS."`.`posttime` desc limit 0,".$amount));
        $bBlog->smartyObj->assign('commentAmount', $amount);
    }
}

/**
* Retrieve a list of posts that contain comments
*
* @param object $bBlog Instance of bBlog class
*/
function populateSelectList(&$bBlog){
    $posts_with_comments_q = "SELECT ".T_POSTS.".postid, ".T_POSTS.".title, count(*) c FROM ".T_COMMENTS.",  ".T_POSTS." 	WHERE ".T_POSTS.".postid = ".T_COMMENTS.".postid GROUP BY ".T_POSTS.".postid ORDER BY ".T_POSTS.".posttime DESC ";
    $posts_with_comments = $bBlog->get_results($posts_with_comments_q,ARRAY_A);
    $bBlog->smartyObj->assign("postselect",$posts_with_comments);
}
?>
