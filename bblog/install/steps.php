<?php
/**
 * Checks needed for the installer, segmented into steps..
 *
 * @package bBlog
 * @author bBlog Weblog, http://www.bblog.com/ - last modified by $LastChangedBy: $
 * @version $Id: $
 * @copyright The bBlog Project, http://www.bblog.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */
 
// can you get to level 6?
switch($step) {
	case 0:
		// tests to get to next level :
		// 1 - agree to terms
		if ((!isset($_POST['agree']) || ($_POST['agree'] != 'yes'))) {
			if ((isset($_POST['submit'])) && ($_POST['submit'])) $message = "<p style='color:red;'>You must agree to the terms</p>";
			break;
		} 
		
		if (isset($_POST['install_type'])) {
			$config['install_type'] = $_POST['install_type'];
			
			if ($_POST['install_type'] == 'upgrade') {
				$config['upgrade_from'] = $_POST['upgrade_from'];
			}
		} else break;
				
		$step=1;
		break;
		
	case 1:
		// tests to get to next level
		// 1 - things need to be writable
		ob_start(); // we don't want any errors
		if(check_writable()) $step = 2;
		ob_end_clean();
		break;
	case 2:
		// tests : 
		// 1 - mysql connects
		// 2 - everything is set
		$missing_fields = '';
		$config_vals = array(
            'blogname'=>'Blog name',
            'blogdescription' => 'Blog description',
            'username' => 'Username',
            'password'=> 'Password',
            'secondPassword' => 'Second password',
            'secretQuestion' => 'Secret question',
            'secretAnswer' => 'Secret answer',
            'email' => 'Admins Email address',
            'bblogemail' => 'bBlog Email',
            'fullname' => 'Full name',
            'mysql_username' => 'MySQL Username',
            'mysql_password' => 'MySQL Password',
            'mysql_database' => 'MySQL Database',
            'mysql_host' => 'MySQL Host',
            'table_prefix' => 'MySQL Table prefix'
		);
		
		$allfilled=TRUE;
		foreach($config_vals as $field=>$prompt){
            if(isset($_POST[$field]) && !empty($_POST[$field]))
                $config[$field] = $_POST[$field];
            else
                $missing_fields .= $prompt.' ';
		}
		
		//Test first to see if the passwords both match
		if ($config['password'] !== $config['secondPassword']){
			$missing_fields .= "Passwords mismatched.";
		}
		//trim bblog/install/index.php off the URL
		$pos = strpos($config['url'], 'bblog/install/');
        $config['url'] = ($pos !== false) ? substr($config['url'], 0, $pos) : $config['url'];
            
		if (strlen($missing_fields) > 0) {
			$message = "<p style='color:red;'>You must fill all the fields. Following fields are missing<br />$missing_fields</p>";
			break;
		}

		// try to connect to db
		$db = new db($config['mysql_username'],$config['mysql_password'],$config['mysql_database'],$config['mysql_host']);
		
		/**
		 * xushi: i get an notice warning in the logs when installing regarding this line
		 * PHP Stack trace:, referer: bblog/install/index.php
		 * PHP   1. {main}() bblog/install/index.php:0, referer: http://localhost/xushi/08/bblog/install/index.php
		 * PHP   2. include() bblog/install/index.php:121, referer: http://localhost/xushi/08/bblog/install/index.php
		 * PHP Notice:  Undefined variable: EZSQL_ERROR in bblog/install/steps.php on line 131, referer: http://localhost/xushi/08/bblog/install/index.php
		 */				 
		if(is_array($EZSQL_ERROR)) {
			$message = $EZSQL_ERROR[0]['error_str'];
			
			break;
		}
		
		$step = 4;
		$_SESSION['config'] =& $config;
		break;
}
?>
