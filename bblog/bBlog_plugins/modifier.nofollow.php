<?php
/**
 * modifier.nofollow.php - Smarty {nofollow} plugin, Prevent comments spammers
 *
 * @package bBlog
 * @author Martin Konicek - <martin_konicek@centrum.cz> - last modified by $LastChangedBy: $
 * @version $Id: $
 * @link http://smartbee.sourceforge.net/
 * @copyright The bBlog Project, http://www.bblog.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */
 

/**
 * Type:     plugin<br>
 * Name:     NoFollow<br>
 * Date:     6.3.2005<br>
 * Purpose:  Prevent comments spammers - more at http://www.google.com/googleblog/2005/01/preventing-comment-spam.html<br>
 * Input:<br>
 *<p>
 * Example:
 *  {$url|nofollow}
 *<p>
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