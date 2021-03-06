<?php
/**
 * modifier.smilies.php
 *
 * @package bBlog
 * @author Eaden McKee - <email@eadz.co.nz> - last modified by $LastChangedBy: $
 * @version $Id: $
 * @copyright The bBlog Project, http://www.bblog.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */


function smarty_modifier_smilies($stream, $path){
    $smilies = array(
    ' :)'        => 'icon_smile.gif',
    ' :D'        => 'icon_biggrin.gif',
    ' :-D'       => 'icon_biggrin.gif',
    ':grin:'    => 'icon_biggrin.gif',
    ' :)'        => 'icon_smile.gif',
    ' :-)'       => 'icon_smile.gif',
    ':smile:'   => 'icon_smile.gif',
    ' :('        => 'icon_sad.gif',
    ' :-('       => 'icon_sad.gif',
    ':sad:'     => 'icon_sad.gif',
    ' :o'        => 'icon_surprised.gif',
    ' :-o'       => 'icon_surprised.gif',
    ':eek:'     => 'icon_surprised.gif',
    ' 8O'        => 'icon_eek.gif',
    ' 8-O'       => 'icon_eek.gif',
    ':shock:'   => 'icon_eek.gif',
    ' :?'        => 'icon_confused.gif',
    ' :-?'       => 'icon_confused.gif',
    ' :???:'     => 'icon_confused.gif',
    ' 8)'        => 'icon_cool.gif',
    ' 8-)'       => 'icon_cool.gif',
    ':cool:'    => 'icon_cool.gif',
    ':lol:'     => 'icon_lol.gif',
    ' :x'        => 'icon_mad.gif',
    ' :-x'       => 'icon_mad.gif',
    ':mad:'     => 'icon_mad.gif',
    ' :P'        => 'icon_razz.gif',
    ' :-P'       => 'icon_razz.gif',
    ':razz:'    => 'icon_razz.gif',
    ':oops:'    => 'icon_redface.gif',
    ':cry:'     => 'icon_cry.gif',
    ':evil:'    => 'icon_evil.gif',
    ':twisted:' => 'icon_twisted.gif',
    ':roll:'    => 'icon_rolleyes.gif',
    ':wink:'    => 'icon_wink.gif',
    ' ;)'        => 'icon_wink.gif',
    ' ;-)'       => 'icon_wink.gif',
    ':!:'       => 'icon_exclaim.gif',
    ':?:'       => 'icon_question.gif',
    ':idea:'    => 'icon_idea.gif',
    ':arrow:'   => 'icon_arrow.gif',
    ' :|'        => 'icon_neutral.gif',
    ' :-|'       => 'icon_neutral.gif',
    ':neutral:' => 'icon_neutral.gif',
    ':mrgreen:' => 'icon_mrgreen.gif',
    );
    foreach($smilies as $key => $value){
        $change = ' <img src="'.BBLOGURL.$path.$value.'" alt="'.$key.'" />';
        $stream = str_replace($key, $change, $stream);
    }
    return $stream;
}
?>