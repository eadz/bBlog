<?php
/**
 * modifier.phpsource.php - Smarty truncate modifier plugin
 *
 * @author Eaden McKee - <email@eadz.co.nz> - last modified by $LastChangedBy: $
 * @version $Id: $
 * @copyright The bBlog Project, http://www.bblog.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty truncate modifier plugin
 *
 * Type:     modifier<br>
 * Name:     truncate<br>
 * Purpose:  Truncate a string to a certain length if necessary,
 *           optionally splitting in the middle of a word, and
 *           appending the $etc string.
 * @link http://smarty.php.net/manual/en/language.modifier.truncate.php
 *          truncate (Smarty online manual)
 * @param string
 * @param integer
 * @param string
 * @param boolean
 * @return string
 */
function smarty_modifier_search($string, $pattern = '')
{
  if(!empty($pattern)){
    $pattern = htmlspecialchars($pattern);
		$patterns = explode(' ',$pattern);
		$string = strip_tags($string);
		$colors = array(
				'#ffff00','#00ffff','#99ff99','#ff9999','#ff66ff', // black on color hilights
				'#880000', '#00aa00', '#886800', '#004699', '#990099'); // white on color hilights
		$i = 0;
		foreach ($patterns as $pattern){
			$string = preg_replace(
      '/'.$pattern.'/i', '<span class="search-string" style="background-color:'.$colors[$i++].'">'.$pattern.'</span>', $string);
		}
    return $string;
  } else {
    return $string;
  }
}

/* vim: set expandtab: */

?>
