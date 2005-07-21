<?php

// admin.externalcontent.php - administer external content
//
// Written by Paul Balogh <javaducky@gmail.com>
//
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

function identify_admin_externalcontent () {

  $help = '<p>External Content is a way of including content segments from external sites. This plugin 
           allows you to add, edit and delete content providers.
           <p>The original concept was to enable the inclusion of a <a href="http://gallery.sourceforge.net" target="_blank">Gallery</a>
           <i>Random Image</i> block within the bBlog sidebar.  Although, the plugin can accomodate any
           content provider by an external URL.
           <p>Be wary that, of course, you cannot control the actual content the provider gives you.';

  return array (
    'name'           =>'externalcontent',
    'type'           =>'admin',
    'nicename'       =>'External Content',
    'description'    =>'Edit bBlog external content providers',
    'template' 	     =>'externalcontent.html',
    'authors'        =>'Paul Balogh <javaducky@gmail.com>',
    'licence'        =>'GPL',
    'help'           => $help
  );
}

function admin_plugin_externalcontent_run(&$bBlog) {

    // Determine what our admin is attempting to do
    if(isset($_GET['action']))      {$action = $_GET['action'];} 
    elseif(isset($_POST['action'])) {$action = $_POST['action'];}
    else {                           $action = '';}

    switch($action) {
    	case "New" :  // add new provider
    		$bBlog->query("insert into ".T_EXT_CONTENT."
                set nicename='".my_addslashes($_POST['nicename'])."',
                url='".my_addslashes($_POST['url'])."'");
    		break;
    
    	case "Delete" : // delete provider
                $bBlog->query("delete from ".T_EXT_CONTENT." where id=".$_POST['providerid']);
                break;
    
    	case "Save" : // update an existing provider
    	        if (isset($_POST['enabled'])) {
    	            $enabled = 'true';
    	        } else {
    	            $enabled = 'false';
    	        }
                $bBlog->query("update ".T_EXT_CONTENT."
                set nicename='".my_addslashes($_POST['nicename'])."',
                url='".my_addslashes($_POST['url'])."',
                enabled='".$enabled."' 
                where id=".$_POST['providerid']);
            	break;
            	
    	default : // show form
            	break;
        	
	}
	
    $bBlog->smartyObj->assign('eproviders', 
                              $bBlog->get_results("select * from ".T_EXT_CONTENT." order by nicename"));

}

?>
