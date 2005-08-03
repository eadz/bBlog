<?php
/**
 * admin.rssfeedmaker.php - easily make custom rss feeds
 * <p>
 * @copyright Copyright (C) 2003  Eaden McKee <email@eadz.co.nz>
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 * @package bblog
 */
 
function identify_admin_rssfeedmaker () 
{
  return array (
    'name'           =>'rssfeedmaker',
    'type'             =>'admin',
    'nicename'     =>'RSS Linker',
    'description'   =>'Create custom RSS feeds',
    'template' 	=> 'rssfeedmaker.html',
    'authors'        =>'Eaden McKee <eadz@bblog.com>',
    'licence'         =>'GPL',
    'help'            => ''
  );
}

function admin_plugin_rssfeedmaker_run(&$bBlog) 
{
	if ((isset($_POST['sub'])) && ($_POST['sub'] == 'y')) 
	{
		$url = BLOGURL.'rss.php?';


		if($_POST['version'] == 2) $url .= 'ver=2';
		elseif($_POST['version'] == 'atom03') $url .= 'ver=atom03';
		else $url .= 'ver=0.92';

		if(is_numeric($_POST['num'])) $url .= '&amp;num='.$_POST['num'];

		if($_POST['sectionid']>0) $url .= '&amp;sectionid='.$_POST['sectionid'];

		if(is_numeric($_POST['year'])) $url .= '&amp;year='.$_POST['year'];
		if(is_numeric($_POST['month'])) $url .= '&amp;year='.$_POST['day'];
		if(is_numeric($_POST['day'])) $url .= '&amp;year='.$_POST['day'];

		$bBlog->smartyObj->assign('results',TRUE);
		$bBlog->smartyObj->assign('feedurl',$url);
	}
	
	$sections = $bBlog->get_sections();
	$sectionlist = '';
	
	foreach ($sections as $section) 
	{
		$sectionlist .= "<option value='{$section->sectionid}'>{$section->nicename}</option>";
	}
	
	$bBlog->smartyObj->assign('sectionlist',$sectionlist);
}
?>
