<?php
include "bblog/config.php";
$bBlog->smartyObj->assign('string', $_GET['string']);
$encoded = urlencode($_GET['string']);
$bBlog->smartyObj->assign('encodedstring', $encoded);
$bBlog->smartyObj->display('search.html');
?>
