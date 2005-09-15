<?php
/**
 * function.months_archive.php
 *
 * @package bBlog
 * @author Eaden McKee - <email@eadz.co.nz> - last modified by $LastChangedBy: $
 * @version $Id: $
 * @copyright The bBlog Project, http://www.bblog.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */


function identify_function_months_archive () {
$help = '
<p>the {getrecentposts} function is used to retrieve recent posts. It takes the following parameters:<br />
<br />
assign: variable to assign data to<br />
archive: to get ascending sorted results<br />
num: for number of entries to return<br />
skip: number of entries to skip<br />
section: to request recent items in a section<br />
home=true : to only show posts that have not been hidden.<br />
sectionid: to request recent items in a section, by specifing the sectionid';

  return array (
    'name'           =>'getrecentposts',
    'type'             =>'function',
    'nicename'     =>'GetRecentPosts',
    'description'   =>'Retrieves recent blog posts',
    'authors'        =>'Reverend Jim <jim@revjim.net>',
    'licence'         =>'GPL',
    'help'   => $help
  );
}

function smarty_function_months_archive($params, &$smartyObj) {
    $bBlog = & $smartyObj->get_template_vars("bBlog_object");

    if($params['assign'] == '') {
        $params['assign'] = 'months';
    }

    $query = "
    SELECT
        SQL_CACHE
        MONTH(FROM_UNIXTIME(`posttime`)) AS `month`,
        YEAR(FROM_UNIXTIME(`posttime`)) AS `year`
    FROM ".T_POSTS."
    GROUP BY
        `month`
    ORDER BY
        `posttime`
        DESC
    LIMIT 10
    ";
    $col = $bBlog->db->get_results($query);
    $results = array();
    $i = 0;
    foreach ($col as $tmp){
        $results[$i]['month'] = $tmp->month;
        $results[$i]['year'] = $tmp->year;
        $results[$i]['name'] = months($tmp->month);
        $results[$i]['cur'] = ($tmp->month == date('m')) ? true : false;
        $i++;
    }
    $smartyObj->assign($params['assign'],$results);
}

function months($i){
    $months = array(
        '1' => 'Leden',
        '2' => 'Únor',
        '3' => 'Březen',
        '4' => 'Duben',
        '5' => 'Květen',
        '6' => 'Červen',
        '7' => 'Červenec',
        '8' => 'Srpen',
        '9' => 'Září',
        '10' => 'Říjen',
        '11' => 'Listopad',
        '12' => 'Prosinec'
    );
    return $months[$i];
}