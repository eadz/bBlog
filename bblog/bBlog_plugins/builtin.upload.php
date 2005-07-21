<?php
// admin.post.php - Handles posting an entry
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

function identify_admin_upload () {
    return array (
    'name'           =>'upload',
    'type'             =>'builtin',
    'nicename'     =>'Image Upload',
    'description'   =>'Upload images',
    'authors'        =>'Martin Konicek <markon@air4web.com>',
    'licence'         =>'GPL',
    'help'            =>''
  );
}

if(!empty($_FILES)){
	$ext = preg_match('/(\.[a-z]+)$/i', $_FILES['userfile']['name'], $matches);
	$filename = md5(microtime().rand()).$matches[1];
	$uploadfile = IMAGESUPLOADROOT . $filename;
	move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile);
	$bBlog->smartyObj->assign('uploaded', '<img src=\\"'.IMAGESUPLOADURL.$filename.'\\" alt=\\"'.basename($_FILES['userfile']['name']).' ['.filesize($uploadfile).' bytes]'.'\\" />');
}
$bBlog->display('upload.html');
?>
