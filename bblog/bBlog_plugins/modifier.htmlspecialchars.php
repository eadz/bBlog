<?php
/**
 * modifier.htmlspecialchars.php
 *
 * @package bBlog
 * @author Eaden McKee - <email@eadz.co.nz> - last modified by $LastChangedBy: $
 * @version $Id: $
 * @copyright The bBlog Project, http://www.bblog.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */


function identify_modifier_htmlspecialchars () {
    return array (
    'name'           =>'htmlspecialchars',
    'type'             =>'smarty_modifier',
    'nicename'     =>'HTML Special Chars',
    'description'   =>'Converts HTML Special Chars to form-friendly entities',
    'authors'        =>'',
    'licence'         =>'',
    'help'    	=> ''
  );
}

function smarty_modifier_htmlspecialchars ($in) {
    return htmlspecialchars($in);
}


?>
