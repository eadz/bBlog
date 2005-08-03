<?php
/**
 * builtin.help.php - Displays the help info to admins
 * <p>
 * @copyright Copyright (C) 2003  Eaden McKee <email@eadz.co.nz>
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 * @package bblog
 */
 
function identify_admin_help () {
  return array (
    'name'           =>'help',
    'type'           =>'admin',
    'nicename'       =>'Help',
    'description'    =>'Displays Help',
    'authors'         =>'Eaden McKee',
    'licence'         =>'GPL'
  );
}
if(is_numeric($_GET['pid']) or strlen($_GET['mod'])>0) {
        $bBlog->smartyObj->assign('pluginhelp',TRUE);

	if($_GET['mod']) $pluginrow = $bBlog->get_row("select * from ".T_PLUGINS." where name='".$_GET['mod']."' and type='modifier'");

	else	$pluginrow = $bBlog->get_row("select * from ".T_PLUGINS." where id='".$_GET['pid']."'");

	$bBlog->smartyObj->assign("title","Help: ".$pluginrow->type." : ".$pluginrow->nicename);
	$bBlog->smartyObj->assign("helptext",$pluginrow->help);
	$bBlog->smartyObj->assign("type",$pluginrow->type);
	$bBlog->smartyObj->assign("nicename",$pluginrow->nicename);
	$bBlog->smartyObj->assign("description",$pluginrow->description);
	$bBlog->smartyObj->assign("authors",$pluginrow->authors);
	$bBlog->smartyObj->assign("licence",$pluginrow->licence);

} elseif($_GET['modifierhelp']) {
        $bBlog->smartyObj->assign('title','Modifier Help');
	$bBlog->smartyObj->assign('inline',TRUE);
	$helptext = "<p>Modifiers are an easy way to enable you to make links and other web features without knowing html. There are a few to choose fshowcloserom, select one to get instructions.</p><ul class='form'>";
        $modifiers = $bBlog->get_results("select * from ".T_PLUGINS." where type='modifier' order by nicename");
	foreach($modifiers as $mod) {
                $helptext .= "<li><a href='index.php?b=help&amp;inline=true&amp;pid={$mod->id}'>{$mod->nicename}</a> - {$mod->description}</li>";
	}
	$helptext .="</ul>";
	$bBlog->smartyObj->assign('helptext',$helptext);
 } else {
	$bBlog->smartyObj->assign("title","Help");
	$bBlog->smartyObj->assign("helptext",'Visit the <a href="http://www.bblog.com/docs/" target="_blank">bBlog online documentation</a> or the <a href="http://www.bBlog.com/forum.php" target="_blank">bBlog forum</a> for help.');
}
$bBlog->display("help.html");
?>
