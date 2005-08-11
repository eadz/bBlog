<?php
/**
 * standalone.upgrade.php - The bBlog upgrade script
 * 
 * This will be the default standalone standard upgrader, which will 
 * include all patches to previous versions. It mainly has to do with updates
 * regarding the DB. And can also be used as a stand alone upgrader, without
 * going through the installer.
 * 
 * Note: This file is stable enough to *work*, and enter beta mode.
 * It is reverse compatible down to 0.7.2. I decided not to put in the 0.6
 * and below patches because we all decided such versions are too old
 * and unsupported.. And besides.. that means another ~400 lines extra.
 * 
 * EDIT:
 * -------------------------
 * I've just noticed a nice idea which will greatly reduce the complexity and size
 * of the code. The upgrader doesn't need any versions. It doesn't need to know
 * anything from you except 2 things.
 * - What the last version of the db looks like, and
 * - What your current db looks like.
 * 
 * If anything is different/missing, then it patches and updates. That's really it.
 * I mean it'll look similar to the install php, but with each querry protected by 
 * a check to see if what you have is old, missing, or the same. At the very end
 * of the patcher, you make a $db->query($q);, instead of several small ones like
 * now.
 * 
 * This idea occured to me while merging 0.6, because half of the db was missing, 
 * and 0.7.2 corrected it by doing the exact method just explained. The same could 
 * be expanded to the whole upgrade proccess.
 * 
 * @author - Xushi <xushi.xushi@gmail.com>
 * @license - GPL <http://www.gnu.org/copyleft/gpl.html>
 * @package bblog
 */


	//TODO: close any sessions that might still be open (as a precaution)
	//@session_destroy();
	
	/**
	 * Flyspray 70: die if config.php not found. This is useful for
	 * anyone who forgot to put it back when upgrading.
	 */	
	if(file_exists("../config.php")) {
		include '../config.php';
	} else {
		die("Error: config.php file not found. Make sure you place it back from your previous version, and try again");
	}
	
	// The header
	if (file_exists('header.php')) {
		include 'header.php';
	}
	
	
	// just a random number for now..
	$ver = 0.74;
	
	$db = new db(DB_USERNAME, DB_PASSWORD, DB_DATABASE, DB_HOST) or die("Error: config.php not found.");
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<title>bBlog upgrader</title>
	<link rel="stylesheet" type="text/css" title="Main" href="../style/admin.css" media="screen" />
</head>
<body><div id="header">
<h1>bBlog</h1>
<h2>Universal Upgrader v0.1</h2>
</div>

