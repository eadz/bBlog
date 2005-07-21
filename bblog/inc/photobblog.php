<?php
//  photobblog.php - PhotobBlog functions
/*
** Copyright (C) 2003,2004  Mark Dobossy <mdobossy@princeton.edu>, Raefer Gabriel <blog@raefer.fastmail.fm>
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