<?php
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
	$bBlog->smartyObj->assign("files",$dir);
	$bBlog->smartyObj->assign("path",UPLOADFILESURL);
}
?>
