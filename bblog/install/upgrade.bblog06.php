<?php
function upgrade_from_bblog06_intro() {
	// things to say on the 2nd page
	echo "<p>The upgrade process from bBlog 0.6 is just like a fresh install, you do not keep any files from 0.6. The only difference between a 0.6 to 0.7 upgrade and a fresh install is instead of creating the new tables in the database, we use the existing tables ( with a few modifications ). <br />On the next page your database details should be the same as for your old bBlog 0.6.</p>";
}

function upgrade_from_bblog06_pre() {
	global $db,$step,$config;
	$url = $config['url'];
	$pfx = 'bB_';
	echo "<h3>Patching Tables</h3><p>";
	// things to do after the config is done, but before the rest.
	$q = array();
	$q[] = "ALTER TABLE `{$pfx}posts` ADD ownerid int(10) NOT NULL default '0', ADD INDEX (ownerid);";
	$q[] = "ALTER TABLE `{$pfx}comments` ADD `onhold` TINYINT( 1 ) DEFAULT '0' NOT NULL ;";
        $q[] = "DELETE FROM `{$pfx}config`";
	$q[] = "DELETE FROM `{$pfx}plugins`";
	
	$q[]="INSERT INTO `{$pfx}config` (`id`, `name`, `value`) VALUES
	('', 'EMAIL', '".$config['email']."'),
	('', 'BLOGNAME', '".$config['blogname']."'),
	('', 'TEMPLATE', 'lines'),
	('', 'DEFAULT_MODIFIER', 'textile'),
	('', 'DEFAULT_STATUS', 'live'),
	('', 'PING','bblog.com/ping.php,weblogs.com/RPC2'),
	('', 'NOTIFY','false'),
	('', 'COMMENT_TIME_LIMIT',1),
	('', 'BLOG_DESCRIPTION', '".$config['blogdescription']."'),
	('', 'LAST_MODIFIED', UNIX_TIMESTAMP());";
	
	$q[] = "INSERT INTO {$pfx}config VALUES ('','COMMENT_MODERATION','none');";
	$q[] = "INSERT INTO {$pfx}config VALUES ('','META_DESCRIPTION','Bblog');";
        $q[] = "INSERT INTO {$pfx}config VALUES ('','META_KEYWORDS','blogging, php');";
        $q[] = "CREATE TABLE {$pfx}authors (
                  id int(10) NOT NULL auto_increment,
                  nickname varchar(20) NOT NULL default '',
                  email varchar(100) NOT NULL default '',
                  password varchar(20) NOT NULL default '',
                  fullname varchar(50) NOT NULL default '',
                  url varchar(50) NOT NULL default '',
                  icq int(10) unsigned NOT NULL default '0',
                  profession varchar(30) NOT NULL default '',
                  likes text NOT NULL,
                  dislikes text NOT NULL,
                  location varchar(25) NOT NULL default '',
                  aboutme text NOT NULL,
                  PRIMARY KEY  (id)
                ) TYPE=MyISAM;";
        $q[] = "CREATE TABLE {$pfx}categories (
  categoryid int(11) NOT NULL auto_increment,
  name varchar(60) NOT NULL default '',
  PRIMARY KEY  (categoryid)
) TYPE=MyISAM; "; // categories for links

        $q[] = "INSERT INTO {$pfx}categories VALUES (1,'Navigation');";
	$q[] = "INSERT INTO {$pfx}categories VALUES (2,'Blogs I read');";
        $q[] = "CREATE TABLE {$pfx}links (
  linkid int(11) NOT NULL auto_increment,
  nicename varchar(255) NOT NULL default '',
  url varchar(255) NOT NULL default '',
  category int(11) NOT NULL default '0',
  position int(8) NOT NULL default '500',
  PRIMARY KEY  (linkid)
) TYPE=MyISAM;";

        $q[] = "ALTER TABLE `{$pfx}posts` ADD `hidefromhome` TINYINT( 1 ) DEFAULT '0' NOT NULL AFTER `ownerid` , ADD `allowcomments` ENUM( 'allow', 'timed', 'disallow' ) DEFAULT 'allow' NOT NULL AFTER `hidefromhome` , ADD `autodisabledate` INT( 11 ) DEFAULT '0' NOT NULL AFTER `allowcomments` ;";

	$q[]= "INSERT INTO {$pfx}links VALUES (1,'Home','{$url}/',1,20);";
        $q[]= "INSERT INTO {$pfx}links VALUES (2,'Archives','{$url}archives.php',1,30);";
	$q[]= "INSERT INTO {$pfx}links VALUES (3,'RSS 2.0 Feed','{$url}rss.php?ver=2',1,40);";
        $q[]="INSERT INTO {$pfx}links VALUES (4,'bBlog Dev','http://dev2.bblog.com/',2,50);";
	$q[]="INSERT INTO {$pfx}links VALUES (5, 'Eadz::Blog','http://www.eadz.co.nz/blog/',2,60);";
        $q[]="CREATE TABLE {$pfx}rss (
  id int(11) NOT NULL auto_increment,
  url text NOT NULL,
  input_charset text NOT NULL,
  PRIMARY KEY  (id)
) TYPE=MyISAM;";
        $q[]="INSERT INTO {$pfx}rss VALUES ('','http://www.bblog.com/rdf.php','I88592');";
        $q[]="INSERT INTO {$pfx}rss VALUES ('','','I88592');";
	$q[]="INSERT INTO {$pfx}rss VALUES ('','','I88592');";
	$q[]="INSERT INTO {$pfx}rss VALUES ('','','I88592');";
	$q[]="INSERT INTO {$pfx}rss VALUES ('','','I88592');";
	$q[]="INSERT INTO {$pfx}rss VALUES ('','','I88592');";
	$q[]="INSERT INTO {$pfx}rss VALUES ('','','I88592');";
	$q[]="INSERT INTO {$pfx}rss VALUES ('','','I88592');";
	$q[]="INSERT INTO {$pfx}rss VALUES ('','','I88592');";


   foreach($q as $q2do) {
 	$db->query($q2do);
	echo ".";
   }
   echo " Done. </p>";
   echo "<h3>Updating Data</h3><p>";
   echo "done";
   $db->query("UPDATE {$pfx}posts SET ownerid=1");
   echo "<h3>Adding default author</h3><p>";
   $db->query("INSERT INTO `{$pfx}authors` (`nickname`,`password`) VALUES
    ('".$config['username']."','".$config['password']."');");   
   echo "Done<br /><a href='install.php'>Click here to continue</a>";
   $step = 5; // skip creating tables

}

//function upgrade_from_bblog06_post() {
// things to say / do after the config file is written, and the database stuff done.
// maybe append to $config['extra_config'] for extra config to be written to the config file.

//}

?>
