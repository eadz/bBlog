<?php

// xushi: flyspray #55: make sure install/ is deleted
if (file_exists("bblog/install/")) {
	//die("Error: Make sure the folder bblog/install is deleted.");
}

include "bblog/config.php";
$bBlog->smartyObj->assign('year',$_GET['year']);
$bBlog->smartyObj->assign('month',sprintf("%02s", $_GET['month']));
$bBlog->display('archives.html');
?>
