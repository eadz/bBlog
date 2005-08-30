<?php
/**
 * builtin.options.php - the option panel, allows you to change options
 * <p>
 * @copyright Copyright (C) 2003  Eaden McKee <email@eadz.co.nz>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 * @package bblog
 */

function identify_admin_options () {
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


function get_options () {
$options = array();

$options = array(
    array(
        "name"  => "EMAIL",
        "label" => "Email Address",
        "value" => C_EMAIL,
        "type"  => "email"
    ),

    array(
        "name" => "BLOGNAME",
        "label" => "Blog Name",
        "value" => C_BLOGNAME,
        "type"  => "text"
    ),

    array(
        "name" => "BLOG_DESCRIPTION",
        "label" => "Blog Description",
        "value" => C_BLOG_DESCRIPTION,
        "type"  => "text"
    ),

    array(
        "name" => "TEMPLATE",
        "label" => "Template",
        "value" => C_TEMPLATE,
        "type"  => "templateselect"
    ),

    array(
        "name" => "DEFAULT_MODIFIER",
        "label" => "Default Modifier",
        "value" => C_DEFAULT_MODIFIER,
        "type" => "modifierselect"
    ),

    array(
        "name" => "DEFAULT_STATUS",
        "label" => "Default Post Status",
        "value" => C_DEFAULT_STATUS,
        "type" => "statusselect"
    ),

    array(
        "name"  => "PING",
        "label" => "Notify websites of new posts. seperate with comma, e.g. weblogs.com/RPC2,www.bblog.com/ping.php,blo.gs/",
        "value" => C_PING,
        "type"  => "text"
    ),

    array(
        "name"  => "COMMENT_MODERATION",
        "label" => "Require your approval before comments appear",
        "value" => C_COMMENT_MODERATION,
        "type"  => "commentmoderation"
    ),

    array(
        "name"  => "NOTIFY",
        "label" => "Send notifications via email for new comments",
        "value" => C_NOTIFY,
        "type"  => "truefalse"
    ),
     array(
        "name"  => "META_KEYWORDS",
        "label" => "META Keywords for search engines",
        "value" => C_META_KEYWORDS,
        "type"  => "text"
    ),
     array(
        "name"  => "META_DESCRIPTION",
        "label" => "META Description for search engines",
        "value" => C_META_DESCRIPTION,
        "type"  => "text"
    ),
     array(
        "name"  => "COMMENT_TIME_LIMIT",
        "label" => "Comment Flood Protection ( minutes ) Set to 0 to disable.",
        "value" => C_COMMENT_TIME_LIMIT,
        "type"  => "text"
    ),
      array(
        "name"  => "SMARTY_TAGS_IN_POST",
        "label" => "Allow smarty tags in post",
        "value" => C_SMARTY_TAGS_IN_POST,
        "type"  => "truefalse"
    ),
     array(
        "name"  => "CLEANURLS",
        "label" => "Use clean urls e.g. /post/1/ instead of ?postid=1, you have to put the .htaccess file in place.",
        "value" => C_CLEANURLS,
        "type"  => "truefalse"
    ),
      array(
        "name"  => "CUSTOMURLS",
        "label" => "Use Custom urls e.g. /post/about-me.html - you enter about-me.html in the post screen",
        "value" => C_CUSTOMURLS,
        "type"  => "truefalse"
    ),
      array(
        "name"  => "IMAGE_VERIFICATION",
        "label" => "Use Image verification to stop comment spam ( RECOMMENDED! ) - requires php with zlib support ( try it out most hosts support it )",
        "value" => C_IMAGE_VERIFICATION,
        "type"  => "truefalse"
    ),

        array(
        "name"  => "FANCYURL",
        "label" => "Fancy url's",
        "value" => C_FANCYURL,
        "type"  => "truefalse"
    )


);

if(file_exists(BBLOGROOT . 'inc/admin_templates/jscripts/tiny_mce/tiny_mce.js')){
    $options[] = array(
        "name"  => "WYSIWYG",
        "label" => "WYSIWYG editor",
        "value" => C_WYSIWYG,
        "type"  => "truefalse"
    );
}

return $options;
}

$bBlog->get_modifiers();

$optionformrows = array();

$options = get_options();

if ((isset($_POST['submit'])) && ($_POST['submit'] == 'Save Options')) { // saving options..
 $updatevars = array();
 foreach($options as $option) {

     if(!isset($_POST[$option['name']])) break;

     switch ($option['type']) {
              case "text"  :
              case "email" :
              case "url"   :
                   $updatevars[] = array(
                                 "name" =>$option['name'],
                                 "value" => my_addslashes($_POST[$option['name']])
                                 );
                    break;
              case "password" :
                   if($_POST[$option['name']] != '')

                   $updatevars[] = array(
                                 "name" => $option['name'],
                                 "value" => $_POST[$option['name']]
                                 );
                   break;

              case "templateselect" :
                   // make sure we're not being poked.
                   if(ereg('^[[:alnum:]]+$',$_POST[$option['name']])) {
                      $updatevars[] = array(
                                    "name" => $option['name'],
                                    "value" => strtolower($_POST[$option['name']])
                                    );

                   }
                   break;

              case "statusselect" :
                   if($_POST[$option['name']] == 'live')
                         $updatevars[]= array(
                                        "name" => $option['name'],
                                        "value" => 'live'
                                        );

                    if($_POST[$option['name']] == 'draft')
                         $updatevars[]= array(
                                        "name" => $option['name'],
                                        "value" => 'draft'
                                        );
                   break;

          case "commentmoderation" :
                   if($_POST[$option['name']] == 'none')
                         $updatevars[]= array(
                                        "name" => $option['name'],
                                        "value" => 'none'
                                        );

                    if($_POST[$option['name']] == 'all')
                         $updatevars[]= array(
                                        "name" => $option['name'],
                                        "value" => 'all'
                                        );
             if($_POST[$option['name']] == 'urlonly')
                         $updatevars[]= array(
                                        "name" => $option['name'],
                                        "value" => 'urlonly'
                                        );
                   break;

              case "modifierselect" :
                   if(ereg('^[[:alnum:]]+$',$_POST[$option['name']]))
                         $updatevars[] = array(
                                       "name"=>$option['name'],
                                       "value"=>$_POST[$option['name']]
                                       );

                   break;
          case "truefalse" :
            $updatevars[] = array(
                "name"=>$option['name'],
                "value"=>$_POST[$option['name']]
                );
            break;
              default: break;


  } // switch
 } // foreach


} // if
if ((isset($_POST['submit'])) && ($_POST['submit'] == 'Save Options')) {
  foreach($updatevars as $update) {
   $bBlog->query("update ".T_CONFIG." set value='".$update['value']."' where `name`='".$update['name']."'");
   } // foreach
   $bBlog->smartyObj->assign("showmessage",TRUE);
   $bBlog->smartyObj->assign("showoptions",'no');
   $bBlog->smartyObj->assign("message_title","Options Updated");
   $bBlog->smartyObj->assign("message_content","Your changes have been saved.<br><a href='index.php?b=options&r=".rand(20,214142124)."'>Click here to continue</a>");

} else {

foreach($options as $option) {
        $formleft = $option['label'];
        switch ($option['type']) {
              case "text"  :
              case "email" :
              case "url"   :
                   $formright = '<input type="text" name="'.$option['name'].'"
                                    class="bf" value="'.$option['value'].'"/>';
                   break;

              case "password" :
                   $formright = '<input type="password" name="'.$option['name'].'"
                                    class="bf" value="'.$option['value'].'"/>';
                   break;

              case "templateselect" :
                   $formright = '<select name="'.$option['name'].'" class="bf">';
                   $d = dir("templates");
                   while (false !== ($entry = $d->read())) {
                       if(ereg("^[a-zA-Z0-9]{1,20}$",$entry)){
                           $formright .= "<option value=\"$entry\"";
                           if($option['value'] == $entry) $formright .=" selected";
                           $formright .= ">$entry</option>";
                       }
                   }
                   $d->close();
                   $formright .= '</select>';
                   break;

              case "statusselect" :
                   $formright = '<select name="'.$option['name'].'" class="bf">';
                   $formright .= '<option value="live" ';
                   if(C_DEFAULT_STATUS == 'live') $formright .= 'selected';
                   $formright .= '>Live'.'</option>';
                   $formright .= '<option value="draft" ';
                   if(C_DEFAULT_STATUS == 'draft') $formright .= 'selected';
                   $formright .= '>Draft'.'</option>';
                   $formright .= '</select>';
                   break;

              case "truefalse" :
                   $formright = '<select name="'.$option['name'].'" class="bf">';
                   $formright .= '<option value="true" ';
                   if($option['value'] == 'true') $formright .= 'selected';
                   $formright .= '>Yes'.'</option>';
                   $formright .= '<option value="false" ';
                   if($option['value'] == 'false') $formright .= 'selected';
                   $formright .= '>No'.'</option>';
                   $formright .= '</select>';
                   break;

              case "modifierselect" :
                   $formright = '<select name="'.$option['name'].'" class="bf">';
                   if ($bBlog->modifiers)
                   {
                       foreach($bBlog->modifiers as $mod)
                       {
                           $formright .= '<option value="'.$mod->name.'" ';
                           if(C_DEFAULT_MODIFIER == $mod->name) $formright .= 'selected';
                           $formright .= '>'.$mod->nicename.'</option>';
                       }
                   }
                   $formright .= '</select>';
                   break;

          case "commentmoderation" :
                   $formright = '<select name="'.$option['name'].'" class="bf">';

                   $formright .= '<option value="none" ';
                   if(C_COMMENT_MODERATION == 'none') $formright .= 'selected';
                   $formright .= '>No Moderation</option>';

           $formright .= '<option value="urlonly" ';
                   if(C_COMMENT_MODERATION == 'urlonly') $formright .= 'selected';
                   $formright .= '>Only for comments with links</option>';

           $formright .= '<option value="all" ';
                   if(C_COMMENT_MODERATION == 'all') $formright .= 'selected';
                   $formright .= '>Moderate All Comments</option>';


                   $formright .= '</select>';
                   break;

              default: $formright = ''; break;

        }
        $optionrows[] = array("left" => $formleft,"right" => $formright);
        // have help here too someday :)


}
$bBlog->smartyObj->assign("optionrows",$optionrows);
} // end of else
$bBlog->display("options.html");








?>
