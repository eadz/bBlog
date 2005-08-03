<?php
/**
 * function.links.php - a smarty function for displaying bBlog links
 * <p>
 * @author Mario Delgado <mario@seraphworks.com>
 * @copyright Copyright (C) 2003  Eaden McKee <email@eadz.co.nz>
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 * @package bblog
 */ 

function identify_function_links () {
$help = '
<p>Links is a Smarty function to be used in templates.
<p>Example usage
<ul><li>To return a list of links, one per line :<br>
   {links}</li>
   <li>To return a list, seperated by a # <br>
     {links sep=#}</li>
   <li>To return an unorderd list (&lt;ul>&lt;li> etc) use {links mode=list}</li>
   <li>To return a list for the category humor <br>
     {links cat=humor}</li>
   <li>To return a list without the category stores <br>
     {links notcat=stores}</li>
   <li>To limit the number of links returned <br>
     {links num=5}</li>
   <li>The default behavior is to return the list in <br>
    or as set in the admin panel. This <br>
     can be changed with one of the following key words <br>
    <br>
     {links ord=nicename} <br>
     {links ord=category}</li>
   <li>To return a list in descending order <br>
     {links desc=TRUE}</li>
   <li>cat and notcat are mutually exclusive and cannot <br>
     be used together. They can both be used with sep, <br>
     ord, num and desc which can all be used together. <br>
     Category names and ord key words are case sensative.</li>
</ul>';

  return array (
    'name'             =>'links',
    'type'             =>'function',
    'nicename'         =>'Links',
    'description'      =>'Make a list of links',
    'authors'          =>'Mario Delgado <mario@seraphworks.com>',
    'licence'          =>'GPL',
    'help'             => $help
  );


}

function smarty_function_links($params, &$smartyObj) {
  $bBlog = & $smartyObj->get_template_vars("bBlog_object");

  if($params['mode'] == 'list') $markedlinks = '<ul>';
    else $markedlinks = '';

    if(!isset($params['sep'])) {
       $sep = "<br />";
    } else {
       $sep = $params['sep'];
    }
    
    if(isset($params['presep'])) $presep = $params['presep']; // use this for lists

    if(isset($params['desc'])) {
       $asde = "DESC";
    } else {
       $asde = "";
    }

    if(isset($params['ord'])) {
       $order = $params['ord'];
    } else {
       $order = "position";
    }

    if(isset($params['num'])) {
       $max = $params['num'];
    } else {
       $max = "10";
    }

    if(isset($params['cat'])) {
       $cat = $bBlog->get_var("select categoryid from ".T_CATEGORIES." where name='".$params['cat']."'");
    }

    if(isset($params['notcat'])) {
       $notcat = $bBlog->get_var("select categoryid from ".T_CATEGORIES." where name='".$params['notcat']."'");
    }

    if ($cat) {
       $links = $bBlog->get_results("select * from ".T_LINKS." where category='".$cat."' order by ".$order." ".$asde." limit ".$max);    
    } elseif ($notcat) {
       $links = $bBlog->get_results("select * from ".T_LINKS." where category !='".$notcat."' order by ".$order." ".$asde." limit ".$max);    
    } else {
       $links = $bBlog->get_results("select * from ".T_LINKS." order by ".$order." ".$asde." limit ".$max);    
    }


    if(!empty($links)) {
      foreach ($links as $link) {
              $url = $link->url;
              $nicename = $link->nicename;
	      if($params['mode'] == 'list') {
	      	$markedlinks .= "<li><a href='$url'>$nicename</a></li>";
	      } else $markedlinks .= $presep.'<a href="'.$url.'">'.$nicename.'</a>'.$sep;
      }
    } 
    if($params['mode'] == 'list') $markedlinks .= '</ul>';
    return $markedlinks;
}

?>
