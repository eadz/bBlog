<?php
/**
 * xushi: notes
 * 
 * remove the bottom comments and stick in a phpDoc or somewhere..
 * its too much bulk here..
 * 
 * This file will be stuck in the install/ folder
 * from now on. Let everything to do with installation
 * and upgrading be packed in one area/folder.
 * 
 * im thinking of reducing the GUI installation steps
 * from 6 pages/steps, down to 3.
 * 1) The 'file permissions' can be binded to the 'agree'
 * button on the first page (with the licence)
 * 2) The second page will be the one asking the user to
 * fill his details for the installation
 * 3) All the other installation info can be grouped to a
 * third single page.
 * 
 * i like how in 0.8, by default, a new install is determined by 
 * weather you have a config.php or not.
 * 
 */


/**
 * install.php - bBlog installer
 * install.php - author: Eaden McKee <email@eadz.co.nz>
 *
 * bBlog Weblog http://www.bblog.com/
 * Copyright (C) 2003  Eaden McKee <email@eadz.co.nz>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
**/

// using sessions becasue it makes things easy.
session_start();

/* Experimental setting to delete install folder and chmod config.php at the
	end of the install procedure 
	Now enabled by default
*/

	$clean_install = 1;

	// start install all over, forget everything.
	if (isset($_GET['reset'])) {
		unset($config);
		unset($step);
		@session_destroy();
		header("Location: install.php");
		exit;
	}

	$step =& $_SESSION['step'];
	$config =& $_SESSION['config'];

	if(!isset($_SESSION['step'])) $step=0;

	// provide some useful defaults, and prevents undefined indexes.

	if(!isset($config['path'])) $config['path'] = dirname(__FILE__).'/';
	if(!isset($config['url'])) $config['url'] = 'http://'.$_SERVER['HTTP_HOST'].str_replace('bblog/install.php','',$_SERVER['REQUEST_URI']);
	if(!isset($config['mysql_host'])) $config['mysql_host'] = 'localhost';
	if(!isset($config['username'])) $config['username'] = 'admin';
	if(!isset($config['table_prefix'])) $config['table_prefix'] = 'bb_';
	if(!isset($config['password'])) $config['password'] = "";
	if(!isset($config['secondPassword'])) $config['secondPassword'] = "";
	if(!isset($config['email'])) $config['email'] = "";
	if(!isset($config['fullname'])) $config['fullname'] = "";
	if(!isset($config['mysql_username'])) $config['mysql_username'] = "";
	if(!isset($config['mysql_password'])) $config['mysql_password'] = "";
	if(!isset($config['mysql_database'])) $config['mysql_database'] = "";
	if(!isset($config['blogname'])) $config['blogname'] = "";
	if(!isset($config['blogdescription'])) $config['blogdescription'] = "";
	
	$config['version'] = "0.8beta1";
	
	include './libs/ez_sql.php';
	include './install/steps.php';
	include './install/header.php';
	
	if($step > 2) {
		$db = new db($config['mysql_username'], $config['mysql_password'], $config['mysql_database'], $config['mysql_host']);

	}
	
	// i think we need to turn off the session before opening
	// the new upgrade script.
	if(isset($config['upgrade_from'])) {
		if(file_exists('./install/standalone.upgrade.php')) {
			include './install/standalone.upgrade.php';
		} else {
			echo "<h3>Error</h3>";
			echo "<p>You have chosen an upgrade option, but the upgrade file (  install/standalone.upgrade.php ) is missing";
			include 'install/footer.php';
			exit;
		}
	}
	
	switch ($step) {
		case 0:
			?>
			<h3>Welcome to the bBlog installer</h3>
			<br />
			<?php if(isset($message)) echo $message; ?>
			<h4>Introduction</h4>
			<p>Welcome to the bBlog installer. If you get stuck, please read the documentation at http://www.bblog.com/wiki and visit the forum at http://www.bblog.com/forum.php<br />
			One thing to note: this installer uses sessions, so if you have disabled cookies, please re-enable them.</p>
			<h4>Licence Agreement</h4>
				<p>First things first, the licence agreement:</p>
				<textarea rows="8" cols="80" style="border: 2px dotted #333; background: #f0f0f0; font-size:10px;" readonly><?php include 'docs/LICENCE.txt'; ?></textarea>
				<p><input type="checkbox" class="checkbox" name="agree" value="yes"> I agree to these terms </p>
			<h4>Install Type</h4>
			<ul class="form">
				<li><input type="radio" class="radio" name="install_type" value="fresh" checked="checked" onClick=" document.forms.install.elements['upgrade_from'].disabled = true;" /> Fresh Install</li>
				<li><input type="radio" class="radio" name="install_type" value="upgrade" onClick=" document.forms.install.elements['upgrade_from'].disabled = false;" /> Upgrade from
				<select name="upgrade_from" id="upgrade_from" disabled="">
					<option value="bblog07">bBlog 0.7</option>
				</select>
			</ul>

		<div class='frame'><input type="submit" value="Next &gt;" name="submit" /></div>
		
		<?php
		break;
		
			
		/**
		 * Case 1: Find out if the user is installing a new version,
		 * or upgrading from another one.
		 */ 
		
		case 1:
			if ((isset($config['install_type'])) && ($config['install_type'] == 'upgrade')) {
				echo "<h3>Upgrading</h3>";
				// Since 0.7.4 had a default of 'bB_' as the table prefix, we'll try and maintain that.
				//xushi: i dont think this is a good idea.. better to stick to {pfx}
				$config['table_prefix'] = 'bB_';
				$intro_func = 'upgrade_from_'.$config['upgrade_from'].'_intro';
				if(function_exists($intro_func)) $intro_func();
			}
			?>
			<h3>File and Folder Permissions</h3>
				<p>bBlog needs to be able to write to disk to store its cache of templates.  This is also required if you want to use the blo.gs favorites functionality.</p>
				<p>We will now check the permissions of the 'cache' folder, the 'compiled_templates' folder, the 'pbimages' folder, and the 'cache/favorites.xml' file and create them if they do not exist.</p>
				<p>If this process does not succeed.  You will need to change the permissions manually.  This will involve chmodding the folders and files with your ftp client (if you're not using ftp you probally know what do do here). Permissions should either be 775 or 777.</p>
				<p>Additionally, ./config.php should be writable during the install. At the end of the install when the config file is written to disk, you should change the permissions back so it is not writable by the webserver.</p>
			<?php
			$test = check_writable();
			if($test) echo "<p>Great, all working. </p> <p><input type='submit' name='continue' value='Click here to continue' /></p>";
			else echo "<p>Please fix above errors, then <input type='submit' name='continue' value='Click here to try again' /></p>";

		break;
		
	
		/**
		 * Case 2, If user is installing from scratch, 
		 * provide the DB & Blog settings page
		 */
		
		case 2:
			?>
			<h3>Database and blog settings</h3>
			<?php 
			if (isset($message)) {
				echo $message; 
			}
			?>
			<p>Please fill in the config settings below</p>
			
			<table border="0" class='list' cellpadding="4" cellspacing="0">
			<tr>
				<td colspan="3"><h4>General Config</h4></td>
			</tr>
			<tr bgcolor="#eeeedf">
				<td width="33%">Blog Name</td>
				<td width="200"><input type="text" name="blogname" value="<?php echo $config['blogname']; ?>" /></td>
				<td  width="33%" class='si'>A short name for your blog, e.g. "My Blog"</td>
			</tr>
			<tr bgcolor="#eeeeee">
				<td width="33%" bgcolor="#eeeeee">Blog Description</td>
				<td width="200"><input type="text" name="blogdescription" value="<?php echo $config['blogdescription']; ?>"/>
			</td>
			<td  width="33%" class='si'>A short descriptive subtitle e.g. "A blog about fish"</td>
			</tr>
			<tr bgcolor="#eeeedf">
				<td width="33%" bgcolor="#eeeedf">Full Name</td>
				<td><input type="text" name="fullname" value="<?php echo $config['fullname']; ?>"/></td>
				<td class='si'>The owners full name </td>
			</tr>
			<tr bgcolor="#eeeedf">
				<td width="33%" bgcolor="#eeeeee">Username</td>
				<td width="200"><input type="text" name="username" value="<?php echo $config['username']; ?>"/></td>
				<td width="33%" class='si'>The username you want to use to log in to bBlog</td>
			</tr>
			<tr bgcolor="#eeeeee">
				<td width="33%" bgcolor="#eeeedf">Password</td>
				<td width="200"><input type="password" name="password" value="<?php echo $config['password']; ?>"/></td>
				<td width="33%" class='si'>The password you want to use to log in to bBlog</td>
			</tr>
				<tr bgcolor="#eeeeee">
				<td width="33%" bgcolor="#eeeedf">Re-enter Password</td>
				<td width="200"><input type="password" name="secondPassword" value="<?php echo $config['secondPassword']; ?>"/></td>
				<td width="33%" class='si'>Please re-enter the password.</td>
			</tr>
			<tr bgcolor="#eeeedf">
				<td width="33%" bgcolor="#eeeeee">Email Address</td>
				<td width="200"><input type="text" name="email" value="<?php echo $config['email']; ?>"/></td>
				<td width="33%" class='si'>Where to send notifications of comments</td>
			</tr>
			<tr>
				<td colspan="3"><h4>MySQL Settings</h4></td>
			</tr>
			</td>
			<tr bgcolor="#eeeedf">
				<td width="33%">MySQL Username</td>
				<td width="200"><input type="text" name="mysql_username" value="<?php echo $config['mysql_username']; ?>"/></td>
				<td width="33%" class='si'>Your MySQL username</td>
			</tr>
			<tr bgcolor="#eeeeee">
				<td width="33%">MySQL Password</td>
				<td width="200"><input type="password" name="mysql_password" value="<?php echo $config['mysql_password']; ?>" /></td>
				<td width="33%" class='si'>Your MySQL password</td>
			</tr>
			<tr bgcolor="#eeeedf">
				<td width="33%">MySQL Database Name</td>
				<td width="200"><input type="text" name="mysql_database" value="<?php echo $config['mysql_database']; ?>"/></td>
				<td width="33%" class='si'>Your MySQL database name<br>( usually the same as your username )</td>
			</tr>
			<tr bgcolor="#eeeeee">
				<td width="33%">Mysql Host</td>
				<td width="200"><input type="text" name="mysql_host" value="<?php echo $config['mysql_host']; ?>" /></td>
				<td width="33%" class='si'>The MySQL host name is usually 'localhost'</td>
			</tr>
			<tr bgcolor="#eeeedf">
				<td width="33%">Table Prefix</td>
				<td width="200"><input type="text" name="table_prefix" value="<?php echo $config['table_prefix']; ?>" /></td>
				<td width="33%" class='si'>Prefix of tables ( usually bb_ )</td>
			</tr>
			<tr>
				<td colspan="3"><h4>Server Settings</h4></td>
			</tr>
			<tr bgcolor="#eeeedf">
				<td width="33%">Url to your blog</td>
				<td width="200"><input type="text" name="url" value="<?php echo $config['url']; ?>" /></td>
				<td width="33%" class='si'>The full URL to your blog</td>
			</tr>
			<tr bgcolor="#eeeeee">
				<td width="33%">Path to bBlog</td>
				<td width="200"><input type="text" name="path" value="<?php echo $config['path']; ?>" /></td>
				<td width="33%" class='si'>The full UNIX path to the bblog directory</td>
			</tr>
			</table>
			<p><input type="submit" name="submit" value="Next &gt;" />
			<?php
		break;

		/**
		 * Case 3: However, if user is upgrading from a
		 * previous install, then run the upgrade script.
		 */
		
		case 3:
			
			$func = 'upgrade_from_'.$config['upgrade_from'].'_pre';
			if(function_exists($func)) {
				$func();
			} else {
				// this is really an error
				$step=5;
				echo "<p>Nothing to see here, <input type='submit' name='submit' value='Next &gt;' /></p>";
			}
			// upgrade.
			// if tables need to be created, such as MT or wordpress converstion, after this step go to step 4.
			// otherwise, in the case of a bBlog upgrade where tables _dont_ need to be created, go to step 5.
		break;

		
		/**
		 * Case 4: create the new tables, based on a fresh
		 * install of bblog.
		 */
		 
		case 4: // fresh install of bBlog
			// do sql.
			/**
			 * xushi: this will be rewritten.. Im thinking of a more
			 * functional approach to create the databases, in order
			 * to reduce code. Why?
			 * 
			 * The code here is 90% identical to the stuff found in the
			 * upgrader. The difference being that the upgrader has
			 * checks for every query its doing.
			 * So, Why write and debug 2 copies of code ?
			 * Stick the code in 1 place (functions?), and put a function to check
			 * if we are upgrading or installing... still thinking about it...
			 * 
			 * Oh.. by the way.. change `{pfx}xxx` to `".T_xxx."` when i can...
			 * 
			 */
			$q = array();
			/* Creating Tables */
			$pfx = $config['table_prefix'];
			$q[]="DROP TABLE IF EXISTS `{$pfx}comments;";
			$q[]="CREATE TABLE `{$pfx}comments` (
			  `commentid` int(10) unsigned NOT NULL auto_increment,
			  `parentid` int(10) unsigned NOT NULL default '0',
			  `postid` int(10) unsigned NOT NULL default '0',
			  `title` varchar(255) NOT NULL default '',
			  `type` enum('comment','trackback') NOT NULL default 'comment',
			  `posttime` int(11) default NULL,
			  `postername` varchar(100) NOT NULL default '',
			  `posteremail` varchar(100) NOT NULL default '',
			  `posterwebsite` varchar(255) NOT NULL default '',
			  `posternotify` tinyint(1) NOT NULL default '0',
			  `pubemail` tinyint(1) NOT NULL default '0',
 			 `pubwebsite` tinyint(1) NOT NULL default '0',
 			 `ip` varchar(16) NOT NULL default '',
			  `commenttext` text NOT NULL,
			  `deleted` enum('true','false') NOT NULL default 'false',
			  `onhold` tinyint(1) NOT NULL default '0',
			  PRIMARY KEY  (`commentid`),
			  FULLTEXT KEY `commenttext` (`commenttext`)
			) TYPE=MyISAM;";

			$q[]="DROP TABLE IF EXISTS `{$pfx}config;";
			$q[]="CREATE TABLE `{$pfx}config` (
			  `id` int(11) NOT NULL auto_increment,
			  `name` varchar(50) NOT NULL default '',
			  `value` varchar(255) NOT NULL default '',
			  PRIMARY KEY  (`id`)
			) TYPE=MyISAM;";

			$q[]="DROP TABLE IF EXISTS `{$pfx}plugins;";
			$q[]="CREATE TABLE `{$pfx}plugins` (
			  `id` int(11) NOT NULL auto_increment,
			  `type` varchar(50) NOT NULL default 'admin',
			  `name` varchar(60) NOT NULL default '',
			  `ordervalue` decimal(3,2) NOT NULL default '50.00',
			  `nicename` varchar(127) NOT NULL default '',
			 `description` text NOT NULL,
			  `template` varchar(100) NOT NULL default '',
			  `help` mediumtext NOT NULL,
			  `authors` varchar(255) NOT NULL default '',
			  `licence` varchar(50) NOT NULL default '',
			  PRIMARY KEY  (`id`)
			) TYPE=MyISAM;";

			$q[]="DROP TABLE IF EXISTS `{$pfx}posts;";
			$q[]="CREATE TABLE `{$pfx}posts` (
			  `postid` int(10) unsigned NOT NULL auto_increment,
			  `title` varchar(255) NOT NULL default '',
			  `body` mediumtext NOT NULL,
			  `posttime` int(11) NOT NULL default '0',
			  `modifytime` int(11) NOT NULL default '0',
			  `status` enum('live','draft') NOT NULL default 'live',
			  `modifier` varchar(30) NOT NULL default '',
			  `sections` varchar(255) NOT NULL default '',
			  `ownerid` int(10) NOT NULL default '0',
			  `hidefromhome` tinyint(1) NOT NULL default '0',
			  `allowcomments` enum('allow','timed','disallow') NOT NULL default 'allow',
			  `autodisabledate` int(11) NOT NULL default '0',
			  `commentcount` int(11) NOT NULL default '0',
			  `pagename` varchar(255) NOT NULL,
				`fancyurl` varchar(255) NOT NULL,
			  PRIMARY KEY  (`postid`),
			  KEY ownerid (ownerid)
			) TYPE=MyISAM;";

			$q[]="DROP TABLE IF EXISTS `{$pfx}sections;";
			$q[] = "CREATE TABLE `{$pfx}sections` (
			  `sectionid` int(11) NOT NULL auto_increment,
			  `nicename` varchar(255) NOT NULL default '',
			  `name` varchar(60) NOT NULL default '',
			  PRIMARY KEY  (`sectionid`)
			) TYPE=MyISAM ;";

			$q[]="DROP TABLE IF EXISTS `{$pfx}referers;";
			$q[] = "CREATE TABLE `{$pfx}referers` (
			 `visitID` int(11) NOT NULL auto_increment,
			 `visitTime` timestamp(14) NOT NULL,
			 `visitURL` char(250) default NULL,
			 `referingURL` char(250) default NULL,
			 `baseDomain` char(250) default NULL,
			 PRIMARY KEY  (`visitID`)
			) TYPE=MyISAM;";

			$q[]="DROP TABLE IF EXISTS `{$pfx}authors;";
			$q[] = "CREATE TABLE {$pfx}authors (
			  id int(10) NOT NULL auto_increment,
			  nickname varchar(20) NOT NULL default '',
			  email varchar(100) NOT NULL default '',
			  password varchar(40) NOT NULL default '',
			  fullname varchar(50) NOT NULL default '',
			  url varchar(50) NOT NULL default '',
			  icq int(10) unsigned NOT NULL default '0',
			  profession varchar(30) NOT NULL default '',
			  likes text NOT NULL,
			  dislikes text NOT NULL,
			  location varchar(25) NOT NULL default '',
			  aboutme text NOT NULL,
			  ip_domain VARCHAR( 255 ) NOT NULL,
			  PRIMARY KEY  (id)
			) TYPE=MyISAM;";

			$q[]="DROP TABLE IF EXISTS `{$pfx}rss;";
			$q[] = "CREATE TABLE `{$pfx}rss` (
			  `id` int(11) NOT NULL auto_increment,
			  `url` text NOT NULL,
			  `input_charset` text NOT NULL,
			  PRIMARY KEY  (`id`)
			) TYPE=MyISAM ";

			$q[]="DROP TABLE IF EXISTS `{$pfx}links;";
			$q[] = "CREATE TABLE {$pfx}links (
			  linkid int(11) NOT NULL auto_increment,
			  nicename varchar(255) NOT NULL,
			  url varchar(255) NOT NULL default '',
			  category int(11) NOT NULL, 
			  position int(8) NOT NULL default '10',
			  PRIMARY KEY  (linkid)) TYPE=MyISAM";
	
			$q[]="DROP TABLE IF EXISTS `{$pfx}categories;";
			$q[] = "CREATE TABLE {$pfx}categories (
			  categoryid int(11) NOT NULL auto_increment,
			  name varchar(60) NOT NULL, PRIMARY KEY  (categoryid)
			) TYPE=MyISAM";

			$q[]="DROP TABLE IF EXISTS `{$pfx}photobblog;";
			$q[] = "CREATE TABLE {$pfx}photobblog( 
			  postid int(11) NOT NULL default '0',
			  imageLoc varchar(20) NOT NULL default '',
			  caption varchar(255) NOT NULL default '',
			  UNIQUE KEY postid (postid)) TYPE=MyISAM";
	
			$q[]="DROP TABLE IF EXISTS `{$pfx}checkcode;";
			$q[] = "CREATE TABLE `{$pfx}checkcode` (
			  `id` int(10) unsigned NOT NULL auto_increment,
			  `checksum` varchar(255) NOT NULL default '',
			  `timestamp` timestamp(14) NOT NULL,
			  PRIMARY KEY  (`id`)
			) TYPE=InnoDB 
			AUTO_INCREMENT=0 ;";

			$q[]="DROP TABLE IF EXISTS `{$pfx}search;";
			$q[] = "CREATE TABLE `{$pfx}search` (
			  `id` bigint(20) unsigned NOT NULL auto_increment,
			  `article_id` int(10) unsigned NOT NULL default '0',
			  `value` varchar(255) NOT NULL default '',
			  `score` tinyint(3) unsigned NOT NULL default '0',
			  PRIMARY KEY  (`id`)
			) TYPE=InnoDB 
			AUTO_INCREMENT=0;";

			$q[]="DROP TABLE IF EXISTS `{$pfx}search_tmp;";
			$q[] = "CREATE TABLE `{$pfx}search_tmp` (
			  `id` int(10) unsigned NOT NULL auto_increment,
			  `string` varchar(255) NOT NULL default '',
			  `article_id` int(10) unsigned NOT NULL default '0',
			  `points` int(10) unsigned NOT NULL default '0',
			  `time` timestamp(14) NOT NULL,
			  PRIMARY KEY  (`id`)
			) TYPE=MyISAM 
			AUTO_INCREMENT=0 ;";

			$q[]="DROP TABLE IF EXISTS `{$pfx}external_content;";
			$q[] = "CREATE TABLE {$pfx}external_content (
			  id int(11) NOT NULL auto_increment,
			  nicename varchar(255) NOT NULL,
			  url varchar(255) NOT NULL default '',
			  enabled enum('true','false') NOT NULL default 'false',
			  PRIMARY KEY  (id)
			) TYPE=MyISAM";

			/* inserting data */
			$q[]= "INSERT INTO `{$pfx}rss` VALUES (9, '', '')";
			$q[]= "INSERT INTO `{$pfx}rss` VALUES (8, '', '')";
			$q[]= "INSERT INTO `{$pfx}rss` VALUES (7, '', '')";
			$q[]= "INSERT INTO `{$pfx}rss` VALUES (6, '', '')";
			$q[]= "INSERT INTO `{$pfx}rss` VALUES (5, '', '')";
			$q[]= "INSERT INTO `{$pfx}rss` VALUES (4, '', '')";
			$q[]= "INSERT INTO `{$pfx}rss` VALUES (3, '', '')";
			$q[]= "INSERT INTO `{$pfx}rss` VALUES (2, '', '')";
			$q[]= "INSERT INTO `{$pfx}rss` VALUES (1, 'http://www.bblog.com/rdf.php', 'I88592')";

			$q[]="INSERT INTO `{$pfx}config` (`id`, `name`, `value`) VALUES
			  ('', 'EMAIL', '".$config['email']."'),
			  ('', 'BLOGNAME', '".$config['blogname']."'),
			  ('', 'TEMPLATE', 'kubrick'),
			  ('', 'DB_TEMPLATES', 'false'),
			  ('', 'DEFAULT_MODIFIER', 'simple'),
			  ('', 'DEFAULT_STATUS', 'live'),
			  ('', 'PING','bblog.com/ping.php'),
			  ('', 'COMMENT_TIME_LIMIT','1'),
			  ('', 'NOTIFY','false'),
			  ('', 'BLOG_DESCRIPTION', '".$config['blogdescription']."'),
			  ('', 'COMMENT_MODERATION','none'),
			  ('', 'META_DESCRIPTION','Some words about this blog'),
			  ('', 'META_KEYWORDS','work,life,play,web design'),
			  ('', 'SMARTY_TAGS_IN_POST','false'),
			  ('', 'CUSTOMURLS','false'),
			  ('', 'CLEANURLS','false'),
			  ('', 'IMAGE_VERIFICATION','false'),
			  ('', 'WYSIWYG','false'),
			  ('', 'FANCYURL', 'false'),
			  ('', 'LAST_MODIFIED', UNIX_TIMESTAMP());";

			// Categories
			$q[] = "INSERT INTO {$pfx}categories VALUES (1,'Navigation');";
			$q[] = "INSERT INTO {$pfx}categories VALUES (2,'Blogs I read');";
			
			$url = $config['url'];
			$q[]= "INSERT INTO {$pfx}links VALUES (1,'Home','{$url}',1,20);";
			$q[]= "INSERT INTO {$pfx}links VALUES (2,'Archives','{$url}archives.php',1,30);";
			$q[]= "INSERT INTO {$pfx}links VALUES (3,'RSS 2.0 Feed','{$url}rss.php?ver=2',1,40);";
			$q[]="INSERT INTO {$pfx}links VALUES (4,'Webforce Blog','http://www.webforce.co.nz/blog/',2,50);";
        	
			
			// Only add new admin on a fresh install
			if(!isset($config['upgrade_from'])) {
				$q[]="INSERT INTO `{$pfx}authors` (`nickname`,`password`,`email`,`fullname`) VALUES
				('".$config['username']."','".sha1($config['password'])."','".$config['email']."','".$config['fullname']."');";
			}

			$q[] = "INSERT INTO `{$pfx}posts` (`postid`, `title`, `body`, `posttime`, `modifytime`, `status`, `modifier`, `sections`, `commentcount`,`ownerid`) VALUES (1, 'First Post', '[b]This is the first post of bBlog.[/b]\r\n\r\nYou may delete this post in the admin section. Make sure you have deleted the install file and changed the admin password. \r\n\r\nBe sure to visit the [url=http://www.bblog.com/forum.php]bBlog forum[/url] if you have any questions, comments, bug reports etc. \r\n\r\nHappy bBlogging!', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 'live', 'bbcode', '', 0, 1);";
			
			$q[] = "INSERT INTO `{$pfx}sections` (`sectionid`, `nicename`, `name`) VALUES (1, 'News', 'news'),
			  (2, 'Work', 'work'),
			  (3, 'Play', 'play');";

			$i=0;
			echo "<h3>Creating tables</h3><p>";
			$db = new db($config['mysql_username'],$config['mysql_password'],$config['mysql_database'],$config['mysql_host']);
			foreach($q as $q2do) {
				$i++;
				//echo $i." ";
				//echo "<pre>$q2do</pre>";
				$db->query($q2do);
			}
			echo 'Creating Tables: done.</p><p><input type="submit" name="submit" value="Next &gt;" /></p>';
			$step = 5;

		break;


		/**
		 *  Case 5: Scan and update all the plugins 
		 */
		case 5:
			/* update plugins */
			/**
			 * xushi: This code is horrible.. i just dont like it at all.
			 * This code needs to be independant.. in its own function
			 * somewhere else to be shared by the upgrader too.
			 * 
			 * So far, there are 3 copies of it to debug. Here, the upgrader,
			 * and in bBlog_plugins.
			 * 
			 */
			/* Scan for plugins */
			echo "<h3>Loading Plugins</h3>";
			$newplugincount = 0;
			$newpluginnames = array();
			$plugin_files=array();
			$dir="./bBlog_plugins";
			$dh = opendir( $dir ) or die("couldn't open directory");
			while ( ! ( ( $file = readdir( $dh ) ) === false ) ) {
				if(substr($file, -3) == 'php') $plugin_files[]=$file;
			}
			closedir( $dh );
			echo "<table border='0' class='list'>";
			foreach($plugin_files as $plugin_file) {
				$far = explode('.',$plugin_file);
				$type = $far[0];
				$name = $far[1];
				if($type != 'builtin') {
					include_once './bBlog_plugins/'.$plugin_file;
					$func = 'identify_'.$type.'_'.$name;
					if(function_exists($func)) {
						$newplugin = $func();
						
						if (!isset($newplugin['template'])) { $newplugin['template'] = ""; }
					
						$q = "insert into ".$config['table_prefix']."plugins set
						`type`='".$newplugin['type']."',
						`name`='".$newplugin['name']."',
						nicename='".$newplugin['nicename']."',
						description='".addslashes($newplugin['description'])."',
						template='".$newplugin['template']."',
						help='".addslashes($newplugin['help'])."',
						authors='".addslashes($newplugin['authors'])."',
						licence='".$newplugin['licence']."'";
				 		$db->query($q);
				 		echo '<tr><td>'.$newplugin['nicename'].'</td><td>..........Loaded</td></tr>';
	
					} // end if function exists
				} // end if
			} // end foreach
			echo "</table>";
			echo '<p>Done. <input type="submit" name="submit" value="Next &gt;" />';
			$func = 'upgrade_from_'.$config['upgrade_from'].'_post';
			if($config['install_type'] == 'upgrade' && function_exists($func)) $step = 6;
				else $step = 7;
		break;
		
		
		/**
		 * Case 6: post-install upgrade stuff,
		 * such as getting config to write, or giving hints.
		 */ 
		 
		/**
		 * xushi: This case is pointless and should be deleted.
		 */ 
		case 6:
			// post-install upgrade stuff, such as getting config to write, or giving hints.
			$func = 'upgrade_from_'.$config['upgrade_from'].'_post';
			$func();
		break;
		
		
		/**
		 * Case 7 : Finally, create and write the config.php file.
		 */ 
		case 7:
			// Write config!
			echo "<h3>Writing config.php file</h3>";
		
			if (!isset($config['extra_config'])) $config['extra_config'] = '';
		
		$config_file = "<?php
