<?php

/**
 * templates.php - Deals with templating functions
 *
 * @package bBlog
 * @author Eaden McKee - <email@eadz.co.nz> - last modified by $LastChangedBy: $
 * @version $Id: $
 * @copyright The bBlog Project, http://www.bblog.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */
                                                                          

// !Custom Smarty template handler for use with database templates
function db_get_template ($tpl_name, &$tpl_source, &$smarty_obj) {
    Global $bBlog;
    $tpl_source = $bBlog->get_var("select template from ".T_TEMPLATES." where templatename='$tpl_id'");
    return true;
}

////
// !Get the timestamp of a template from the database
function db_get_timestamp($tpl_name, &$tpl_timestamp, &$smarty_obj) {
    // do database call here to populate $tpl_timestamp.
    Global $bBlog;
    $tpl_timestamp = $bBlog->get_var("select compiletime from ".T_TEMPLATES." where templatename='$tpl_id'");
    return true;
}

function db_get_secure($tpl_name, &$smarty_obj) {
    // assume all templates are secure
    return true;
}

function db_get_trusted($tpl_name, &$smarty_obj){ }// not used


////
// !Make a footer in the html comments
// Make footer containing the page generation time
// and number of database calls and last modified date
function buildfoot() {
	global $bBlog;
    $mtime = explode(" ",microtime());
	$endtime = $mtime[1] + $mtime[0];
	
	$pagetime = round($endtime - $bBlog->begintime,5);
	$foot = "
<!--//
This page took $pagetime seconds to make
and executed {$bBlog->db->querycount} SQL queries.
Last modified: ".gmdate('D, d M Y H:i:s \G\M\T',$bBlog->lastmodified)."
Powered by bBlog : http://www.bBlog.com/
//-->";
	return $foot;
}

?>
