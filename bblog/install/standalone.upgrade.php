<?php
/**
 * standalone.upgrade.php - The bBlog standalone upgrade script
 *
 * This will be the default standard upgrader, which will
 * include all patches to previous versions. It mainly has to do with updates
 * regarding the DB. And can also be used as a stand alone upgrader, without
 * going through the installer. (but i disabled the header now coz its duplicate)
 *
 * Note: This file is stable enough to *work*, and enter testing.
 * It is reverse compatible down to 0.7.2. I decided not to put in the 0.6
 * and below patches because we all decided such versions are too old
 * and unsupported.. And besides.. that means another ~400 lines extra.
 *
 * @todo I just noticed a nice idea which will greatly reduce the complexity and size
 * @todo of the code. The upgrader doesnt need any versions. It doesnt need to know
 * @todo anything from you except 2 things.
 * @todo 1) What the last version of the db looks like, and
 * @todo 2) What your current db looks like.
 * @todo 
 * @todo If anything is different/missing, then it patches and updates. Thats really it.
 * @todo I mean it will look similar to the install php, but with each querry protected by
 * @todo a check to see if what you have is old, missing, or the same. At the very end
 * @todo of the patcher, you make a $db->query($q);, instead of several small ones like
 * @todo now.
 * @todo 
 * @todo This idea occured to me while merging 0.6, because half of the db was missing,
 * @todo and 0.7.2 corrected it by doing the exact method just explained. The same could
 * @todo be expanded to the whole upgrade proccess.
 *
 * @package bBlog
 * @author xushi <xushi.xushi@gmail.com>, http://www.bblog.com/ - last modified by $LastChangedBy: $
 * @version $Id: $
 * @copyright The bBlog Project, http://www.bblog.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */


    // @todo close any sessions that might still be open (as a precaution)
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

	// xushi: commented because its being duplicated if we run from within installer. 
    // The header
    //if (file_exists('header.php')) {
    //    include 'header.php';
    //}


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
<h2>Universal Upgrader v0.8-r1</h2>
</div>

