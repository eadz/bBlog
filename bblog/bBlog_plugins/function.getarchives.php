<?php
/**
 * function.getarchives.php - retrieve a list of archives
 * <p>
 * @author Reverend Jim <jim@revjim.net>
 * @copyright Copyright (C) 2003  Eaden McKee <email@eadz.co.nz>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 * @package bblog
 */

function identify_function_getarchives () {
$help = '
<p>the {getarchives} function is used to retrieve a list of archives. It takes the following parameters:<br />
<br />
assign: variable to assign data to<br />
sectionid: to request archives only in a certain section<br />
show: can be years, months, days, hours, minutes, or seconds. Determines how detailed the archive list should be<br />
year: requests archives only from a certain year<br />
month: requests archives only from a certain month<br />
day: requests archives only from a certain day<br />
hour: requests archives only from a certain hour<br />
minute: requests archives only from a certain minute<br />
second: requests archives only from a certain second<br />
count: requests a count of the number of entries in each archive (takes longer to compute)<br />
reverse: reserver the order of the archives (newest first)<br />';

  return array (
    'name'           =>'getarchives',
    'type'             =>'function',
    'nicename'     =>'GetArchives',
    'description'   =>'Retrieves a list of archives',
    'authors'        =>'Reverend Jim <jim@revjim.net>',
    'licence'         =>'GPL',
    'help'   => $help
  );
}
function smarty_function_getarchives($params, &$smartyObj) {
    $bBlog = & $smartyObj->get_template_vars("bBlog_object");

    $ar = array();
    $opt = $params;

    unset($opt['assign']);

    // If "assign" is not set... we'll establish a default.
    if($params['assign'] == '') {
        $params['assign'] = 'archives';
    }

    $ar = $bBlog->get_archives($opt);

    // No posts.
    if(!is_array($ar)) {
        return '';
    }

    if ( $params['reverse'] == 'true' ) {
        $ar = array_reverse( $ar );
    }
    $bBlog->smartyObj->assign($params['assign'],$ar);

    return '';

}

?>
