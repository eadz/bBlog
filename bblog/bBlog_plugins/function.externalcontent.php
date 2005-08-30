<?php
/**
 * function.externalcontent.php - a Smarty function for displaying external content within bBlog
 * <p>
 * @author Paul Balogh <javaducky@gmail.com>
 * @copyright Copyright (C) 2003  Eaden McKee <email@eadz.co.nz>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 * @package bblog
 */

function identify_function_externalcontent() {
    $help = '<p>ExternalContent is a Smarty function to be used in templates.
             <p>Usage:
             <ul>
               <li>Return content from a "registered" provider<br>
               {ExternalContent provider="some provider name"}</li>
               <li>Return content in "adhoc" manner by supplying URL<br>
               {ExternalContent url="http://provider.com/mycontent"}</li>
             </ul>';

    return array (
        'name'        =>'externalcontent',
        'type'        =>'function',
        'nicename'    =>'External Content',
        'description' =>'Embeds markup from the specified content provider',
        'authors'     =>'Paul Balogh <javaducky@gmail.com>',
        'licence'     =>'GPL',
        'help'        => $help
    );

}

function smarty_function_externalcontent($params, &$smartyObj) {
    $bBlog = & $smartyObj->get_template_vars("bBlog_object");

    if (isset($params['provider'])) {
        $results = $bBlog->get_results("select url, enabled from ".T_EXT_CONTENT." where nicename='".$params['provider']."'");
        $content = $results[0]->url;
        $display = $results[0]->enabled;

    } elseif (isset($params['url'])) {
        $content = $params['url'];
        $display = 'true';

    }
    // Check for enabled display
    if ($display == 'false') return '';

    if ($content == '') {
        return "<i>Content Missing!</i>";
    }

    $bytes = @readfile($content);
    if ($bytes = 0) return "<i>No Content!</i>";

    return '';
}

 ?>
