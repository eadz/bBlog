<?php
/**
 * function.header.php
 *
 * @package bBlog
 * @author Reverend Jim - <jim@revjim.net> - last modified by $LastChangedBy: $
 * @version $Id: $
 * @copyright The bBlog Project, http://www.bblog.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */


function identify_function_header() {
$help = '
<p>the {header} function is used to set arbitrary HTTP headers. It takes the following parameters:<br />
<br />
header: the header to send<br />';

  return array (
    'name'           =>'header',
    'type'             =>'function',
    'nicename'     =>'Header',
    'description'   =>'Sets an arbitrary HTTP header',
    'authors'        =>'Reverend Jim <jim@revjim.net>',
    'licence'         =>'GPL',
    'help'   => $help
  );
}
function smarty_function_header($params) {

    if(!headers_sent()) header($params['header']);

    return '';

}

