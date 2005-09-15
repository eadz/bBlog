<?php
/**
 * admin.files.php - file management to upload files
 *
 * @package bBlog
 * @author Eaden McKee - <email@eadz.co.nz> - last modified by $LastChangedBy: $
 * @version $Id: $
 * @copyright The bBlog Project, http://www.bblog.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */
 

function identify_admin_files () {
  return array (
    'name'           =>'files',
    'type'           =>'admin',
    'nicename'       =>'Upload Files',
    'description'    =>'Allows you to upload files',
    'authors'         =>'Martin Konicek <markon@air4web.com>',
    'licence'         =>'GPL',
    'help'            =>'',
        'template' 	=> 'files.html',
  );
}

function admin_plugin_files_run(){
    global $bBlog,$smartyObj,$_FILES;
    if(!empty($_FILES) && !(preg_match('/\.(php|php3|phtml|htaccess)/', $_FILES['userfile']['name']))) {
        $filename =& $_FILES['userfile']['name'];
        $uploadfile = UPLOADFILES . $filename;
        if(move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)){
            $bBlog->smartyObj->assign("uploaded",true);
        } else {
            $bBlog->smartyObj->assign("uploaded",false);
        }
    }
    $dir = scandir(UPLOADFILES);
    // Remove '.' and '..' from the list
    unset($dir[0],$dir[1]);
    // If we don't have files, we don't need to start the list and keep it empty
    $bBlog->smartyObj->assign('have_files',count($dir)>0);
    $bBlog->smartyObj->assign("files",$dir);
    $bBlog->smartyObj->assign("path",UPLOADFILESURL);
}
?>
