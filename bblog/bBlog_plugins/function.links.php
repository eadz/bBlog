<?php
/**
 * function.links.php - a smarty function for displaying bBlog links
 * <p>
 * @author Mario Delgado <mario@seraphworks.com>
 * @copyright Copyright (C) 2003  Eaden McKee <email@eadz.co.nz>
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 * @package bblog
 */

function identify_function_links ()
{
    $help = '
<p>Links is a Smarty function to be used in templates.</p>
<p>Example usage</p>
<ul>
    <li>
        To return a list of links, one per line :<br/>
        {links}
    </li>
    <li>
        To return a list, seperated by a # <br/>
        {links sep=#}
    </li>
    <li>
        To return a list for the category humor <br/>
        {links cat=humor}
    </li>
    <li>
        To return a list without the category stores <br/>
        {links notcat=stores}
    </li>
    <li>
        To return a list of all categories and links (nested in divs) <br/>
        {links mode=cagetorylist presep='<div>' sep='<\/div>'}
    </li>
    <li>
        To limit the number of links returned <br/>
        {links num=5}
    </li>
    <li>
        The default behavior is to return the list in <br/>
        or as set in the admin panel. This <br/>
        can be changed with one of the following key words <br/>
        <br/>
        {links ord=nicename} <br/>
        {links ord=category}
    </li>
    <li>
        To return a list in descending order <br/>
        {links desc=TRUE}
    </li>
    <li>
        cat and notcat are mutually exclusive and cannot <br/>
        be used together. They can both be used with sep, <br/>
        ord, num and desc which can all be used together. <br/>
        Category names and ord key words are case sensative.
    </li>
</ul>';

    return array
    (
        'name'          =>'links',
        'type'          =>'function',
        'nicename'      =>'Links',
        'description'   =>'Make a list of links',
        'authors'       =>'Mario Delgado <mario@seraphworks.com>',
        'licence'       =>'GPL',
        'help'          => $help
    );
}

function smarty_function_links($params, &$smartyObj)
{
    $bBlog = & $smartyObj->get_template_vars("bBlog_object");

    $mode   = (isset($params['mode']) ? $params['mode'] : '');
    $sep    = (isset($params['sep']) ? $params['sep'] : '');
    $presep = (isset($params['presep']) ? $params['presep'] : ''); // use this for lists
    $desc   = (isset($params['desc']) ? 'DESC' : '');
    $order  = (isset($params['ord']) ? $params['ord'] : 'position');
    $max    = (isset($params['num']) ? $params['num'] : '10');
    $cat    = (isset($params['cat']) ? $bBlog->get_var("select categoryid from ".T_CATEGORIES." where name='".$params['cat']."'") : '');
    $notcat = (isset($params['notcat']) ? $bBlog->get_var("select categoryid from ".T_CATEGORIES." where name='".$params['notcat']."'") : '');
    $markedlinks = (($mode == 'list') ? '<ul>' : '');

    if ($mode == 'categorylist')
    {
        $categories = $bBlog->get_results("select * from " . T_CATEGORIES . " order by name");

        if (!empty($categories))
        {
            foreach ($categories as $category)
            {
                $links = $bBlog->get_results("select * from " . T_LINKS . " where category='" . $category->categoryid . "' order by " . $order . " " . $desc . " limit " . $max);

                if (!empty($links))
                {
                    $markedlinks .= $presep;
                    $markedlinks .= '<strong>' . $category->name . '</strong><br/><ul>';

                    foreach ($links as $link)
                    {
                        $markedlinks .= '<li><a href="' . $link->url . '">' . $link->nicename . '</a></li>';
                    }

                    $markedlinks .= '</ul>';
                    $markedlinks .= $sep;
                }
            }
        }
    }
    else
    {
        $sep = ($sep == '') ? $sep = '<br/>': $sep;

        if ($cat)
        {
            $links = $bBlog->get_results("select * from ".T_LINKS." where category='".$cat."' order by ".$order." ".$desc." limit ".$max);
        }
        elseif ($notcat)
        {
            $links = $bBlog->get_results("select * from ".T_LINKS." where category !='".$notcat."' order by ".$order." ".$desc." limit ".$max);
        }
        else
        {
            $links = $bBlog->get_results("select * from ".T_LINKS." order by ".$order." ".$desc." limit ".$max);
        }

        if(!empty($links))
        {
            foreach ($links as $link)
            {
                $url = $link->url;
                $nicename = $link->nicename;

                if($mode == 'list')
                {
                    $markedlinks .= "<li><a href=\"$url\">$nicename</a></li>";
                }
                else
                {
                    $markedlinks .= "$presep<a href=\"$url\">$nicename</a>$sep";
                }
            }
        }
    }

    return $markedlinks;
}

?>
