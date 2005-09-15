<?php
/**
 * header.php - A standard header for the install/upgrade scripts
 *
 * @package bBlog
 * @author Eaden McKee - <email@eadz.co.nz> - last modified by $LastChangedBy: $
 * @version $Id: $
 * @copyright The bBlog Project, http://www.bblog.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */
 
 
// @todo we don't know if compiled_templates is writable yet, so we are not going to use smarty for the install
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <title>bBlog Installer</title>
  <link rel="stylesheet" type="text/css" title="Main" href="../style/admin.css" media="screen" />

</head>
<body>

<div id="header">
<h1>bBlog</h1>
<h2>Install or Upgrade</h2>
</div>
<style type="text/css">
#teaser a {
	cursor:default;
}
#teader a:hover {

}
</style>
<div id="teaser">
<a  class="active" href="Javascript:;">Welcome</a>
<a  <?php if ($step>1) echo 'class="active" '; ?> href="Javascript:;">File Permissions</a>
<a <?php if ($step >2) echo 'class="active" '; ?> href="Javascript:;">Database Settings</a>
<a <?php if ($step >3) echo 'class="active" '; ?> href="Javascript:;">Config</a>
<a <?php if ($step >4) echo 'class="active" '; ?> href="Javascript:;">Finish</a>
<a href="index.php?reset=true" style="cursor:pointer;">Reset install, start over.</a>

</div>
<div id="content">
<form action="index.php" method="POST" name="install">
