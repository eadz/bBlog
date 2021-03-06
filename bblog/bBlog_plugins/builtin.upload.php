<?php
/**
 * admin.post.php - Handles posting an entry
 *
 * @package bBlog
 * @author Eaden McKee - <email@eadz.co.nz> - last modified by $LastChangedBy: $
 * @version $Id: $
 * @copyright The bBlog Project, http://www.bblog.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

function identify_admin_upload () {
    return array (
    'name'           =>'upload',
    'type'             =>'builtin',
    'nicename'     =>'Image Upload',
    'description'   =>'Upload images',
    'authors'        =>'Martin Konicek <markon@air4web.com>',
    'licence'         =>'GPL',
    'help'            =>''
  );
}

if(!empty($_FILES)){
    $ext = preg_match('/(\.[a-z]+)$/i', $_FILES['userfile']['name'], $matches);
    $filename = md5(microtime().rand()).$matches[1];
    $uploadfile = IMAGESUPLOADROOT . $filename;
    move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile);
    $bBlog->smartyObj->assign('uploaded', '<img src=\\"'.IMAGESUPLOADURL.$filename.'\\" alt=\\"'.basename($_FILES['userfile']['name']).' ['.filesize($uploadfile).' bytes]'.'\\" />');
}
$bBlog->display('upload.html');
?>
