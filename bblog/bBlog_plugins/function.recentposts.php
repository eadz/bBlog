<?php
/**
 * function.recentposts.php - main post loop plugin
 * <p>
 * @copyright Copyright (C) 2003  Eaden McKee <email@eadz.co.nz>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 * @package bblog
 */

// 11 June 2003
//   * allow to create real unordered lists: all param mode=list
//   * customizeable title length
//   -- Sebastian http://www.sebastian-werner.net/

function identify_function_recentposts () {
$help = '
<p>Recentposts is a function that creates a list of recent posts
<p>Example usage {recentposts}  to create a list of the 5 most recent posts seperated by a &lt;br&gt;<br />
Or {recentposts mode="list"} to make a list using &lt;li&gt;
<p>Other paramaters : <br>
num=10 for 10 posts<br>
skip=10 to skip 10 posts<br/>
sectionid to specify a section by number<br />
section to specify a section by name<br />
sep=" | " to seperate by pipe instead of  &lt;br&gt;';

  return array (
    'name'           =>'recentposts',
    'type'             =>'function',
    'nicename'     =>'Recent Posts',
    'description'   =>'Displays list of most recent posts',
    'authors'        =>'Eaden McKee <eadz@bblog.com>',
    'licence'         =>'GPL',
    'help'   => $help
  );
}

function smarty_function_recentposts($params, &$smartyObj) {
    $bBlog = & $smartyObj->get_template_vars("bBlog_object");
    $opt = array();

        $num = 5;
        $mode = "br";
        $sep = "<br />";
        $titlelen=30;
        $skip = 0;
        $linkcode = '';
        if(isset($params['sep'])) $sep = $params['sep'];

        if(isset($params['num'])) $num = $params['num'];
    $opt['num'] = $num;

        if(isset($params['mode'])) $mode = $params['mode'];

        if(isset($params['skip'])) $skip = $params['skip'];
    $opt['skip'] = $skip;

    if ($params['section'] != '') {
          $opt['sectionid'] = $bBlog->sect_by_name[$params['section']];
    }
    if ($params['sectionid'] != '') {
          $opt['sectionid'] = $params['sectionid'];
    }

        if(isset($params['titlelen'])) $titlelen = $params['titlelen'];

        $q = $bBlog->make_post_query( $opt );

        $posts = $bBlog->get_posts($q);

        if($mode=="list") $linkcode .= "<ul>";

        $i=0;
        if(is_array($posts)) {
        /* <a([^<]*)?href=(\"|')?([a-zA-Z]*://[a-zA-Z0-9]*\.[a-zA-Z0-9]*\.[a-zA-Z]*([^>]*)?)(\"|')?([^>]*)?>([^<]*)</a> */
    // This should match any protocol, any port, any URL, any title. URL's like www.yest.com are supported, and should be treated as HTTP by browsers.
        $regex = "#<a([^<]*)?href=(\"|')?(([a-zA-Z]*://)?[a-zA-Z0-9]*\.[a-zA-Z0-9]*\.[a-zA-Z]*(:[0-9]*)?([^>\"\']*)?)(\"|')?([^>]*)?>([^<]*)</a>#i";

                foreach ($posts as $post) {
                    $title = $post["title"];

                    if (preg_match($regex, $title, $matches) == 1)
                    {
                        $title = $matches[9];
                    }

                        $i++;
                        if($mode=="list") $linkcode .= "<li>";

                        // we using arrays in the template and objects in the core..
                        $url = $post['permalink'];
                        $title = truncate($title,$titlelen,'...',FALSE);
                        $linkcode .= "<a href='$url'>$title</a>";

                        if($mode=="br" && $num > $i) $linkcode .= $sep;
                        if($mode=="list") $linkcode .= "</li>";
                }
        }

        if($mode=="list") $linkcode .= "</ul>";

        return $linkcode;
}


function truncate($string, $length = 80, $etc = '...',
                                  $break_words = false)
{
    if ($length == 0)
        return '';

    if (strlen($string) > $length) {
        $length -= strlen($etc);
        if (!$break_words)
        $string = preg_replace('/\s+?(\S+)?$/', '', substr($string, 0, $length+1));

        return substr($string, 0, $length).$etc;
    } else
        return $string;
}

/* vim: set expandtab: */

?>
