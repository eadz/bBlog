<?php
/**
 * passwordReset.class.php - password management.
 * <p>
 * This class should manage the obtaining, resetting, and
 * passing of passwords from and to the DB. I did it this way
 * because i prefer anything password related to be managed
 * in one place only. I'm still confused with constructors
 * between java and php, so feel free to fix it.
 * <p>
 * @author xushi - <xushi.xushi@gmail.com>
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 * @copyright Copyright (C) 2005  Eaden McKee <email@eadz.co.nz>
 * @package bblog
 */
 
class passwdManager { 
	
	// temporary until propper username is implemented.
	var $username = "temp";
	
	/**
     * Resets a users's password.
     * <p>
     * Call the random_password(x) function in order
     * to generate a new password, and return it back
     * to the user.
     * <p>
     * @param var $bBlog User name to reset the password of
	 * @return var
	*/
    function get_password($username) {
    	//TODO: err, how do you change to __get ?
    	return $this->random_password(5);
    }
    
    
    /**
     * update author's table with new password.
     * <p>
     * @param var $enc_password The new encrypted password
	*/
    function set_password($enc_password) {
    	global $bBlog;
    	$bBlog->query("UPDATE ".T_AUTHORS." SET password='".$enc_password."' WHERE id='1'");
    }
    
    
    /**
     * Hash the new password with sha1()
     * <p>
     * while writing this comment i just noticed its 'hashing'
     * not 'encrypting', so don't let the function name fool you
     * <p>
     * @param var $random_password the new random password generated
	 * @return var
	*/
    function encrypt_password($random_password) {
    	return sha1($random_password);
    }
    
    
    /**
     * Generate new password.
     * <p>
     * Generates a new random password
     * <p>
     * @author huhu - <http://www.phpfreaks.com/quickcode/Random_Password_Generator/56.php>
	 * @param integer $len Length of new password
	 * @return var
	*/
    function random_password($len)
	{
   		$salt = 'AaBbCcDdEeFfGgHhIiJjKkLlMmNnOoPpQqRrSsTtUuVvXxYyZz0123456789';
   		$salt_max = strlen( $salt ) - 1 ;
    	$pass = '' ;
   		for( $i=0; $i<$len; $i++ ) {
       		$pass .= substr( $salt, mt_rand(0, $salt_max), 1 ) ;
   		}
   		return $pass ;
	}
}
?>
