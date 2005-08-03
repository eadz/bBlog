<?php
/**
 * modifier.smartypost.php - processes smarty tags embedded in posts
 * <p>
 * @author JMario Delgado <mario@seraphworks.com>
 * @copyright Copyright (C) 2003  Eaden McKee <email@eadz.co.nz>
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 * @package bblog
 */

function identify_modifier_smartypost () {

    $help='<br>Use the smartypost modifier on the {$post.body} tag,<br> 
           to process any Smarty tags you have embedded in a post.<br><br>
           Example : 
           <ul> 
               <li>{$post.body|smartypost}</li> 
           </ul> Smarty Post can be used with other modifiers.<br><br>
           Example :
           <ul> 
               <li>{$post.body|readmore:$post.postid|smartypost}</li> 
           </ul>';

    return array (
      'name'          =>'smartypost',
      'type'          =>'smarty_modifier',
      'nicename'      =>'Smarty Post',
      'description'   =>'Processes Smarty tags in a post',
      'authors'       =>'Mario Delgado <mario@seraphworks.com>',
      'licence'       =>'GPL',
      'help'	      =>$help
    );

}

function smarty_modifier_smartypost($text) {

    global $bBlog;
    $bBlog->smartyObj->assign('smartied_post', $text);
    // we will store the smartypost template in the inc/admin_template dir, becasue almost noone will need to change it, - reduce clutter in the templates/* directory.
    $tmptemplatedir = $bBlog->smartyObj->template_dir;
    $tmpcompileid = $bBlog->smartyObj->compile_id;
    $bBlog->smartyObj->template_dir = BBLOGROOT.'inc/admin_templates';
    $bBlog->smartyObj->compile_id = 'admin';
    $output = $bBlog->smartyObj->fetch('smartypost.html');
    $bBlog->smartyObj->template_dir = $tmptemplatedir;
    $bBlog->smartyObj->compile_id = $tmpcompileid;


    return $output;
	  
}

?>
