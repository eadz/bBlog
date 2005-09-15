<?php
/**
 * function.header.php
 *
 * @package bBlog
 * @author Eaden McKee - <email@eadz.co.nz> - last modified by $LastChangedBy: $
 * @version $Id: $
 * @copyright The bBlog Project, http://www.bblog.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

function identify_function_email () {
$help = 'usage: <br/>
        {email email=\'somone@example.com\' name=\'john doe\'} <br/>
        or just<br/>
        {email email=\'somone@example.com\'} <br/>';

  return array (
    'name'           =>'email',
    'type'             =>'function',
    'nicename'     =>'Email',
    'description'   =>'encodes email addresses to get rid of spam bots',
    'authors'        =>'Tobias Schlottke <tschlottke@virtualminds.de>',
    'licence'         =>'GPL',
    'help'   => $help
  );
}

function smarty_function_email($params) {

    extract($params);
    if(!$name) $name = str_replace("."," dot ",str_replace("@"," at ",$email));
    $email = preg_replace("/\"/","\\\"",$email);
    $old = "document.write('<a href=\"mailto:$email\">$name</a>')";

    $output = "";
    for ($i=0; $i < strlen($old); $i++) {
        $output = $output . '%' . bin2hex(substr($old,$i,1));
    }

    echo "<script language=\"JavaScript\" type=\"text/javascript\">eval(unescape('".$output."'))</script>";
}

?>
