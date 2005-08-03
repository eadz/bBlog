<?php
/**
 * function.getposts.php
 * <p>
 * @copyright Copyright (C) 2003  Eaden McKee <email@eadz.co.nz>
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 * @package bblog
 */  

function identify_function_getrecentposts () {
$help = '
<p>the {getrecentposts} function is used to retrieve recent posts. It takes the following parameters:<br />
<br />
assign: variable to assign data to<br />
archive: to get ascending sorted results<br />
num: for number of entries to return<br />
skip: number of entries to skip<br />
section: to request recent items in a section<br />
home=true : to only show posts that have not been hidden.<br />
sectionid: to request recent items in a section, by specifing the sectionid';

  return array (
    'name'           =>'getrecentposts',
    'type'             =>'function',
    'nicename'     =>'GetRecentPosts',
    'description'   =>'Retrieves recent blog posts',
    'authors'        =>'Reverend Jim <jim@revjim.net>',
    'licence'         =>'GPL',
    'help'   => $help
  );
}

function smarty_function_getrecentposts($params, &$smartyObj) {
  $bBlog = & $smartyObj->get_template_vars("bBlog_object");
	
  $ar = array();
  $opt = array();

	// If "assign" is not set... we'll establish a default.
	if($params['assign'] == '') {
		$params['assign'] = 'posts';
	}
	
	// If "archive" is set, order them ASCENDING by posttime.
	if($params['archive']=='TRUE') {
		$opt['order']=" ORDER BY posttime ";
	}

	// If num is set, we'll only get that many results in return
	if(is_numeric($params['num'])) {
		$opt['num'] = $params['num'];
	}

	// If skip is set, we'll skip that many results
	if(is_numeric($params['skip'])) {
		$opt['skip'] = $params['skip'];
	}
     
	if ($params['section'] != '') {
		  $opt['sectionid'] = $bBlog->sect_by_name[$params['section']];
	}
	if ($params['sectionid'] != '') {
		  $opt['sectionid'] = $params['sectionid'];
	}
	
	if($bBlog->show_section) {
		$opt['sectionid'] = $bBlog->show_section;
	}

	$opt['home'] = $params['home'];

  $q = $bBlog->make_post_query($opt);

	if($params['search']){
		$ar['posts'] = $bBlog->get_posts('', false, $params['search']);
		$smartyObj->assign('search',$params['search']);
	} else {
		$ar['posts'] = $bBlog->get_posts($q);
	}
        
	// No posts.
  if(!is_array($ar['posts'])) {
	return '';
  }

  $lastmonth = 0;
  $lastdate = 0;

  foreach($ar['posts'] as $key => $value) {
		// It seems silly to do this. Especially since,
		// this kind of check can be done in Smarty template.
		// Additionally, since {newday} and {newmonth} require
		// the data to be in a variable named "post" it may not
		// function at all.
		//
		// We'll leave it here for now.
		
    /* check if new day  - used by block.newday.php */
    if(date('Ymd',$ar['posts'][$key]['posttime']) != $lastdate) {
      $ar['posts'][$key]['newday'] = TRUE;
    }
    $lastdate = date('Ymd',$ar['posts'][$key]['posttime']);

    /* check if new month - use by block.newmonth.php */
    if(date('Fy',$ar['posts'][$key]['posttime']) != $lastmonth) {
      $ar['posts'][$key]['newmonth'] = TRUE;
    }
    $lastmonth = date('Fy',$ar['posts'][$key]['posttime']);
  }

  $smartyObj->assign($params['assign'],$ar['posts']);

  return '';
	
}

?>
