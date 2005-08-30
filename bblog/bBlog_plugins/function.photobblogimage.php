<?php
/**
 * function.photobblogimage.php
 * <p>
 * @copyright Copyright (C) 2003  Eaden McKee <email@eadz.co.nz>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 * @package bblog
 */

function identify_function_photobblogimage () {
$help = 'Sets Smarty template variables for the image file location and caption name for a given post.  Sample usage: {photobblogimage postid=$postid}';

  return array (
    'name'           =>'photobblogimage',
    'type'             =>'function',
    'nicename'     =>'Photobblog image',
    'description'   =>'This function outputs the photobblog image location.',
    'authors'        =>'Raefer Gabriel <blog@raefer.fastmail.fm>, Mark Dobossy <mdobossy@princeton.edu>',
    'licence'         =>'GPL',
    'help'   => $help
  );
}

function smarty_function_photobblogimage($params, &$smartyObj)
{
    $bBlog = & $smartyObj->get_template_vars("bBlog_object");
    $postid=$params['postid'];
    if ($postid!='') {
        $data=$bBlog->get_row("select imageLoc,caption from ".TBL_PREFIX."photobblog where postid=$postid");
        $smartyObj->assign('imageloc', $data->imageLoc);
        $smartyObj->assign('imagecaption', $data->caption);
    }
    else
    {
        $smartyObj->assign('imageloc', '');
        $smartyObj->assign('imagecaption', '');
    }
}

?>