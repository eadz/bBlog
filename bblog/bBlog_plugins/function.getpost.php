<?php
/**
 * function.getposts.php - retrieve a single post.
 * <p>
 * @author Reverend Jim <jim@revjim.net>
 * @copyright Copyright (C) 2003  Eaden McKee <email@eadz.co.nz>
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 * @package bblog
 */ 

function identify_function_getpost () {
$help = '
<p>The {getpost} function is used to retrieve a single post. It takes the following parameters:<br />
<br />
assign: variable to assign data to<br />
postid: to request a SINGLE post';

  return array (
    'name'           =>'getpost',
    'type'             =>'function',
    'nicename'     =>'GetPost',
    'description'   =>'Gets a single blog post',
    'authors'        =>'Reverend Jim <jim@revjim.net>',
    'licence'         =>'GPL',
    'help'   => $help
  );
}
function smarty_function_getpost($params, &$smartyObj) {
  $bBlog = & $smartyObj->get_template_vars("bBlog_object");

  $ar = array();

	// If "assign" is not set... we'll establish a default.
	if($params['assign'] == '') {
		$params['assign'] = 'post';
	}
	if($params['postid'] == '') {
		$smartyObj->trigger_error('postid is a required parameter');
		return '';
	}

	$q = $bBlog->make_post_query(array("postid"=>$params['postid']));

	$ar['posts'] = $bBlog->get_posts($q);
        
	// No posts.
  if(!is_array($ar['posts'])) {
		return false;
	}

	$ar['posts'][0]['newday'] = 'yes';
	$ar['posts'][0]['newmonth'] = 'yes';

	$smartyObj->assign($params['assign'],$ar['posts'][0]);

	return '';
        
}

?>