<div style="width: 500px; margin-left: auto; margin-right: auto; margin-top: 80px;">
<?php
	
	global $db;
	
	// First of all, make sure the compiled_templates dir is writable
	if (!is_writable("../compiled_templates")) {
		die("Error: Unable to write to $dir. Please make sure its writable.");
	}



	// -----------------0.7.5 patches-----------------------
	// Add a short summary here anyone...
	
	//Lets see if CHARSET and DIRECTION are already there..
	echo "***Checking if you need 0.7.5 upgrades...<br />";
	if (defined('C_CHARSET') && defined('C_DIRECTION') ){
		//no updates necessary :)
		echo "Your database looks good and does not need to be updated, yay! :)<br /><br />";
	}else{
		//we should add 2 values to db
		echo "0.7.5 upgrades not found. Patching...<br /><br />";
		$q = "INSERT INTO `".T_CONFIG."` (`id`, `name`, `value`) VALUES
		('', 'CHARSET', 'UTF-8'),
		('', 'DIRECTION', 'LTR')";
		
		//just do it
		$db->query($q);
	}// end else	
	
	// Update the version, but before you do, check if it actually exists
	// first, before adding it. If you do, *modify* instead, so you don't 
	// end up with 2 values
	$newVer = 0.75;
	if(isset($ver)) { 
		updateVer($newVer); 
	} else { 
		writeVer($newVer);
	}
	// -----------------------------------------------------



	/**
	 * -----------------0.7.6 patches-----------------------
	 * Currently, the only difference
	 * in the database between 0.7.5 and 0.7.6 is
	 * + The addition of VERSION to T_CONFIG table.
	 * I still think we should keep it for now. If ever we decide
	 * that we don't need it, then we can easly remove it.
	 */
	
	echo "*** Checking if you need 0.7.6 upgrades...<br />";
	$ver = $db->get_var("select value from ".T_CONFIG." where name='VERSION'");
	$newVer = 0.76;
	if(isset($ver)) {
		// update
		echo "Found a previous version. Updating to 0.7.6 now<br /><br />";
		$db->query("UPDATE ".T_CONFIG." SET VALUE='".$newVer."' WHERE `name`='VERSION'");
	} 
	else {
		// otherwise, write a new one 
		echo "0.7.6 upgrades not found. Patching...<br /><br />";
		$db->query("INSERT INTO `".T_CONFIG."` (`id`, `name`, `value`) VALUES
			('', 'VERSION', '$newVer')");
	}
	
	
	


	
	//-----------------0.8 patches-----------------------
	
	/** This still needs alot of work, to see wtf is actually
	 * in here, and to make sure it works.. its untested at
	 * the moment. I guarentee you it doesn't work, so don't even
	 * try and execute this function. 
	 *
	 * Edit: i'm renaming q[] to q8[] so it won't do the previous
	 * updates all over again...
	**/ 
	// note: we have 5 new tables.. add them to inc/... .php
	
	echo "*** Checking if you need 0.8 upgrades...<br />";
	
	// TODO: err.. do we really need this one?
	//$url = $config['url'];

	// Check first to see if varchar is 20, or 40.
	// So that we only have to update it once.
	// Thanks keefaz, kolba, DL8
	$matches = array();
	$password_type = '';  	
 	$len = $db->get_results("DESCRIBE ".T_AUTHORS." password");
 	
 	foreach($len as $row) {
    	if($row->Field=='password') {
            $password_type = $row->Type;
            break;
    	}
	}
	
	if(empty($password_type)) {
    	echo 'The password field type could not be found';
    	exit;
	}
	if(preg_match('/^varchar\((\d+)\)$/',$password_type,$matches)) {
		if($matches[1]==20) {
	    	echo "varchar(20). Patching...<br /><br />";
            // stick this into 1 line later on...
            $editPass = "ALTER TABLE `".T_AUTHORS."` MODIFY `password` varchar(40) NOT NULL";
            $db->query($editPass);
            
            
            // This bit will read in all the passwords in the author table 
			// and re-insert them with the sha1 function
			echo "*** Encrypting password...<br />";
			$pass = $db->get_results("SELECT id,password FROM `".T_AUTHORS."` ");
			foreach($pass as $pw) {
				$replacePass = "UPDATE `".T_AUTHORS."`  SET password=sha1('{$pw->password}') where id={$pw->id};";
				$db->query($replacePass);
			}
        }
        else if($matches[1]==40) echo 'No need for changes <br /><br />';
	}
	
	
		
	//lets see if you have SECRET_QUESTION or not.
	echo "***Checking Q&A exists...<br />";
	$testvar = $bBlog->get_results("select secret_question from ".T_AUTHORS."");
	if (isset($testvar)){
		//no updates necessary :)
		echo "Secret Q&A found. No patch needed.<br /><br />";
	}else{
		//we should add 2 values to db
		echo "Not found, patching...";
		
		// Add secret_question, secret_answer, and password_reset_request.
		// Since its an upgrade, the fields will be empty, and will be filled
		// by the user(s) in the option panel after their upgrade.
		$q[] = "ALTER TABLE `".T_AUTHORS."` ADD `secret_question` VARCHAR( 50 ) NOT NULL default ''";
		$q[] = "ALTER TABLE `".T_AUTHORS."` ADD `secret_answer` VARCHAR( 20 ) NOT NULL default ''";
		$q[] = "ALTER TABLE `".T_AUTHORS."` ADD `password_reset_request` int( 1 ) NOT NULL default '0'";
		
		//just do it
		foreach($q as $q2do) {
 		$db->query($q2do);
		}
		
		echo "Done.<br />Please change the Q&A when done.<br /><br />";
	}	
	
	
	// Add 'ip_domain' to the authors table.
	// TODO: i think i need a check here too
	$qq[] = "ALTER TABLE `".T_AUTHORS."` ADD `ip_domain` VARCHAR( 255 ) NOT NULL default ''";
	
	

	// Create a new 'checkcode' table which will include
	// id, checksum, and timestamp.
	$qq[] = "CREATE TABLE IF NOT EXISTS `".T_CHECKCODE."` (
		`id` int(10) unsigned NOT NULL auto_increment,
		`checksum` varchar(255) NOT NULL default '',
		`timestamp` timestamp(14) NOT NULL,
		PRIMARY KEY  (`id`)
	) TYPE=InnoDB;";

	// Insert these new values to the config table. They are related to the
	// New image stuff introduced in 0.8
	// to see if you have these updates installed or not, check if one of them
	// exists or not.
	$check = $db->get_var("select value from ".T_CONFIG." where name='WYSIWYG'");
	if(!isset($check)) {
		$qq[] = "INSERT INTO `".T_CONFIG."` (`id`, `name`, `value`) VALUES ('', 'COMMENT_TIME', '1')";
		$qq[] = "INSERT INTO `".T_CONFIG."` (`id`, `name`, `value`) VALUES ('', 'SMARTY_TAGS_IN_POST', 'false')";
		$qq[] = "INSERT INTO `".T_CONFIG."` (`id`, `name`, `value`) VALUES ('', 'CUSTOMURLS', 'false')";
		$qq[] = "INSERT INTO `".T_CONFIG."` (`id`, `name`, `value`) VALUES ('', 'CLEANURLS', 'false')";
		$qq[] = "INSERT INTO `".T_CONFIG."` (`id`, `name`, `value`) VALUES ('', 'IMAGE_VERIFICATION', 'false')";
		$qq[] = "INSERT INTO `".T_CONFIG."` (`id`, `name`, `value`) VALUES ('', 'WYSIWYG', 'false')";
		$qq[] = "INSERT INTO `".T_CONFIG."` (`id`, `name`, `value`) VALUES ('', 'FANCYURL', 'false')";
		$qq[] = "INSERT INTO `".T_CONFIG."` (`id`, `name`, `value`) VALUES ('', 'LOCALE', '')";
	}
	
	// Create a new table 'external_content' for .. something.. (help me out guys)
	// not creating this table for some reason...
	//-------------------------------------------
	$qq[] = "CREATE TABLE IF NOT EXISTS `".T_EXT_CONTENT."` (
		`id` int(11) NOT NULL auto_increment,
		`nicename` varchar(255) NOT NULL default '',
		`url` varchar(255) NOT NULL default='',
		`enabled` enum('true', 'false') NOT NULL default='false',
		PRIMARY KEY(`id`)
	) TYPE=MyISAM;";

	// undecided if we're sticking in photoblog or not...
	//---------------------------------------------------
	// Create a new table for the new photoblog.
	$qq[] = "CREATE TABLE IF NOT EXISTS `".T_PHOTOBLOG."` ( 
		`postid` int(11) NOT NULL default '0',
		`imageLoc` varchar(20) NOT NULL default '',
		`caption` varchar(255) NOT NULL default '',
		UNIQUE KEY `postid` (`postid`)
	) TYPE=MyISAM;";


	// delete all plugins from the plugin table
	$qq[] = "DELETE FROM `".T_PLUGINS."`";
	
	/* -----------------------------//
	//			update plugins		//
	/*------------------------------*/
	echo "*** Repopulating the plugins...<br /><br />";
	
	// I'm going to just copy the code here for now
	// from bBlog_plugins/builtin.plugins.php 
	// coz for the life of me i can't get it to
	// call the function from there instead..
	
	// WHY ISN'T IT WORKING ?!?!?!?! FFS
	
	echo "<h3>Loading Plugins</h3>";
		$newplugincount = 0;
		$newpluginnames = array();
		$plugin_files=array();
		$dir="../bBlog_plugins";
		$dh = opendir( $dir ) or die("Error: Could not open directory (".$dir.")");
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
				include_once '../bBlog_plugins/'.$plugin_file;
				$func = 'identify_'.$type.'_'.$name;
				if(function_exists($func)) {
					$newplugin = $func();
					
					if (!isset($newplugin['template'])) { $newplugin['template'] = ""; }
					
					$q = $db->query("insert into `".T_PLUGINS."` set
					`type`='".$newplugin['type']."',
					`name`='".$newplugin['name']."',
					nicename='".$newplugin['nicename']."',
					description='".addslashes($newplugin['description'])."',
					template='".$newplugin['template']."',
					help='".addslashes($newplugin['help'])."',
					authors='".addslashes($newplugin['authors'])."',
					licence='".$newplugin['licence']."'");
			 		
			 		echo '<tr><td>'.$newplugin['nicename'].'</td><td>..........Loaded</td></tr>';

				} // end if function exists
			} // end if
		} // end foreach
		echo "</table>";
	
    

	
	
    echo "<br /><br />";
	/*------------------------------//
	//			END PLUGIN UPDATE	//
	//------------------------------*/	
	
	
	// modify posts table to add 2 new fields
	// missing check here to see if they exist or not...
	//---------------------------------------------------
	$qq[] = "ALTER TABLE `".T_POSTS."` ADD `pagename` varchar(255) NOT NULL";
	$qq[] = "ALTER TABLE `".T_POSTS."` ADD `fancyurl` varchar(255) NOT NULL";

	// Create new 'search' table, fill it up with crap.
	$qq[] = "CREATE TABLE IF NOT EXISTS `".T_SEARCH."` (
		`id` bigint(20) unsigned NOT NULL auto_increment,
		`article_id` int(10) unsigned NOT NULL default '0',
		`value` varchar(255) NOT NULL default '',
		`score` tinyint(3) unsigned NOT NULL default '0',
		PRIMARY KEY  (`id`)
		) TYPE=InnoDB;";

	// Create new 'search_tmp' table, fill it up with crap.
	$qq[] = "CREATE TABLE IF NOT EXISTS `".T_SEARCH_TMP."` (
		`id` int(10) unsigned NOT NULL auto_increment,
		`string` varchar(255) NOT NULL default '',
		`article_id` int(10) unsigned NOT NULL default '0',
		`points` int(10) unsigned NOT NULL default '0',
		`time` timestamp(14) NOT NULL,
		PRIMARY KEY  (`id`)
		) TYPE=MyISAM;";

	// DO IT!! (uff..)
	foreach($qq as $qq2do) {
 		$db->query($qq2do);
	}

	
	
	
	
	// update version when done from everything else
	$newVer = 0.8;
	if(isset($ver)) { 
		updateVer($newVer); 
	} else { 
		writeVer($newVer);
	}





	// ---------------- All Done -------------------
	
 	echo "<br /><br /><h3>Done.</h3>";
	// add a check later on to see if it really did finish successfully or not
	echo "<p>The upgrade finished successfully.<br /><br />
		<h3><u>Security</u></h3>
		<p>Now, you need to do 3 things to finish off
		<ol>
	    	<li>Delete install.php and the install folder</li>
	    	<li>chmod -rw config.php, so that it is not writable by the webserver</li>
	    	<li>When you have done that, you may <a href='../index.php?b=options'>Login to bBLog.</a></li>	
		</ol></p><br /><br />";


	// The footer
	if (file_exists('footer.php')) {
		include 'footer.php';
	}

function getVer() {
	// check if VERSION exists in db
	return $db->get_var("select value from `".T_CONFIG."` where name='VERSION'");
}

function writeVer($version) {
	// create/write the new version into db
	global $db;
	$db->query("INSERT INTO `".T_CONFIG."` (`id`, `name`, `value`) VALUES
		('', 'VERSION', '$version')");
}

function updateVer($version) {
	// only update (overwrite) what's currently there
	global $db;
	$db->query("UPDATE ".T_CONFIG." SET VALUE='".$version."' WHERE `name`='VERSION'");
}


?>
</div>
</body>
</html>
