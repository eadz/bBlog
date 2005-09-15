<?php
/**
 * function.photobblog.php
 *
 * @package bBlog
 * @author Eaden McKee - <email@eadz.co.nz> - last modified by $LastChangedBy: $
 * @version $Id: $
 * @copyright The bBlog Project, http://www.bblog.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */


function identify_function_photobblog () {
$help = 'Shows the picture from photobblog.  Sample usage: {photobblog postid=$postid}  Optional parameters:
class - needs a string to the class name of this image.
mode  - if set to "full", will return a full size image instead of a linked thumbnail';

  return array (
    'name'           =>'photobblog',
    'type'             =>'function',
    'nicename'     =>'Photobblogs photo post',
    'description'   =>'This function displays the image.',
    'authors'        =>'Mark Dobossy <mdobossy@princeton.edu>',
    'licence'         =>'GPL',
    'help'   => $help
  );
}

function smarty_function_photobblog($params, &$smartyObj)
{
    $bBlog = & $smartyObj->get_template_vars("bBlog_object");
    $postid=$params['postid'];
    $data=photobblog_get_image($bBlog, $postid);

    //Set class if submitted.
    if(isset($params['class']))
        $classstr="class=\"".$params['class']."\" ";
    else
        $classstr="";

    //construct link/image.
    if($data)
    {
        $imageLoc = $data->imageLoc;
                preg_match('/([^\.]+)(\..*)/i', $imageLoc, $rex);
        $caption = $data->caption;
        if ($params['mode']=='full') {
            $imagestr="<span class=\"photo\"><img src=\"".BBLOGURL."pbimages/thumbs/".$rex[1]."-150x100".$rex[2]."\" ".$classstr." alt=\"".$caption."\" title=\"".$caption."\" border=\"0\" /></span>";
        } else {
            $imagestr="<div class='rightbox'><a href=\"".BBLOGURL."pbimages/".$imageLoc."\" target=\"_blank\"><img src=\"".BBLOGURL."pbimages/thumbs/".$rex[1]."-150x100".$rex[2]."\" ".$classstr." alt=\"".$caption."\" title=\"".$caption."\" border=\"0\" /></a></div>";
        }
    }
    else
        $imagestr="";
    return $imagestr;
}

function photobblog_get_image(&$bBlog, $postid)
{
    if(empty($postid)){
        return false;
    }
    $data=$bBlog->get_row("select imageLoc,caption from ".TBL_PREFIX."photobblog where postid=$postid");
    if(empty($data))
        return false;
    else
        return $data;
}
?>