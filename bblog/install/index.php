<?php
/**
 * index.php - bBlog main installation file
 *
 * The bBlog installer!
 *
 * @todo note: <br />
 * ----- <br />
 * im thinking of reducing the GUI installation steps
 * from 6 pages/steps, down to 3. <br />
 * 1) The 'file permissions' can be binded to the 'agree'
 * button on the first page (with the licence) <br />
 * 2) The second page will be the one asking the user to
 * fill his details for the installation <br />
 * 3) All the other installation info can be grouped to a
 * third single page.
 *
 * note: <br />
 * ----- <br />
 * i like how in 0.8, by default, a new install is determined by
 * weather you have a full config.php or not.
 *
 * note: <br />
 * ------ <br />
 * Remove the switch case. Either stick all code in 1 blob, or replace
 * case with functions.. Atleast with functions you can return SUCCSES
 * or FAIL.
 * 
 * @todo Delete blogurl section from install page (i think i already did that..)
 *
 * @package bBlog
 * @author xushi <xushi.xushi@gmail.com> - last modified by $LastChangedBy: $
 * @version $Id: $
 * @copyright The bBlog Project, http://www.bblog.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */



// using sessions becasue it makes things easy.
session_start();


    // start install all over, forget everything.
    if (isset($_GET['reset'])) {
        unset($config);
        unset($step);
        @session_destroy();
        header("Location: index.php");
        exit;
    }

    $step =& $_SESSION['step'];
    $config =& $_SESSION['config'];

    if(!isset($_SESSION['step'])) $step=0;

    // provide some useful defaults, and prevents undefined indexes.

    if(!isset($config['path'])) $config['path'] = dirname(__FILE__).'/';
    if(!isset($config['url'])) $config['url'] = 'http://'.$_SERVER['HTTP_HOST'].str_replace('bblog/install/index.php','',$_SERVER['SCRIPT_NAME']);
    if(!isset($config['mysql_host'])) $config['mysql_host'] = 'localhost';
    if(!isset($config['username'])) $config['username'] = 'admin';
    if(!isset($config['table_prefix'])) $config['table_prefix'] = 'bb_';
    if(!isset($config['password'])) $config['password'] = "";
    if(!isset($config['secondPassword'])) $config['secondPassword'] = "";
    if(!isset($config['email'])) $config['email'] = "";
    if(!isset($config['fullname'])) $config['fullname'] = "";
    if(!isset($config['secretQuestion'])) $config['secretQuestion'] = "";
    if(!isset($config['secretAnswer'])) $config['secretAnswer'] = "";
    if(!isset($config['mysql_username'])) $config['mysql_username'] = "";
    if(!isset($config['mysql_password'])) $config['mysql_password'] = "";
    if(!isset($config['mysql_database'])) $config['mysql_database'] = "";
    if(!isset($config['blogname'])) $config['blogname'] = "";
    if(!isset($config['blogdescription'])) $config['blogdescription'] = "";
    if(!isset($config['bblogemail'])) $config['bblogemail'] = "";

    $config['version'] = "0.8beta2";

    include '../libs/ez_sql.php';
    include 'steps.php';
    include 'header.php';


    if($step > 2) {
        // construct a new db
        $db = new db($config['mysql_username'], $config['mysql_password'], $config['mysql_database'], $config['mysql_host']);

    }

    // i think we need to turn off the session before opening
    // the new upgrade script.
    if(isset($config['upgrade_from'])) {
        if(file_exists('upgrade.bblog07.php')) {
            include 'upgrade.bblog07.php';
        } else {
            echo "<h3>Error</h3>";
            echo "<p>You have chosen an upgrade option, but the upgrade file (  install/standalone.upgrade.php ) is missing";
            include 'footer.php';
            exit;
        }
    }

    switch ($step) {
        case 0:
            ?>
            <title>bBlog Installer</title>
            <h3>Welcome to the bBlog installer</h3>
            <br />
            <?php if(isset($message)) echo $message; ?>
            <h4>Introduction</h4>
            <p>Welcome to the bBlog installer. If you get stuck, please read the documentation at http://www.bblog.com/wiki and visit the forum at http://www.bblog.com/forum.php<br />
            One thing to note: this installer uses sessions, so if you have disabled cookies, please re-enable them.</p>
            <h4>Licence Agreement</h4>
                <p>First things first, the licence agreement:</p>
                <textarea rows="8" cols="80" style="border: 2px dotted #333; background: #f0f0f0; font-size:10px;" readonly><?php include '../docs/LICENCE.txt'; ?></textarea>
                <p><input type="checkbox" class="checkbox" name="agree" value="yes"/> I agree to these terms </p>
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
                //xushi: i dont think this is a good idea to give a static prefix (unless its just temporarely)
                $config['table_prefix'] = 'bB_';

                // unneeded now.. maby useful later? doubt it though..
                //$intro_func = 'upgrade_from_'.$config['upgrade_from'].'_intro';
                //if(function_exists($intro_func)) $intro_func();
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

            <table border="0" class='list' cellpadding="4" cellspacing="0" summary="Config Table">
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
                <td width="33%" bgcolor="#eeeeee">bBlog Email Address</td>
                <td width="200"><input type="text" name="bblogemail" value="<?php echo $config['bblogemail']; ?>"/></td>
                <td width="33%" class='si'>What address to use when sending emails to your users, such as password resets or notifications of comments</td>
            </tr>
            <tr>
                <td colspan="3"><h4>Admin Settings</h4></td>
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
                <td width="33%" class='si'>Your personal email address. Useful for password recovery.</td>
            </tr>
            <tr bgcolor="#eeeeee">
                <td width="33%">Secret Question</td>
                <td width="200"><input type="text" name="secretQuestion" value="<?php echo $config['secretQuestion']; ?>" /></td>
                <td width="33%" class='si'>Enter a secret question</td>
            </tr>
            <tr bgcolor="#eeeeee">
                <td width="33%">Secret Answer</td>
                <td width="200"><input type="password" name="secretAnswer" value="<?php echo $config['secretAnswer']; ?>" /></td>
                <td width="33%" class='si'>Secret Answer</td>
            </tr>
            <tr>
                <td colspan="3"><h4>MySQL Settings</h4></td>
            </tr>
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
            </table>
            <p><input type="submit" name="submit" value="Next &gt;" />
            <?php
        break;

        /**
         * Case 3: However, if user is upgrading from a
         * previous install, then run the upgrade script.
         */

        case 3:

            // unused anymore.. was for the old upgraders.
            //$func = 'upgrade_from_'.$config['upgrade_from'].'_pre';
            //if(function_exists($func)) {
            //	$func();
            //} else {
            //	// this is really an error
            //	$step=5;
            //	echo "<p>Nothing to see here, <input type='submit' name='submit' value='Next &gt;' /></p>";
            //}
            // upgrade.
            // if tables need to be created, such as MT or wordpress converstion, after this step go to step 4.
            // otherwise, in the case of a bBlog upgrade where tables _dont_ need to be created, go to step 5.
        break;


        /**
         * Case 4: create the new tables, based on a fresh
         * install of bblog.
         */

        case 4:
            /**
             * The database installer
             *
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
             * edit: Stick with {pfx}..., T_xxx is only used when we have a working
             * config.php .. my bad :)
             */

            $q = array();

            /* ----------------------------------------
                            Creating Tables
            ----------------------------------------- */

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
             `value` TEXT NOT NULL default '',
             `label` varchar(100) NOT NULL default '',
             `type` varchar(25) NOT NULL default '',
             `possible` varchar(100) NOT NULL default '',
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
	      `postcount` int(11) NOT NULL default '0',
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
              email varchar(50) NOT NULL default '',
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
              secret_question varchar(50) NOT NULL default '',
              secret_answer varchar(20) NOT NULL default '',
              password_reset_request int(1) NOT NULL default '0',
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



            /* ----------------------------------------
                            inserting data
            ----------------------------------------- */

            $q[]= "INSERT INTO `{$pfx}rss` VALUES (9, '', '')";
            $q[]= "INSERT INTO `{$pfx}rss` VALUES (8, '', '')";
            $q[]= "INSERT INTO `{$pfx}rss` VALUES (7, '', '')";
            $q[]= "INSERT INTO `{$pfx}rss` VALUES (6, '', '')";
            $q[]= "INSERT INTO `{$pfx}rss` VALUES (5, '', '')";
            $q[]= "INSERT INTO `{$pfx}rss` VALUES (4, '', '')";
            $q[]= "INSERT INTO `{$pfx}rss` VALUES (3, '', '')";
            $q[]= "INSERT INTO `{$pfx}rss` VALUES (2, '', '')";
            $q[]= "INSERT INTO `{$pfx}rss` VALUES (1, 'http://www.bblog.com/rdf.php', 'I88592')";

			// added telcor's new option patch.
			$q[]="INSERT INTO `{$pfx}config` (`id`, `name`, `value`, `label`, `type`, `possible`) VALUES
             ('', 'EMAIL', '".$config['bblogemail']."', 'Blog Main Email', 'text', ''),
             ('', 'BLOGNAME', '".$config['blogname']."', 'Blog Name', 'text', ''),
             ('', 'TEMPLATE', 'lines', 'bBlog Template', 'select', 'template'),
             ('', 'DB_TEMPLATES', 'false', '', '', ''),
             ('', 'DEFAULT_MODIFIER', 'simple', 'Default Modifier', 'select', 'modifier'),
             ('', 'CHARSET', 'UTF-8', '', '', ''),
             ('', 'VERSION', '0.8', '', '', ''),
             ('', 'DIRECTION', 'LTR', '', '', ''),
             ('', 'DEFAULT_STATUS', 'live', 'Default Post Status', 'select', 'Array(\"live\",\"draft\")'),
             ('', 'PING','bblog.com/ping.php', '', '', ''),
             ('', 'NOTIFY','false', 'Send notifications via email for new comments', 'select', 'Array(\"true\"=>\"Yes\",\"false\"=>\"No\")'),
             ('', 'BLOG_DESCRIPTION', '".$config['blogdescription']."', 'Blog Description', 'text', ''),
			 ('', 'COMMENT_TIME_LIMIT','1', 'Comment Flood Protection ( minutes ) Set to 0 to disable.', 'text', ''), 
             ('', 'META_DESCRIPTION','Some words about this blog', 'META Description for search engines', 'text', ''),
             ('', 'META_KEYWORDS','work,life,play,web design', '', '', ''),
             ('', 'COMMENT_TIME', '1', '', '', ''),
             ('', 'SMARTY_TAGS_IN_POST','false', 'Allow Smarty Tags', 'select', 'Array(\"true\"=>\"Yes\",\"false\"=>\"No\")'),
             ('', 'CUSTOMURLS','false', 'Use Custom urls e.g. /post/about-me.html - you enter about-me.html in the post screen', 'select', 'Array(\"true\"=>\"Yes\",\"false\"=>\"No\")'),
             ('', 'CLEANURLS','false', 'Use clean urls e.g. /post/1/ instead of ?postid=1, you have to put the .htaccess file in place.', 'select', 'Array(\"true\"=>\"Yes\",\"false\"=>\"No\")'),
             ('', 'IMAGE_VERIFICATION','false', 'Use Image verification to stop comment spam ( RECOMMENDED! ) - requires php with zlib support ( try it out most hosts support it )', 'select', 'Array(\"true\"=>\"Yes\",\"false\"=>\"No\")'),
             ('', 'WYSIWYG','false', 'WYSIWYG editor', 'select', 'Array(\"true\"=>\"Yes\",\"false\"=>\"No\")'),
             ('', 'FANCYURL', 'false', 'Fancy url', 'select', 'Array(\"true\"=>\"Yes\",\"false\"=>\"No\")'),
             ('', 'LOCALE', '', '', '', ''),
             ('', 'LAST_MODIFIED', UNIX_TIMESTAMP(), '', '', ''),
             ('', 'TIMEZONE', '0', 'Your Timezone', 'select', 'Array(\"12\"=>\"GMT\")'),
             ('', 'COMMENT_MODERATION', 'urlonly', 'Comment Moderation', 'select', 'Array(\"all\"=>\"Moderate All Comments\",\"none\"=>\"No Moderation\",\"urlonly\"=>\"Only comments with URLs\")');";
             
	     // left this here for now, but this info is too big for the field ( varchar(255) ) !!  Also, can't a function generate this like 'modifier' or 'template'?
             //('', 'TIMEZONE', '0', 'Your Timezone', 'select', 'Array(\"-12\"=>\"GMT - 12 Hours\",  \"-11\"=>\"GMT - 11 Hours\",  \"-10\"=>\"GMT - 10 Hours\",  \"-9\"=>\"GMT - 9 Hours\",  \"-8\"=>\"GMT - 8 Hours\",  \"-7\"=>\"GMT - 7 Hours\",  \"-6\"=>\"GMT - 6 Hours\",  \"-5\" selected=\"selected\"=>\"GMT - 5 Hours\",  \"-4\"=>\"GMT - 4 Hours\",  \"-3.5\"=>\"GMT - 3.5 Hours\",  \"-3\"=>\"GMT - 3 Hours\",  \"-2\"=>\"GMT - 2 Hours\",  \"-1\"=>\"GMT - 1 Hours\",  \"0\"=>\"GMT,  \"1\"=>\"GMT + 1 Hour,  \"2\"=>\"GMT + 2 Hours\",  \"3\"=>\"GMT + 3 Hours\",  \"3.5\"=>\"GMT + 3.5 Hours\",  \"4\"=>\"GMT + 4 Hours\",  \"4.5\"=>\"GMT + 4.5 Hours\",  \"5\"=>\"GMT + 5 Hours\",  \"5.5\"=>\"GMT + 5.5 Hours\",  \"6\"=>\"GMT + 6 Hours\",  \"6.5\"=>\"GMT + 6.5 Hours\",  \"7\"=>\"GMT + 7 Hours\",  \"8\"=>\"GMT + 8 Hours\",  \"9\"=>\"GMT + 9 Hours\",  \"9.5\"=>\"GMT + 9.5 Hours\",  \"10\"=>\"GMT + 10 Hours\",  \"11\"=>\"GMT + 11 Hours\",  \"12\"=>\"GMT + 12 Hours\",  \"13\"=>\"GMT + 13 Hours\")');";
	  

            // Categories
            $q[] = "INSERT INTO {$pfx}categories VALUES (1,'Navigation');";
            $q[] = "INSERT INTO {$pfx}categories VALUES (2,'Blogs I read');";

            $url = $config['url'];
            $q[]= "INSERT INTO {$pfx}links VALUES (1,'Home','{$url}',1,20);";
            $q[]= "INSERT INTO {$pfx}links VALUES (2,'Archives','{$url}archives.php',1,30);";
            $q[]= "INSERT INTO {$pfx}links VALUES (3,'RSS 2.0 Feed','{$url}rss.php?ver=2',1,40);";
            $q[]= "INSERT INTO {$pfx}links VALUES (4,'Webforce Blog','http://www.webforce.co.nz/blog/',2,50);";
			$q[]= "INSERT INTO {$pfx}links VALUES (5,'Xushi\'s Blog','http://www.xushi.co.uk/',2,60);";
			$q[]= "INSERT INTO {$pfx}links VALUES (6,'Hijacker\'s Blog','http://www.sjuengling.de/',2,70);";
			$q[]= "INSERT INTO {$pfx}links VALUES (7,'Telcor\'s Blog','http://blog.tel-cor.com/',2,80);";
			

            // Only add new admin on a fresh install
            if(!isset($config['upgrade_from'])) {
                $q[]="INSERT INTO `{$pfx}authors` (`nickname`,`password`,`email`,`fullname`,`secret_question`,`secret_answer`) VALUES
                ('".$config['username']."','".sha1($config['password'])."','".$config['email']."','".$config['fullname']."','".$config['secretQuestion']."','".$config['secretAnswer']."');";
            }


            $q[] = "INSERT INTO `{$pfx}posts` (`postid`, `title`, `body`, `posttime`, `modifytime`, `status`, `modifier`, `sections`, `commentcount`,`ownerid`) VALUES (1, 'First Post', '[b]This is the first post of bBlog.[/b]\r\n\r\nYou may delete this post in the admin section. Make sure you have deleted the install file and changed the admin password. \r\n\r\nBe sure to visit the [url=http://www.bblog.com/forum.php]bBlog forum[/url] if you have any questions, comments, bug reports etc. \r\n\r\nHappy bBlogging!', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 'live', 'bbcode', '', 0, 1);";

            $q[] = "INSERT INTO `{$pfx}sections` (`sectionid`, `nicename`, `name`) VALUES (1, 'News', 'news'),
              (2, 'Work', 'work'),
              (3, 'Play', 'play');";

            $i=0;
            //var_dump($q);
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
             * edit: err, i dont even think this works, let alone does anything..
             */
             
            /* Scan for plugins */
            echo "<h3>Loading Plugins</h3>";
            $newplugincount = 0;
            $newpluginnames = array();
            $plugin_files=array();
            $dir="../bBlog_plugins";
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
                    include_once '../bBlog_plugins/'.$plugin_file;
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

                        /**
                         * Stack Trace: Error at line 644 and 642
                         * <p>
                         * [client 127.0.0.1] PHP Stack trace:, referer: http://localhost/xushi/08/bblog/install/index.php
                         * [client 127.0.0.1] PHP   1. {main}() /home/xushi/public_html/08/bblog/install/index.php:0, referer: http://localhost/xushi/08/bblog/install/index.php
                         * [client 127.0.0.1] PHP Notice:  Undefined index:  licence in /home/xushi/public_html/08/bblog/install/index.php on line 644, referer: http://localhost/xushi/08/bblog/install/index.php
                         * <p>
                         * [client 127.0.0.1] PHP Stack trace:, referer: http://localhost/xushi/08/bblog/install/index.php
                         * [client 127.0.0.1] PHP   1. {main}() /home/xushi/public_html/08/bblog/install/index.php:0, referer: http://localhost/xushi/08/bblog/install/index.php
                         * [client 127.0.0.1] PHP Notice:  Undefined index:  help in /home/xushi/public_html/08/bblog/install/index.php on line 642, referer: http://localhost/xushi/08/bblog/install/index.php
                         * <p>
                         * [client 127.0.0.1] PHP Stack trace:, referer: http://localhost/xushi/08/bblog/install/index.php
                         * [client 127.0.0.1] PHP   1. {main}() /home/xushi/public_html/08/bblog/install/index.php:0, referer: http://localhost/xushi/08/bblog/install/index.php
                         * [client 127.0.0.1] PHP Notice:  Undefined index:  help in /home/xushi/public_html/08/bblog/install/index.php on line 642, referer: http://localhost/xushi/08/bblog/install/index.php
                         */

                    } // end if function exists
                } // end if
            } // end foreach
            echo "</table>";
            echo '<p>Done. <input type="submit" name="submit" value="Next &gt;" />';
            //$func = 'upgrade_from_'.$config['upgrade_from'].'_post';
            //if($config['install_type'] == 'upgrade' && function_exists($func)) $step = 6;
            //	else $step = 7;
            $step = 7;
        break;


        /**
         * Case 6: post-install upgrade stuff,
         * such as getting config to write, or giving hints.
         */

        /**
         * xushi: This case is pointless and should be deleted.
         */
        case 6:
            // post-install upgrade stuff, such as getting config to write, or giving hints. (unused)
            $func = 'upgrade_from_'.$config['upgrade_from'].'_post';
            $func();
        break;


        /**
         * Case 7 : Finally, create and write the config.php file.
         */
        case 7:
            // Write config!
            echo "<h3>Writing config.php file</h3>";
            $config['rootpath'] = dirname(dirname(__FILE__)).'/';
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
 *         v0.8.0                 `....|'
 *
 * @package bBlog
 * @author bBlog weblog
 * @copyright The bBlog Project, http://www.bblog.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

/* ************* */
/* MySQL details */
/* ************* */

// MySQL database username
define('DB_USERNAME','".$config['mysql_username']."');

// MySQL database password
define('DB_PASSWORD','".$config['mysql_password']."');

// MySQL database name
define('DB_DATABASE','".$config['mysql_database']."');

// MySQL hostname
define('DB_HOST','".$config['mysql_host']."');

/* prefix for table names if you're installing			*
 * more than one copy of bblog on the same database		*
 * don't change this unless you know what you're doing. */
define('TBL_PREFIX','".$config['table_prefix']."');


/* ************** */
/* file and paths */
/* ************** */

// Full path of the directory where you've installed bBlog ( i.e. the bblog folder )
define('BBLOGROOT','".$config['rootpath']."');


/* ********** */
/* URL config */
/* ********** */

/* URL to your blog ( one folder below the 'bBlog' folder )
 * e.g, if your bBlog folder is at www.example.com/blog/bblog, your
 * blog will be at www.example.com/blog/ */
define('BLOGURL','".$config['url']."');

/* URL to the bblog folder via the web.
 * Becasue if you're using clean urls and news.php as your BLOGURL,
 * we can't automatically append bblog to it. */
define('BBLOGURL',BLOGURL.'bblog/');

// Clean or messy urls ? ( READ README-URLS.txt ! )

define('C_CUSTOM_URL_POST',BLOGURL.'/item/%pagename%');
define('C_URL_POST',BLOGURL.'/item/%postid%/');
define('C_URL_SECTION',BLOGURL.'/section/%sectionname%/');

// bBlog ID is unical ID for security reasons
define('BBLOGID', '".md5(microtime().rand())."');

".$config['extra_config']."

// ---- end of config ----
// leave this line alone
include BBLOGROOT.'inc/init.php';
?>";
            $fp = fopen('../config.php', 'wb');
            fwrite($fp, $config_file);
            fclose($fp);

			// @todo auto chmod of config.php doesnt seem to work
            @chmod('../config.php', 0644);
            
            echo '<p>Config file written.</p><p><input type="submit" name="continue" value="Next &gt;" /></p>';
            $step = 8;
        break;


        // Case 8: Print out a few good messages to the user :)

        case 8:
            echo "<h3>All Done!</h3>";
            echo "<p>Install finished, almost....
                <h3>Security</h3>
                <p>Now, you need to do 3 things to finish off
                <ol>
                <li>Delete or rename the install folder</li>
                <li>Chmod the config.php so that it is not writable by the webserver</li>
                <li>When you have done that, you may <a href='../index.php?b=options'>Login to bBLog</a>. Be sure to visit the Options page to set your email address and other options.</li>
                </ol><br /><br />";
            
        break;
        
    }// end switch case
    
    

    if (file_exists('footer.php')) {
        include 'footer.php';
    }


    /**
     * Check Writable
     *
     * Checks if folders are writable or not.
     * Currently checks the bblog/ directory.
     */

     /**
      * Stack Trace: warnings found (for debugging)
      * The line numbers are a bit off coz of the copy/paste of these dumps.
      *
      * [client 127.0.0.1] PHP Stack trace:, referer: http://localhost/xushi/08/bblog/install/index.php
      * [client 127.0.0.1] PHP   1. {main}() bblog/install/index.php:0, referer: http://localhost/xushi/08/bblog/install/index.php
      * [client 127.0.0.1] PHP   2. delete_install() bblog/install/index.php:789, referer: http://localhost/xushi/08/bblog/install/index.php
      * [client 127.0.0.1] PHP   3. <a href='http://www.php.net/opendir' target='_new'>opendir</a>\n() bblog/install/index.php:920, referer: http://localhost/xushi/08/bblog/install/index.php
      * [client 127.0.0.1] PHP Warning:  opendir(install) [<a href='function.opendir'>function.opendir</a>]: failed to open dir: No such file or directory in /home/xushi/public_html/08/bblog/install/index.php on line 920, referer: http://localhost/xushi/08/bblog/install/index.php
      *
      * [client 127.0.0.1] PHP Stack trace:, referer: http://localhost/xushi/08/bblog/install/index.php
      * [client 127.0.0.1] PHP   1. {main}() /home/xushi/public_html/08/bblog/install/index.php:0, referer: http://localhost/xushi/08/bblog/install/index.php
      * [client 127.0.0.1] PHP   2. delete_install() /home/xushi/public_html/08/bblog/install/index.php:789, referer: http://localhost/xushi/08/bblog/install/index.php
      * [client 127.0.0.1] PHP   3. <a href='http://www.php.net/readdir' target='_new'>readdir</a>\n() /home/xushi/public_html/08/bblog/install/index.php:921, referer: http://localhost/xushi/08/bblog/install/index.php
      * [client 127.0.0.1] PHP Warning:  readdir(): supplied argument is not a valid Directory resource in /home/xushi/public_html/08/bblog/install/index.php on line 921, referer: http://localhost/xushi/08/bblog/install/index.php
      *
      * [client 127.0.0.1] PHP Stack trace:, referer: http://localhost/xushi/08/bblog/install/index.php
      * [client 127.0.0.1] PHP   1. {main}() bblog/install/index.php:0, referer: http://localhost/xushi/08/bblog/install/index.php
      * [client 127.0.0.1] PHP   2. delete_install() bblog/install/index.php:789, referer: http://localhost/xushi/08/bblog/install/index.php
      * [client 127.0.0.1] PHP   3. <a href='http://www.php.net/closedir' target='_new'>closedir</a>\n() bblog/install/index.php:929, referer: http://localhost/xushi/08/bblog/install/index.php
      * [client 127.0.0.1] PHP Warning:  closedir(): supplied argument is not a valid Directory resource in bblog/install/index.php on line 929, referer: http://localhost/xushi/08/bblog/install/index.php
      */
    function check_writable() {
        $ok = TRUE;

        if(is_writable("../compiled_templates")) {
            echo "../compiled_templates is writeable<br />";
        } else {
            echo "<span style='color:red;'>../compiled_templates is NOT writable</span><br />";
            $ok = FALSE;
        }

        if(is_writable("../config.php")) {
            echo "../config.php is writeable<br />";
        } else {
            echo "<span style='color:red;'>../config.php is NOT writable</span><br />";
            $ok = FALSE;
        }
        
        if(is_writable("../cache")) {
            echo "../cache/ is writeable<br />";
        } else {
            echo "<span style='color:red;'>../cache/ is NOT writable</span><br />";
            $ok = FALSE;
        }
        
        if(is_writable("../files")) {
            echo "../files/ is writeable<br />";
        } else {
            echo "<span style='color:red;'>../files/ is NOT writable</span><br />";
            $ok = FALSE;
        }
        
        return $ok;
    }

  
    function print_iis_message($msg){
        echo '<h5 style="color: red;">Unable to create '.htmlentities($msg).'.</h5><p>Your web server software is
        Microsoft IIS. The <strong>Internet Guest Account (IUSR_<em>servername</em>)</strong> must have
        the following permissions explicitly granted to the bblog folder:</p>
        <ul>
            <li>modify</li>
            <li>Read &amp; Execute</li>
            <li>List Folder Contents</li>
            <li>read</li>
            <li>write</li>
        </ul>
        <p><strong>NOTE</strong> Assigning the modify permission automatically assigns the lesser permissions. Make
        certain the child directories and files inherit the modify permissions (the default).</p>';
    }
?>
