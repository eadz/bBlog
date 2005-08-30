<?php
/**
 * modifier.htmlspecialchars.php
 * <p>
 * @copyright Copyright (C) 2003  Eaden McKee <email@eadz.co.nz>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 * @package bblog
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
