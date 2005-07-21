<?php
function upgrade_from_bblog07_intro() {
	// things to say on the 2nd page
	echo "<p>The upgrade process from bBlog 0.7 is just like a fresh install, you do not keep any files from 0.7 (except maybe some templates). The only difference between a 0.7 to 0.8 upgrade and a fresh install is instead of creating the new tables in the database, we use the existing tables ( with a few modifications ).</p>
	<p>Please backup your existing bblog directory and install from a fresh directory.</p>
	<p>It is also a good idea to backup your database before continuing with the upgrade.</p>
	<p>On the next page your database details should be the same as for your old bBlog 0.7.</p>";
}

function upgrade_from_bblog07_pre() {
	/* Wow, this stuff is really broken.  Attempting to fix. - vantage1313 */
	/* Replacing hard coded $pfx with proper variable */
	global $db,$step,$config;
	$url = $config['url'];
	$pfx = $config['table_prefix'];
	echo "<h3>Patching Tables</h3><p>";
	// things to do after the config is done, but before the rest.
	$q = array();
	$q[] = "ALTER TABLE `{$pfx}authors` MODIFY `password` varchar(40) NOT NULL";
	$q[] = "ALTER TABLE `{$pfx}authors` ADD `ip_domain` VARCHAR( 255 ) NOT NULL default ''";
	$q[] = "
	CREATE TABLE `{$pfx}checkcode` (
		`id` int(10) unsigned NOT NULL auto_increment,
		`checksum` varchar(255) NOT NULL default '',
		`timestamp` timestamp(14) NOT NULL,
		PRIMARY KEY  (`id`)
	) TYPE=InnoDB;";
	$q[] = "INSERT INTO `{$pfx}config` (`id`, `name`, `value`) VALUES ('', 'SMARTY_TAGS_IN_POST', 'false')";
	$q[] = "INSERT INTO `{$pfx}config` (`id`, `name`, `value`) VALUES ('', 'CUSTOMURLS', 'false')";
	$q[] = "INSERT INTO `{$pfx}config` (`id`, `name`, `value`) VALUES ('', 'CLEANURLS', 'false')";
	$q[] = "INSERT INTO `{$pfx}config` (`id`, `name`, `value`) VALUES ('', 'IMAGE_VERIFICATION', 'false')";
	$q[] = "INSERT INTO `{$pfx}config` (`id`, `name`, `value`) VALUES ('', 'WYSIWYG', 'false')";
	$q[] = "INSERT INTO `{$pfx}config` ( `id` , `name` , `value` ) VALUES ('', 'FANCYURL', 'false')";
	$q[] = "
	CREATE TABLE `{$pfx}external_content` (
		`id` int(11) NOT NULL auto_increment,
		`nicename` varchar(255) NOT NULL default '',
		`url` varchar(255) NOT NULL default='',
		`enabled` enum('true', 'false') NOT NULL default='false',
		PRIMARY KEY(`id`)
		) TYPE=MyISAM;";
  $q[] = "create table {$pfx}photobblog( 
		`postid` int(11) NOT NULL default '0',
		`imageLoc` varchar(20) NOT NULL default '',
		`caption` varchar(255) NOT NULL default '',
		UNIQUE KEY `postid` (`postid`)
		) TYPE=MyISAM;";
	$q[] = "DELETE FROM `{$pfx}plugins`";
	$q[] = "ALTER TABLE `{$pfx}posts` ADD `pagename` varchar(255) NOT NULL";
	$q[] = "ALTER TABLE `{$pfx}posts` ADD `fancyurl` varchar(255) NOT NULL";
	$q[] = "CREATE TABLE `{$pfx}search` (
		`id` bigint(20) unsigned NOT NULL auto_increment,
		`article_id` int(10) unsigned NOT NULL default '0',
		`value` varchar(255) NOT NULL default '',
		`score` tinyint(3) unsigned NOT NULL default '0',
		PRIMARY KEY  (`id`)
		) TYPE=InnoDB;";
	$q[] = "CREATE TABLE `{$pfx}search_tmp` (
		`id` int(10) unsigned NOT NULL auto_increment,
		`string` varchar(255) NOT NULL default '',
		`article_id` int(10) unsigned NOT NULL default '0',
		`points` int(10) unsigned NOT NULL default '0',
		`time` timestamp(14) NOT NULL,
		PRIMARY KEY  (`id`)
		) TYPE=MyISAM;";

	foreach($q as $q2do) {
 		$db->query($q2do);
		echo ".";
	}
// This bit will read in all the passwords in the author table and re-insert them
// with the sha1 function
	$q = "SELECT id,password FROM {$pfx}authors";
	$pass = $db->get_results($q);
	foreach ($pass as $pw) {
		$q = "UPDATE {$pfx}authors SET password=sha1('{$pw->password}') where id={$pw->id};";
		$db->query($q);
	}
	echo " Done. </p>";
	echo "<p><input type=\"submit\" value=\"Next &gt;\" name=\"submit\" /></p>";
	$step = 5; // skip creating tables

}


//function upgrade_from_bblog07_post() {
// things to say / do after the config file is written, and the database stuff done.
// maybe append to $config['extra_config'] for extra config to be written to the config file.

//}

?>
