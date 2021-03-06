<?php
/**
 * function.getposts.php - retrieve recent posts
 *
 * @package bBlog
 * @author Reverend Jim - <jim@revjim.net> - last modified by $LastChangedBy: $
 * @version $Id: $
 * @copyright The bBlog Project, http://www.bblog.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */


function identify_function_getarchiveposts () {
$help = '
<p>the {getarchiveposts} function is used to retrieve recent posts. It takes the following parameters:<br />
<br />
assign: variable to assign data to<br />
archive: to get ascending sorted results<br />
section: to request recent items in a section<br />
num: number of items to fetch<br />
skip: number of items to skip<br />
all: when set to 1, gets all items (ignores num and skip)<br />
year: year of posts<br />
month: month of posts<br />
day: day of posts<br />
hour: hour of posts<br />
minute: minute of posts<br />
second: second of posts<br />
sectionid: to request recent items in a section, by specifing the sectionid';

  return array (
    'name'           =>'getarchiveposts',
    'type'             =>'function',
    'nicename'     =>'GetArchivetPosts',
    'description'   =>'Retrieves recent blog posts',
    'authors'        =>'Reverend Jim <jim@revjim.net>',
    'licence'         =>'GPL',
    'help'   => $help
  );
}
function smarty_function_getarchiveposts($params, &$smartyObj) {
  $bBlog = & $smartyObj->get_template_vars("bBlog_object");

  $ar = array();
  $opt = array();

    // If "assign" is not set... we'll establish a default.
    if($params['assign'] == '') {
        $params['assign'] = 'posts';
    }

        // If "all" is set to 1, do not limit the number of elements.
        if(is_numeric($params['all']) && $params['all'] == 1) {
               // The limit variable is used in make_post_query() to
               // specify the skip and num parameters.  If the limit
               // variable has a length, these parameters are not set,
               // hence allowing all entries to be retrieved.
               $opt['limit'] = " ";
        }


    // If "archive" is set, order them ASCENDING by posttime.
    if($params['archive']) {
        $opt['order']=" ORDER BY posttime ";
    }

    if(is_numeric($params['num'])) {
        $opt['num'] = $params['num'];
    }

    if(is_numeric($params['skip'])) {
        $opt['skip'] = $params['skip'];
    }



    if(is_numeric($params['year'])) {
        if(strlen($params['year']) != 4) {
            $smartyObj->trigger_error('getarchiveposts: year parameter requires a 4 digit year');
            return '';
        }
        $opt['year'] = $params['year'];
    }

    if(is_numeric($params['month'])) {
        if(strlen($params['month']) != 2) {
            $smartyObj->trigger_error('getarchiveposts: month parameter requires a 2 digit month');
            return '';
        }
        $opt['month'] = $params['month'];
    }

    if(is_numeric($params['day'])) {
        if(strlen($params['day']) != 2) {
            $smartyObj->trigger_error('getarchiveposts: day parameter requires a 2 digit day');
            return '';
        }
        $opt['day'] = $params['day'];
    }

    if(is_numeric($params['hour'])) {
        if(strlen($params['hour']) != 2) {
            $smartyObj->trigger_error('getarchiveposts: hour parameter requires a 2 digit hour');
            return '';
        }
        $opt['hour'] = $params['hour'];
    }

    if(is_numeric($params['minute'])) {
        if(strlen($params['minute']) != 2) {
            $smartyObj->trigger_error('getarchiveposts: minute parameter requires a 2 digit minute');
            return '';
        }
        $opt['minute'] = $params['minute'];
    }

    if(is_numeric($params['second'])) {
        if(strlen($params['second']) != 2) {
            $smartyObj->trigger_error('getarchiveposts: second parameter requires a 2 digit second');
            return '';
        }
        $opt['second'] = $params['second'];
    }

    if ($params['section'] != '') {
        $opt['sectionid'] = $bBlog->sect_by_name[$params['section']];
    }
    if ($params['sectionid'] != '') {
        $opt['sectionid'] = $params['sectionid'];
    }

  $q = $bBlog->make_post_query($opt);

  $ar['posts'] = $bBlog->get_posts($q);

    // No posts.
  if(!is_array($ar['posts'])) {
        return '';
    }

    $lastmonth = '';
    $lastdate = '';

    foreach($ar['posts'] as $key => $value) {
        // It seems silly to do this. Especially since,
        // this kind of check can be done in Smarty template.
        // Additionally, since {newday} and {newmonth} require
        // the data to be in a variable named "post" it may not
        // function at all.
        //
        // We'll leave it here for now.

    if(date('Fy',$ar['posts'][$key]['posttime']) != $lastmonth) {
      $ar['posts'][$key]['newmonth'] = 'yes';
        }
    $lastmonth = date('Fy',$ar['posts'][$key]['posttime']);

    if(date('Ymd',$ar['posts'][$key]['posttime']) != $lastdate) {
      $ar['posts'][$key]['newday'] = 'yes';
    }
    $lastdate = date('Ymd',$ar['posts'][$key]['posttime']);
    }

    $smartyObj->assign($params['assign'],$ar['posts']);

  return '';

}

?>