/**
 *
 *  '||     '||'''|,  '||`
 *   ||      ||   ||   ||
 *   ||''|,  ||;;;;    ||  .|''|, .|''|,
 *   ||  ||  ||   ||   ||  ||  || ||  ||
 *  .||..|' .||...|'  .||. `|..|' `|..||
 *                                    ||
 *         v1.0                    `..|'
 *
 *
 *  \$Id: install.php,v 1.77 2005/06/12 11:27:24 xushi Exp $ 
 * bBlog Weblog Software http://www.bblog.com/
 * Copyright (C) 2003-2004 Eaden McKee <email@eadz.co.nz>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version. 
 *
 * This program is distributed in the hope that it will be useful, 
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
**/

/* login details are stored in the database */



/* MySQL details */

// MySQL database username
define('DB_USERNAME','".$config['mysql_username']."');

// MySQL database password
define('DB_PASSWORD','".$config['mysql_password']."');

// MySQL database name
define('DB_DATABASE','".$config['mysql_database']."');

// MySQL hostname
define('DB_HOST','".$config['mysql_host']."');

// prefix for table names if you're installing
// more than one copy of bblog on the same database
// don't change this unless you know what you're doing.
define('TBL_PREFIX','".$config['table_prefix']."');

/* file and paths */

// Full path of the directory where you've installed bBlog
// ( i.e. the bblog folder )
define('BBLOGROOT','".$config['path']."');

