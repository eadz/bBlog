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
function identify_modifier_simple () {
    return array (
    'name'           =>'simple',
    'type'             =>'modifier',
    'nicename'     =>'Newlines and URLS',
    'description'   =>'Converts breaks to newlines and URLs into clickable links',
    'authors'        =>'Eaden McKee, phpBB Authors',
    'licence'         =>'GPL',
    'help'    	=> 'This is a simple modifier that simply converts new lines ( returns ) into html breaks, any urls ( e.g. http://www.bblog.com/ or www.bblog.com) into clickable links.'
  );
}
////
// !a simple modifier combining nl2br and make clickable
function smarty_modifier_simple ($body) {
    //Replaced all code with methods in StringHandler class
    
    $parts = explode(" ", $body);
    foreach($parts as $ind=>$line){
        $parts[$ind] = StringHandling::transformLinks($line);
    }
    $body = join(" ", $parts);
    return nl2br($body);
}


?>
