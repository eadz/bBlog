<?php
/**
 * function.getsections.php - retrieve the content page linked to the section
 * <p>
 * @author Elie `LordWo` BLETON <lordwo_REM_OVE_THIS@laposte.net>
 * @copyright Copyright (C) 2003  Eaden McKee <email@eadz.co.nz>
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 * @package bblog
 */ 

function identify_function_getcontent () {
$help = '
<p>the {getcontent} function is used to retrieve the content page linked to the section.<br /><br />
Your index.html template should include() the result of this function, or proceed with normal blog display if the result is FALSE.</p>
';
  return array (
    'name'           =>'getcontent',
    'type'           =>'function',
    'nicename'       =>'GetContent',
    'description'    =>'Returns the content page linked to the section. Return FALSE if none.<br>This',
    'authors'        =>'Elie `LordWo` BLETON <lordwo_REM_OVE_THIS@laposte.net>',
    'licence'        =>'GPL',
    'help'           => $help
  );
}

function smarty_function_getcontent($params, &$smartyObj) {
  $bBlog = & $smartyObj->get_template_vars("bBlog_object");

  // Retrieving data
  $bBlog->get_sections();
  $sections = $bBlog->sections;
  foreach ($sections as $object) {
     $new[$object->sectionid] = $object;
  }
  $sections = $new;
  
  $current_section = $smartyObj->get_template_vars("sectionid");
    
  // Return  
  $smartyObj->assign("content",$sections[$current_section]->content);
}

?>