/* URL config */

// URL to your blog ( one folder below the 'bBlog' folder )
// e.g, if your bBlog folder is at www.example.com/blog/bblog, your
// blog will be at www.example.com/blog/
define('BLOGURL','".$config['url']."');

// URL to the bblog folder via the web.
// Becasue if you're using clean urls and news.php as your BLOGURL,
// we can't automatically append bblog to it.
define('BBLOGURL',BLOGURL.'bblog/');

// Clean or messy urls ? ( READ README-URLS.txt ! )

define('C_CUSTOM_URL_POST','".$config['url']."item/%pagename%');
define('C_URL_POST','".$config['url']."item/%postid%/');
define('C_URL_SECTION','".$config['url']."section/%sectionname%/');

// bBlog ID is unical ID for security reasons
define('BBLOGID', '".md5(microtime().rand())."');

".$config['extra_config']."

// ---- end of config ----
// leave this line alone
include BBLOGROOT.'inc/init.php';
?>";
			$fp = fopen('./config.php', 'w');
			fwrite($fp, $config_file);
			fclose($fp);
			if (1 == $clean_install) {
				chmod('./config.php', 0644);
			}
			echo '<p>Config file written.</p><p><input type="submit" name="continue" value="Next &gt;" /></p>';
			$step = 8;
		break;

		
		// Case 8: Print out a few good messages to the user :)
		
		case 8:
			echo "<h3>All Done!</h3>";
			if (1 == $clean_install) {
				$clean = delete_install('install');
				if (! $clean) {
					echo "<p>The installer was unable to remove some install files.  It is advised that
					you delete the install/ directory in your bBlog installation folder as well as the install.php file.  You may also want to check that your config.php file is not world readable!</p>";
				}
				else {
					echo "<p>Installation complete!</p>";
				}
				echo "You may now <a href='index.php?b=options'>Login to bBLog. Be sure to visit the Options page to set your email address and other options.</a>";
			}
			else {
				echo "<p>Install finished, almost....
				<h3>Security</h3>
				<p>Now, you need to do 3 things to finish off
				<ol>
		    	<li>Delete install.php</li>
		    	<li>delete the install folder</li>
		    	<li>Chmod the config.php so that it is not writable by the webserver</li>
		    	<li>When you have done that, you may <a href='index.php?b=options'>Login to bBLog. Be sure to visit the Options page to set your email address and other options.</a></li>
					</ol></p>";
			}
		break;

	}

	if (file_exists('install/footer.php')) {
		include 'install/footer.php';
	}

	function make_dirs(){
		$dirs = array(
		  "./cache",
		  "./compiled_templates",
		  "./pbimages",
		  "./pbimages/thumbs",
		  "./files"
		);
		foreach ($dirs as $dir){
			if(!file_exists($dir)){
				if (!mkdir($dir, 0777)){
					echo "<p>Unable to create directory $dir with permissions 0777.</p>";
					return FALSE;
				}
				else if (!is_writable($dir)) {
					echo "<p>Unable to write to $dir</p>";
					return FALSE;
				}
			}
		}
		return TRUE;
	}

	function make_files(){
		$files = array(
		  "./cache/favorites.xml",
		  "./config.php"
		);
	
		foreach ($files as $file){
			if(!file_exists($file)){
				if ($handle = fopen($file, "w")) {
					fwrite($handle, '');
					chmod($file, 0777);
					fclose($handle);
				}
				else {
					echo "<p>Unable to create file $file</p>";
					return FALSE;
				}
			}
		}
		return TRUE;
	}

	function check_writable() {
		$ok = TRUE;
		if(is_writable("../bblog")) {
			echo "../bblog is writeable<br />";
		} else {
			echo "<span style='color:red;'>../bblog is NOT writable</span><br />";
			$ok = FALSE;
		}
	
		if (FALSE != $ok) {
			$ok = make_dirs();
		}
	
		if (FALSE != $ok) {
			$ok = make_files();
		}
		return $ok;

	}

	function delete_install($path) {
		$dir = opendir($path);
		while($file = readdir($dir)) {
			if (is_file("$path/$file")) {
				@unlink("$path/$file");
			}
			else if (is_dir("$path/$file") && $file !='.' && $file !='..') {
				delete_install("$path/$file");
			}
		}
		closedir($dir);
		return @rmdir($path);
	}
?>
