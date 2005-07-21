<?php
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

function identify_admin_diagnostic () {
  return array (
    'name'           =>'options',
    'type'           =>'builtin',
    'nicename'       =>'Options',
    'description'    =>'Allows you to change options',
    'authors'         =>'Eaden McKee',
    'licence'         =>'GPL',
    'help'            =>''
  );
}

if (!empty($_POST)){
	global $bBlog;
	$bBlog->search->index_all();
}

// test nice url support
$tester = @file_get_contents(BLOGURL . 'error.tester')=='ok' ? true : false;
$bBlog->smartyObj->assign('state_404', $tester);

// gd
$gdtester = function_exists('ImageCreateFromJPEG') ? true : false;
$bBlog->smartyObj->assign('state_gd', $gdtester);

// extensions
$bBlog->smartyObj->assign('extensions', get_loaded_extensions());

// search
$bBlog->smartyObj->assign('records_count',$bBlog->search->records_count());

// security
$security = file_exists(BBLOGROOT . 'install.php') ? false : true;
$security = file_exists(BBLOGROOT . 'install') ? false : $security;
$bBlog->smartyObj->assign('security', $security);

$bBlog->display("diagnostic.html");
?>
