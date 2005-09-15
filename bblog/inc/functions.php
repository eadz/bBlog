<?php

/**
 * functions.php - General functions for bBlog that don't fit elsewhere
 *
 * @package bBlog
 * @author Eaden McKee - <email@eadz.co.nz> - last modified by $LastChangedBy: $
 * @version $Id: $
 * @copyright The bBlog Project, http://www.bblog.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */
                               


// !Pings weblogs.com, blo.gs, and others in the future
// this is in it's own function so we can use register_shutdown_function
function ping() {

    $weblogsparams[0] = C_BLOGNAME;
    $weblogsparams[1] = BLOGURL;
    $sites = explode(',',C_PING);
    if(count($sites) > 0) {
	foreach($sites as $site) {
		$url = explode('/',$site);
		XMLRPC_request($url[0], "/".$url[1], "weblogUpdates.ping", $weblogsparams, WEBLOG_XMLRPC_USERAGENT );
	}
    }

}


////
// !This fixes double slashes
// check to see if magic_quotes_gpc is set or not
// and excape accordingly
function my_addslashes ($data) {
	if (get_magic_quotes_gpc()) {
		return $data;
	} else {
		return addslashes($data);
	}
} // end of my_addslashes()


////
// !runs my_addslashes on an array item
// used by my_addslashes_array_walk
function my_addslashes_array(&$item,$key) {
       $item = my_addslashes($item);
} // end of my_addslashes_array()

////
// !runs my_addslashes over an array
// $array is not passed by reference so it makes a copy.
function my_addslashes_array_walk($array) {
       array_walk($array,'my_addslashes_array');
       return $array;
}


function update_when_compiled($tpl_source, &$smartyObj) {
    $bBlog = & $smartyObj->get_template_vars("bBlog_object");
         
    if(!defined('IN_BBLOG_ADMIN')) {
      $bBlog->modifiednow();
    }
    
    return $tpl_source;
}

//used as a block delimiter for dynamic content, doesn't actually
//do anything to the content
function smarty_block_dynamic($param, $content, &$smarty) {
    return $content;
}
?>
