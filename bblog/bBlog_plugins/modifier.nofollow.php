<?php
/**
 * Smarty {nofollow} plugin
 *
 * Type:     plugin<br>
 * Name:     NoFollow<br>
 * Date:     6.3.2005<br>
 * Purpose:  Prevent comments spammers - more at http://www.google.com/googleblog/2005/01/preventing-comment-spam.html<br>
 * Input:<br>
 *
 * Example:    
 *  {$url|nofollow}
 *
 * @link http://smartbee.sourceforge.net/
 * @author   Martin Konicek <martin_konicek@centrum.cz>
 * @version  1.0
 * @param null
 * @param Smarty
 * @return boolen
 */

function smarty_modifier_nofollow($string){
	return preg_replace('/<(a.*?)>/i', '<\1 rel="nofollow">', $string);
}

/* vim: set expandtab: */
?>