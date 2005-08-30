<?php
/**
 * function.header.php
 * <p>
 * @author Reverend Jim <jim@revjim.net>
 * @copyright Copyright (C) 2003  Eaden McKee <email@eadz.co.nz>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 * @package bblog
 */

function identify_function_header () {
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

