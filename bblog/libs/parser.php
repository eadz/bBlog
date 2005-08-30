<?php

/**
 * @author Martin Konicek <martin_konicek@centrum.cz>
 * @copyright Copyright (c) 2004, Martin Konicek
 * @todo
 * @link http://www.volny.cz/martin.konicek/
 * @version 0.0.1
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 * @package forsell
 */

// CHANGE IT
define('DIR_SEP',DIRECTORY_SEPARATOR);
define('DIR_PARSER',dirname(__FILE__).DIR_SEP);
define('DIR_LIB',DIR_PARSER);

// DATABASE
require_once DIR_LIB.'ez_sql.php';
require_once DIR_CUR.'config.php';
$db = new db_mysql(DB_USERNAME, DB_PASSWORD, DB_DATABASE, DB_HOST);
global $db;

// DON'T CHANGE
define('TABLE','parseurl');	// don't change
require_once DIR_PARSER . 'ez_sql.php';

// CODE
$parse = new parseURL();
parse_str($parse->query);
parse_str($parse->query,$_GET);

$parse_url_script = $parse->script;
unset($db,$parse);

require DIR_CUR . $parse_url_script;

// LIBRARY
class parseURL {

    var $query;
    var $script;
    var $real;

    function parseURL(){
        $this->_splitURL($_SERVER["REQUEST_URI"]);
        $real = $this->_findReal($this->script);
        if($real){
            $this->_sendHeaders();
            $this->_splitURL($real);
        } else {
            $this->_notFound($this->script);
        }
    }

    function _sendHeaders(){
        header("HTTP/1.0 200 OK");
    }

    /** find real script name
        * @param string $nice requested page name
        * @return string $result script url
        */
    function _findReal($nice){
        global $db;
        $result = $db->get_var("SELECT `real` FROM `".TABLE.
        "` WHERE `nice`='".$this->_sql($nice).
        "' LIMIT 1;");
        $this->real =& $result;
        return $result;
    }

    /** split URL into path and query
        * @param string $url $_SERVER["REQUEST_URI"]
        * @return boolean
        */
    function _splitURL($url){
        $matches = parse_url($url);
        $this->query = $matches['query'];
        $this->script = $matches['path'];
        return true;
    }

    /** this is called, when page is not found - 404
        * @param string $script requested page name
        */
    function _notFound($script){
    header("HTTP/1.0 404 Not Found");
    ?>
    <html><head><title>Document Not Found</title></head>
    <body><h1>Document Not found</h1>
    <hr>
    <em>Image Creator</em> &copy Martin Konicek
    </body></html>
    <?php
    exit();
    }

    /** attentd sql injection
        * @param string $escape sql string from user
        * @return string escaped sql string without injection
        */
    function _sql($escape){
        return mysql_real_escape_string($escape);
    }

}

?>