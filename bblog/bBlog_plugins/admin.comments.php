<?php
// admin.comments.php - administer comments
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

function identify_admin_comments () {
    return array (
    'name'           =>'comments',
    'type'             =>'admin',
    'nicename'     =>'Comments',
    'description'   =>'Remove, Approve or Edit comments',
    'authors'        =>'Eaden McKee <eadz@bblog.com>',
    'licence'         =>'GPL',
    'template' 	=> 'comments_admin.html',
    'help'    	=> ''
  );
}

function admin_plugin_comments_run(&$bBlog) {
// Again, the plugin API needs work.
if(isset($_GET['commentdo']))  { $commentdo = $_GET['commentdo']; }
elseif (isset($_POST['commentdo'])) { $commentdo = $_POST['commentdo']; }
else { $commentdo = ""; }

switch($commentdo) {
	case "Delete" : // delete comment
		if(is_numeric($_POST['commentid'])) {
			$postid = $bBlog->get_var("select postid from ".T_COMMENTS." where commentid='".$_POST['commentid']."'");
			$childcount = $bBlog->get_var("select count(*) as c from ".T_COMMENTS
						." where parentid='".$_POST['commentid']."' group by commentid");
			if($childcount > 0) { // there are replies to the comment so we can't delete it.
				$bBlog->query("update ".T_COMMENTS." set deleted='true', postername='', posteremail='', posterwebsite='', pubemail=0, pubwebsite=0, commenttext='Deleted Comment' where commentid='".$_POST['commentid']."'");
			} else { // just delete the comment
				$bBlog->query("delete from ".T_COMMENTS." where   commentid='".$_POST['commentid']."'");
			}
            		
			
			$newnumcomments = $bBlog->get_var("SELECT count(*) as c FROM ".T_COMMENTS." WHERE postid='$postid' and deleted='false' group by postid");
		        $bBlog->query("update ".T_POSTS." set commentcount='$newnumcomments' where postid='$postid'");
		        $bBlog->modifiednow();
		}
		break;

	case "Edit" :

		if(!(is_numeric($_POST['commentid']) && is_numeric($_POST['postid']))) break;

		$comment = $bBlog->get_comment($_POST['postid'],$_POST['commentid']);
		if(!$comment) break;

		$bBlog->smartyObj->assign('showeditform',TRUE);
		$bBlog->smartyObj->assign('comment',$comment[0]);

		break;

	case "editsave" :
		if(!(is_numeric($_POST['commentid']))) break;
		$title = my_addslashes($_POST['title']);
		$author = my_addslashes($_POST['author']);
		$email  = my_addslashes($_POST['email']);
		$websiteurl = my_addslashes($_POST['websiteurl']);
		$body = my_addslashes($_POST['body']);
		$q = "update ".T_COMMENTS." set title='$title', postername='$author', posterwebsite='$websiteurl', posteremail='$email', commenttext='$body' where commentid='{$_POST['commentid']}'";
		$bBlog->query($q);

		//print_r($_POST);
		break;
	case "Approve" :
		if(!is_numeric($_POST['commentid'])) break;
		$bBlog->query("update ".T_COMMENTS." set onhold='0' where commentid='".$_POST['commentid']."'");
		break;
	default : // show form
        	break;
	}
	
	if ((isset($_POST['post_comments'])) && (is_numeric($_POST['post_comments']))) {
	        $post_comments_q = "SELECT * FROM `".T_COMMENTS."` , `".T_POSTS."` WHERE `".T_POSTS."`.`postid`=`".T_COMMENTS."`.`postid` and deleted='false' and `".T_COMMENTS."`.`postid`='".$_POST['post_comments']."' order by `".T_COMMENTS."`.`posttime` desc";
	        $bBlog->smartyObj->assign('comments',$bBlog->get_results($post_comments_q));
		$bBlog->smartyObj->assign('message','Showing comments for PostID '.$_POST['post_comments'].'.<br /><a href="index.php?b=plugins&amp;p=comments">Click here to show 20 most recent comments</a>.');
		
	} else {
		
		$bBlog->smartyObj->assign('message','Showing 20 most recent comments across all posts. ');
		
	        $bBlog->smartyObj->assign('comments',$bBlog->get_results("SELECT * FROM `".T_COMMENTS."` , `".T_POSTS."` WHERE `".T_POSTS."`.`postid`=`".T_COMMENTS."`.`postid` and deleted='false' order by `".T_COMMENTS."`.`posttime` desc limit 0,20"));

	}
	$posts_with_comments_q = "SELECT ".T_POSTS.".postid, ".T_POSTS.".title, count(*) c FROM ".T_COMMENTS.",  ".T_POSTS." 	WHERE ".T_POSTS.".postid = ".T_COMMENTS.".postid GROUP BY ".T_POSTS.".postid ORDER BY ".T_POSTS.".posttime DESC  LIMIT 0 , 30 ";
	$posts_with_comments = $bBlog->get_results($posts_with_comments_q,ARRAY_A);
	$bBlog->smartyObj->assign("postselect",$posts_with_comments);
	
	
	
}

?>
