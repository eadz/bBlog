<?php
/**
 * modifier.none.php
 *
 * @package bBlog
 * @author Eaden McKee - <email@eadz.co.nz> - last modified by $LastChangedBy: $
 * @version $Id: $
 * @copyright The bBlog Project, http://www.bblog.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

 function identify_modifier_none () {
  return array (
    'name'           =>'none',
    'type'           =>'modifier',
    'nicename'       =>'None',
    'description'    =>'Does nothing at all to your text',
    'authors'         =>'Eaden McKee',
    'licence'         =>'GPL',
    'help'             =>'There is not much to say here... your post will stay exactly as you type it and will not be changed'
  );
}

function smarty_modifier_none($string)
{
    return $string;
}

?>
