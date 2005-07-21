<?php
function upgrade_from_bblog04_intro() {
	// things to say on the 2nd page
	echo "<p>The upgrade process from bBlog 0.4 is just like a fresh install, you do not keep any files from 0.4. The only difference between a 0.4 to 0.6 upgrade and a fresh install is Instead of creating the new tables in the database, we use the existing tables ( with a few modifications ). <br />On the next page your database details should be the same as for your old bBlog 0.4.</p>";
}

function upgrade_from_bblog04_pre() {
	global $db,$step;
	echo "<h3>Patching Tables</h3><p>";
	// things to do after the config is done, but before the rest.
	$q = array();
	$q[] = "ALTER TABLE `bB_comments` ADD `title` VARCHAR( 255 ) NOT NULL ,  ADD `type` ENUM( 	'comment', 'trackback' ) NOT NULL;";
	$q[] = "INSERT INTO `bB_config` VALUES ('', 'PING', 'false');";
	$q[] = "INSERT INTO `bB_config` VALUES ('', 'NOTIFY', 'false');";
	$q[] = "ALTER TABLE `bB_comments` ADD `deleted` ENUM( 'false', 'true' ) DEFAULT 'false' NOT NULL ;";
	$q[] = "DELETE FROM `bB_config`";
	$q[] = "DELETE FROM `bB_plugins`";
	$q[]="INSERT INTO `bB_config` (`id`, `name`, `value`) VALUES
	('', 'EMAIL', 'change@me'),
	('', 'BLOGNAME', '".$config['blogname']."'),
	('', 'TEMPLATE', 'default'),
	('', 'DB_TEMPLATES', 'false'),
	('', 'DEFAULT_MODIFIER', 'textile'),
	('', 'DEFAULT_STATUS', 'live'),
	('', 'PING','false'),
	('', 'NOTIFY','false'),
	('', 'BLOG_DESCRIPTION', '".$config['blogdescription']."'),
	('', 'LAST_MODIFIED', UNIX_TIMESTAMP());";
   foreach($q as $q2do) {
 	$db->query($q2do);
	echo ".";
   }
   echo " Done. </p>";
   echo "<h3>Updating Data</h3><p>";
   $posts = $db->get_results("select * from bB_posts");
   foreach($posts as $post) {
   	$nsect = '';
	if($post->sections == '') {
		echo htmlspecialchars($post->title)." Fine<br />";
	} else {
		$tmpsec = explode(":",$post->sections);
		$nsect  = ":".implode(":",$tmpsec).":";
		$db->query("update bB_posts set sections='$nsect' where postid='{$post->postid}'");
		echo htmlspecialchars($post->title)." Updated<br />";
	}

   }
   echo "Done<br /><a href='install.php'>Click here to continue</a>";
   $step = 5; // skip creating tables

}

//function upgrade_from_bblog04_post() {
// things to say / do after the config file is written, and the database stuff done.
// maybe append to $config['extra_config'] for extra config to be written to the config file.

//}

?>
