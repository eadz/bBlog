<?php
include "bblog/config.php";
$bBlog->smartyObj->assign('year',$_GET['year']);
$bBlog->smartyObj->assign('month',sprintf("%02s", $_GET['month']));
$bBlog->display('archives.html');
?>
