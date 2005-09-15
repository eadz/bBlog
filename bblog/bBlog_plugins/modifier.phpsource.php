<?php
/**
 * modifier.phpsource.php
 *
 * @package bBlog
 * @author Eaden McKee - <email@eadz.co.nz> - last modified by $LastChangedBy: $
 * @version $Id: $
 * @copyright The bBlog Project, http://www.bblog.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

function smarty_modifier_phpsource($text) {
    $text = preg_replace('/<php>[\r\n]+/', '<php>', $text);
    $text = preg_replace('/[\r\n]+<\/php>/', '</php>', $text);
    while(preg_match('/(<php>(.*?)<\/php>)/is', $text, $found)){
        $source =  highlight_string($found[2],true);
        $source = preg_replace('/<code>\W*<.*?>(.*)<\/span>\W*<\/code>/is', '<code>\1</code>',$source);
        $source = preg_replace('/<code>\W*<font.*?>(.*)<\/font>\W*<\/code>/is', '<code>\1</code>',$source);
        $source = preg_replace('/<code>[\r\n]+/', '<code>', $source);
        $text = str_replace($found[1], $source, $text);
    }
    return $text;
}

function identify_modifier_phpsource () {
  return array (
    'name'           =>'phpsource',
    'type'           =>'modifier',
    'nicename'       =>'phpsource',
    'description'    =>'Chops a post short with a readmore link',
    'authors'        =>'Tim Lucas <t.lucas-toolmantim.com>',
    'licence'        =>'GPL',
    'help'	     =>'Usage:<br>'
    );
}

?>
