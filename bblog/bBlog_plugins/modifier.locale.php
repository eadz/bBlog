<?php
/**
 * modifier.locale.php - smarty modifier to set locale
 *
 * @package bBlog
 * @author Eaden McKee - <email@eadz.co.nz> - last modified by $LastChangedBy: $
 * @version $Id: $
 * @copyright The bBlog Project, http://www.bblog.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */
 
 
function smarty_modifier_locale($stream, $locale) {
  setlocale(LC_ALL, $locale);
  setlocale(LC_TIME, $locale);

  return $stream;
}

function identify_modifier_locale () {
  return array (
    'name'           =>'locale',
    'type'           =>'smarty_modifier',
    'nicename'       =>'Set Locale',
    'description'    =>'Set locale and return unmodified input data',
    'authors'         =>'Sebastian Werner',
    'licence'         =>'GPL',
    'help'           =>''
  );
}

function bblog_modifier_locale_help () {
?>
<p>Set Locale</p>
<pre>
{$post.posttime|locale:"de_DE"|data_format:"date"}
</pre>
<?php
}
?>