<div style="width: 500px; margin-left: auto; margin-right: auto; margin-top: 80px;">
<?php

    global $db;

    // First of all, make sure the compiled_templates dir is writable
    if (!is_writable("../compiled_templates")) {
        die("Error: Unable to write to $dir. Please make sure its writable.");
    }



    // -----------------0.7.5 patches-----------------------
    // Introduces the UTF-8, and LTR/RTL template styles.

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


    


    /**
     * -----------------0.7.6 patches-----------------------
     * The only difference in the database between 0.7.5 and 
     * 0.7.6 is the addition of VERSION to T_CONFIG table.
     */
	
    echo "*** Checking if you need 0.7.6 upgrades...<br />";
    $ver = getVer();
    //$newVer = 0.76;
    if(isset($ver)) {
        // update
        echo "Found a previous version.<br /><br />";
    }
    else {
        // otherwise, write a new one
        echo "0.7.6 upgrades not found. Patching...<br /><br />";
        writeVer(0.76);
    }
	





    //-----------------0.8 patches-----------------------

    /** 
     * Ok.. the new code now should almost work flawlesly. I
     * kept a mix of both the old and new style of upgrading
     * for now until all the work is complete, then we decide
     * on which to use.
     */
     
    echo "*** Checking if you need password hashing...<br />";

    // @todo xushi: err.. do we really need this line?
    // $url = $config['url'];


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
            // @todo xushi: an if test is needed to make sure the passwords
            // @todo arn't re-hashed if the user refreshes the page.
            echo "*** Encrypting password...<br /><br />";
            $pass = $db->get_results("SELECT id,password FROM `".T_AUTHORS."` ");
            foreach($pass as $pw) {
                $replacePass = "UPDATE `".T_AUTHORS."`  SET password=sha1('{$pw->password}') where id={$pw->id};";
                $db->query($replacePass);
            }
        }
        else if($matches[1]==40) echo 'No need to update passwords.<br /><br />';
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
    echo "***Checking Q&A exists...<br />";
    $testIpDomain = $bBlog->get_results("SELECT ip_domain FROM ".T_AUTHORS."");
    if (isset($testIpDomain)){
    	//no updates necessary :)
        echo "IP Domain found. No update needed.<br /><br />";
    }
    else {
    	echo "Not found, patching...<br /><br />";
		$qq[] = "ALTER TABLE `".T_AUTHORS."` ADD `ip_domain` VARCHAR( 255 ) NOT NULL default ''";
	}


    // Create a new 'checkcode' table which will include
    // id, checksum, and timestamp.
    $qq[] = "CREATE TABLE IF NOT EXISTS `".T_CHECKCODE."` (
        `id` int(10) unsigned NOT NULL auto_increment,
        `checksum` varchar(255) NOT NULL default '',
        `timestamp` timestamp(14) NOT NULL,
        PRIMARY KEY  (`id`)
    ) TYPE=InnoDB;";

    /**
    * Insert these new values to the config table. They are related to the
    * New image stuff introduced in 0.8
    * to see if you have these updates installed or not, check if one of them
    * exists or not.
    *
    * edit: because the whole table arch has been changed, i think its easier to
    * just copy the relevent data, drop the table, and recreate it again...
    *
    * but let's add a check that will prevent this from being executed more than once
    * just incase a user refreshes.. otherwise he'll lose his configs.
    */
    
    $res = getVer();
    if($res !== "0.8") {
    	
    	echo "Updating config table...";
    	
    	// Step 1: copy everything from bb_config into an array.
    	
    	/**
    	 * @todo i will use this one later.
    	
    	$config_vals = array(
            'blogname'=>'Blog name',
            'blogdescription' => 'Blog description',
            'username' => 'Username',
            'password'=> 'Password',
            'secondPassword' => 'Second password',
            'secretQuestion' => 'Secret question',
            'secretAnswer' => 'Secret answer',
            'email' => $db->get_var("SELECT value FROM ".T_CONFIG." WHERE name='EMAIL'"),
            'bblogemail' => $db->get_results("SELECT value FROM ".T_CONFIG." WHERE name='EMAIL'"),
            'fullname' => 'Full name',
            'mysql_username' => 'MySQL Username',
            'mysql_password' => 'MySQL Password',
            'mysql_database' => 'MySQL Database',
            'mysql_host' => 'MySQL Host',
            'table_prefix' => 'MySQL Table prefix'
        );
        */
    	
		$config_vals['email'] = $db->get_var("SELECT value FROM ".T_CONFIG." WHERE name='EMAIL'");
		$config_vals['blogname'] = $db->get_var("SELECT value FROM ".T_CONFIG." WHERE name='BLOGNAME'");
		$config_vals['template'] = $db->get_var("SELECT value FROM ".T_CONFIG." WHERE name='TEMPLATE'");
		$config_vals['db_templates'] = $db->get_var("SELECT value FROM ".T_CONFIG." WHERE name='DB_TEMPLATES'");
		$config_vals['default_modifier'] = $db->get_var("SELECT value FROM ".T_CONFIG." WHERE name='DEFAULT_MODIFIER'");
    	$config_vals['charset'] = $db->get_var("SELECT value FROM ".T_CONFIG." WHERE name='CHARSET'");
    	$config_vals['version'] = $db->get_var("SELECT value FROM ".T_CONFIG." WHERE name='VERSION'");
    	$config_vals['direction'] = $db->get_var("SELECT value FROM ".T_CONFIG." WHERE name='DIRECTION'");
    	$config_vals['default_status'] = $db->get_var("SELECT value FROM ".T_CONFIG." WHERE name='DEFAULT_STATUS'");
    	$config_vals['ping'] = $db->get_var("SELECT value FROM ".T_CONFIG." WHERE name='PING'");
    	$config_vals['comment_time_limit'] = $db->get_var("SELECT value FROM ".T_CONFIG." WHERE name='COMMENT_TIME_LIMIT'");
    	$config_vals['notify'] = $db->get_var("SELECT value FROM ".T_CONFIG." WHERE name='NOTIFY'");
    	$config_vals['blog_description'] = $db->get_var("SELECT value FROM ".T_CONFIG." WHERE name='BLOG_DESCRIPTION'");
    	$config_vals['comment_time_limit'] = $db->get_var("SELECT value FROM ".T_CONFIG." WHERE name='COMMENT_TIME_LIMIT'");
    	$config_vals['meta_description'] = $db->get_var("SELECT value FROM ".T_CONFIG." WHERE name='META_DESCRIPTION'");
    	$config_vals['meta_keywords'] = $db->get_var("SELECT value FROM ".T_CONFIG." WHERE name='META_KEYWORDS'");
    	
    	
    	// Stage 2: drop the table and create a new one
		$conftable[]="DROP TABLE IF EXISTS `".T_CONFIG."`;";
		$conftable[]="CREATE TABLE `".T_CONFIG."` (
			`id` int(11) NOT NULL auto_increment,
			`name` varchar(50) NOT NULL default '',
			`value` TEXT NOT NULL default '',
			`label` varchar(100) NOT NULL default '',
			`type` varchar(25) NOT NULL default '',
			`possible` varchar(100) NOT NULL default '',
		PRIMARY KEY  (`id`)
		) TYPE=MyISAM;";
		
		foreach($conftable as $conftable2do) {
    		$db->query($conftable2do);
		}
    	
    	
    	// Stage 3: Mass Alien Population
    	// also, regenerate LAST_MODIFIED, and add the extra 0.8 configs too...
		$qq[] = "INSERT INTO `".T_CONFIG."` (`id`, `name`, `value`, `label`, `type`, `possible`) VALUES
			('', 'EMAIL', '".$config_vals['email']."', 'Blog Main Email', 'text', ''),
			('', 'BLOGNAME', '".$config_vals['blogname']."', 'Blog Name', 'text', ''),
			('', 'TEMPLATE', '".$config_vals['template']."', 'bBlog Template', 'select', 'template'),
			('', 'DB_TEMPLATES', '".$config_vals['db_templates']."', '', '', ''),
			('', 'DEFAULT_MODIFIER', '".$config_vals['default_modifier']."', 'Default Modifier', 'select', 'modifier'),
			('', 'CHARSET', '".$config_vals['charset']."', '', '', ''),
			('', 'VERSION', '".$config_vals['version']."', '', '', ''),
			('', 'DIRECTION', '".$config_vals['direction']."', '', '', ''),
			('', 'DEFAULT_STATUS', '".$config_vals['default_status']."', 'Default Post Status', 'select', 'Array(\"live\",\"draft\")'),
			('', 'PING','".$config_vals['ping']."', '', '', ''),
			('', 'NOTIFY','".$config_vals['notify']."', 'Send notifications via email for new comments', 'select', 'Array(\"true\"=>\"Yes\",\"false\"=>\"No\")'),
			('', 'BLOG_DESCRIPTION', '".$config_vals['blog_description']."', 'Blog Description', 'text', ''),
			('', 'COMMENT_TIME_LIMIT','".$config_vals['comment_time_limit']."', 'Comment Flood Protection ( minutes ) Set to 0 to disable.', 'text', ''), 
			('', 'META_DESCRIPTION','".$config_vals['meta_description']."', 'META Description for search engines', 'text', ''),
			('', 'META_KEYWORDS','".$config_vals['meta_keywords']."', '', '', ''),	
	
    	    ('', 'COMMENT_TIME', '1', '', '', ''),
    	    ('', 'SMARTY_TAGS_IN_POST','false', 'Allow Smarty Tags', 'select', 'Array(\"true\"=>\"Yes\",\"false\"=>\"No\")'),
    	    ('', 'CUSTOMURLS','false', 'Use Custom urls e.g. /post/about-me.html - you enter about-me.html in the post screen', 'select', 'Array(\"true\"=>\"Yes\",\"false\"=>\"No\")'),
    	    ('', 'CLEANURLS','false', 'Use clean urls e.g. /post/1/ instead of ?postid=1, you have to put the .htaccess file in place.', 'select', 'Array(\"true\"=>\"Yes\",\"false\"=>\"No\")'),
    	    ('', 'IMAGE_VERIFICATION','false', 'Use Image verification to stop comment spam ( RECOMMENDED! ) - requires php with zlib support ( try it out most hosts support it )', 'select', 'Array(\"true\"=>\"Yes\",\"false\"=>\"No\")'),
			('', 'WYSIWYG','false', 'WYSIWYG editor', 'select', 'Array(\"true\"=>\"Yes\",\"false\"=>\"No\")'),
			('', 'FANCYURL', 'false', 'Fancy url', 'select', 'Array(\"true\"=>\"Yes\",\"false\"=>\"No\")'),
			('', 'LOCALE', '', '', '', ''),
			
			('', 'LAST_MODIFIED', UNIX_TIMESTAMP(), '', '', '');"; 
			// @todo xushi: is the LAST_MODIFIED of any use? and does it work in windows?
			
			echo " Done.<br /><br />";
	} // end if (for the 0.8 check)
	else {
		echo "Config table seems up to date.<br /><br />";
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



    // @todo: undecided if we are sticking in photoblog or not...
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

    /**
    * @todo I'm going to just copy the code here for now
    * @todo from bBlog_plugins/builtin.plugins.php
    * @todo coz for the life of me i can't get it to
    * @todo call the function from there instead..
	*/
    
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
    // @todo add check to see if pagename and fancyurl exist in T_POST or not.
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

    echo "<br /><br /><h3>Finished.</h3>";
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
    global $db;
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

/**
 * will use the above 3 functions to check and update/create
 * unused yet
 
function doVer($newVersion)
{
	$ver = getVer();
    $newVer = $newVersion;
    if(isset($ver)) {
        // update
        echo "Found a previous version. Updating to ".$newVer." now.<br /><br />";
		updateVer($newVer);
    }
    else {
        // otherwise, write a new one
        echo $newVer. "upgrades not found. Patching...<br /><br />";
        writeVer($newVer);
    }
}
 */
 

?>
</div>
</body>
</html>
