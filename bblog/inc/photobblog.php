<?php
/**
 * photobblog.php - PhotobBlog functions
 *
 * @package bBlog
 * @author Eaden McKee - <email@eadz.co.nz> - last modified by $LastChangedBy: $
 * @version $Id: $
 * @copyright The bBlog Project, http://www.bblog.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

require_once(dirname(__FILE__).'/../libs/image.php');

function photobblog_post_photo(&$bBlog, $postid, $imageLoc, $caption) {
// This function posts a new photo to the database.

$bBlog->query("insert into ".TBL_PREFIX."photobblog set postid=$postid, imageLoc='$imageLoc',
			   caption='".my_addslashes($caption)."'");

}

function imageHandler($sourcefile, $savename, $savedir, $widthmax, $heightmax, $quality, $extension)
{
	$destfile = $savedir."/".$savename;
	if(file_exists($destfile)){unlink($destfile);}
	if(file_exists($sourcefile)){
		preg_match('/\.(jpg|png|gif)$/', $sourcefile, $ext);
		$image = new img($sourcefile, $ext[1]);
		//echo $widthmax.'/'.$heightmax;exit();
		$image->resize($widthmax, $heightmax, true);
		$image->store($destfile);
		chmod($destfile, 0777);
	}
}

function photobblog_delete(&$bBlog, $postid)
{
	$bBlog->query("delete from ".TBL_PREFIX."photobblog where postid=".$postid);
}

function photobblog_update(&$bBlog, $postid, $imageLoc, $caption)
{
	$bBlog->query("update ".TBL_PREFIX."photobblog set imageLoc='".$imageLoc."' , caption='".my_addslashes($caption)."' where postid=".$postid);
}
?>