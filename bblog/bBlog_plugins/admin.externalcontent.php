<?php
/**
 * admin.externalcontent.php - administer external content
 *
 * @package bblog
 * @author Paul Balogh - <javaducky@gmail.com> - last modified by $LastChangedBy: $
 * @version $Id: $
 * @copyright The bBlog Project, http://www.bblog.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
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
