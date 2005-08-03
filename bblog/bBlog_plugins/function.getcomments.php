<?php 
/**
 * block.comments.php - BBlog comments, Provides threaded comments
 * <p>
 * @copyright Copyright (C) 2003  Eaden McKee <email@eadz.co.nz>
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 * @package bblog
 */ 

function identify_function_getcomments() {
	$help = '
	<p>Provides threaded comments. See the default templates for usage examples.';

	return array ('name' => 'getcomments', 'type' => 'function', 'nicename' => 'Get Comments', 'description' => 'Gets Comments and trackbacks for a post and threads them', 'authors' => 'Eaden McKee <eaden@eadz.co.nz>', 'licence' => 'GPL', 'help' => $help);
}

function smarty_function_getcomments($params, &$smartyObj) {
	$bBlog = & $smartyObj->get_template_vars("bBlog_object");

	$assign = "comments";
	$postid = $bBlog->show_post;
	$replyto = $_REQUEST['replyto'];
	extract($params);

	// first, assign the hidden fields
	$commentformhiddenfields = '<input type="hidden" name="do" value="submitcomment" />';
	$commentformhiddenfields .= '<input type="hidden" name="comment_postid" value="'.$postid.'" />';

	if (is_numeric($replyto)) {
		$commentformhiddenfields .= '<a name="commentform"></a><input type="hidden" name="replytos" value="'.$replyto.'" />';
	}
	$rid = rand(3,100000);
	$commentformhiddenfields .= "<input type='hidden' name='rid' value='$rid' />";
	
	$smartyObj->assign('rid',$rid);

	$smartyObj->assign("commentformhiddenfields", $commentformhiddenfields);
	$smartyObj->assign("commentformaction", $bBlog->_get_entry_permalink($postid,false));
	// are we posting a comment ?
	if ($_POST['do'] == 'submitcomment' && is_numeric($_POST['comment_postid'])) { // we are indeed!
		if (is_numeric($_POST['replytos']))
			$rt = $_POST['replytos'];
		else
			$rt = false;

		$bBlog->new_comment($_POST['comment_postid'], $rt);
	}

	// get the comments.

	/* start loop and get posts*/
	$rt = false;
	if (is_numeric($_GET['replyto'])) {
		$rt = $_GET['replyto'];
		$cs = $bBlog->get_comment($postid, $rt);
	} else {
		$cs = $bBlog->get_comments($postid, FALSE);
	}

	/* assign loop variable */
	$smartyObj->assign($assign, $cs);
	
	/* load saved variables */
	$smartyObj->assign('postername', $_COOKIE['postername']);
	$smartyObj->assign('posteremail', $_COOKIE['posteremail']);
	$smartyObj->assign('posterwebsite', $_COOKIE['posterwebsite']);
	$smartyObj->assign('postercomment', $_SESSION['postercomment']);
	unset($_SESSION['postercomment']);
}

?>
