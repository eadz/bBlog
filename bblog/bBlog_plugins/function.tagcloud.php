<?php
/**
 * function.tagcloud.php
 *
 * @package bBlog
 * @author Eaden McKee - <email@eadz.co.nz> - last modified by $LastChangedBy: $
 * @version $Id: $
 * @copyright The bBlog Project, http://www.bblog.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 * @fixme eadz: check to see if it works in a post
 */


function identify_function_tagcloud () {
$help = '
<p>tagcloud is a Smarty function to be used in templates.
<p>Example usage
<ul><li>To create a tag cloud :<br>
   {tagcloud}
   <li>Used within a {posts} loop, to only link to tags that the post is in :<br>
      {tagcould sections=$post.sections}
</ul>';


  return array (
    'name'           =>'tagcloud',
    'type'             =>'function',
    'nicename'     =>'Tag Cloud',
    'description'   =>'Make links to sections in the form of a tag cloud',
    'authors'        =>'Eaden McKee <eadz@bblog.com>',
    'licence'         =>'GPL',
    'help'   => $help
  );


}

function smarty_function_tagcloud($params, &$smartyObj) {
    $bBlog = & $smartyObj->get_template_vars("bBlog_object");

    if(isset($params['sections'])) $sections = $params['sections'];
    else $sections = $bBlog->sections;
    $num = count($sections);
    
    // rgb values
    $maxcolor = 1;
    $mincolor = 128;

    // font size in pixels
    $maxsize = 13;
    $minsize = 9;

    $max = 1;
    foreach($sections as $section) 
       if($section->postcount > $max) 
           $max = $section->postcount;

    $i = 0;
    $linkcode = '';
    foreach ($sections as $section) {
            // we using arrays in the template and objects in the core..
            if(isset($params['sections'])) {
                   $url = $section['url'];
                   $nicename = $section['nicename'];
            } else {
                   $url = $section->url;
                   $nicename = $section->nicename;
            }

            $fontsize = $minsize + round( (($maxsize - $minsize)/$max) * $section->postcount );
            $fontcolor = $mincolor + round( (($maxcolor - $mincolor)/$max) * $section->postcount );
            $fc = $fontcolor.",".$fontcolor.",".$fontcolor;
            $linkcode .= ' <a href="'.$url.'" rel="tag" style="font-size:'.$fontsize.'px; color:rgb('.$fc.');">'.$nicename.'</a> ';

    }

    return $linkcode;
}

/* vim: set expandtab: */

?>
