<?php
/**
 * admin.sections.php - administer sections
 * <p>
 * @copyright Copyright (C) 2003  Eaden McKee <email@eadz.co.nz>
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 * @package bblog
 */

function identify_admin_sections () {
  $help = '<p>Sections are just a way of organizing posts. This plugin allows you to edit and delete sections.
  When you make or edit a post, you can choose which sections it goes it.';
  return array (
    'name'           =>'sections',
    'type'             =>'admin',
    'nicename'     =>'Sections',
    'description'   =>'Edit Sections',
    'template' 	=> 'sections.html',
    'authors'        =>'Eaden McKee <eadz@bblog.com>',
    'licence'         =>'GPL',
    'help'            => $help
  );
}

function admin_plugin_sections_run(&$bBlog) {
// Again, the plugin API needs work.
if(isset($_GET['sectdo']))  { $sectdo = $_GET['sectdo']; }
elseif(isset($_POST['sectdo'])) { $sectdo = $_POST['sectdo']; }
else { $sectdo = ''; }

switch($sectdo) {
	case 'new' :  // sections are being editied
		$bBlog->query("insert into ".T_SECTIONS."
			set nicename='".my_addslashes($_POST['nicename'])."',
			name='".my_addslashes($_POST['urlname'])."'");
		$bBlog->get_sections(); // update the section cache
		break;

	case "Delete" : // delete section
		// have to remove all references to the section in the posts
                $sect_id = $bBlog->sect_by_name[$_POST['sname']];
                if($sect_id > 0) { //
			$posts_in_section_q = $bBlog->make_post_query(array("sectionid"=>$sect_id));
                        $posts_in_section = $bBlog->get_posts($posts_in_section_q,TRUE);
                        if($posts_in_section) {
                            foreach($posts_in_section as $post) {
                        	unset($tmpr);
                                $tmpr = array();
				$tmpsections = explode(":",$post->sections);
                                foreach($tmpsections as $tmpsection) {
                                	if($tmpsection != $sect_id) $tmpr[] = $tmpsection;
				}
                                $newsects = implode(":",$tmpr);
				// update the posts to remove the section
                                $bBlog->query("update ".T_POSTS." set sections='$newsects'
                                	where postid='{$post->postid}'");

                            } // end foreach ($post_in_section as $post)
			} // end if($posts_in_section) 
                        // delete the section
                        //$bBlog->get_results("delete from ".T_SECTIONS." where sectionid='$sect_id'");
                        $bBlog->query("delete from ".T_SECTIONS." where sectionid='$sect_id'");
			//echo "delete from ".T_SECTIONS." where sectionid='$sect_id'";
			$bBlog->get_sections();
			//$bBlog->debugging=TRUE;

                } // else show error
	case "Save" :
 		$sect_id = $bBlog->sect_by_name[$_POST['sname']];
                if($sect_id < 1) break;
                $bBlog->query("update ".T_SECTIONS
                	." set nicename='".my_addslashes($_POST['nicename'])."'
                        where sectionid='$sect_id'");
                $bBlog->get_sections(); // update section cache
        	break;

	default : // show form
        	break;
	}
        $bBlog->smartyObj->assign('esections',$bBlog->sections);
}


?>