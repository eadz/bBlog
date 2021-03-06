<?php
/**
 * function.icq.php
 *
 * @package bBlog
 * @author Eaden McKee - <email@eadz.co.nz> - last modified by $LastChangedBy: $
 * @version $Id: $
 * @copyright The bBlog Project, http://www.bblog.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */


function smarty_function_icq($params,  &$smartyObj) {
    return icq_online($params['number']) ? 'online' : 'offline';
}

function icq_online($icq_number){
    if($fp = fsockopen("status.icq.com", 80)){
        stream_set_timeout($fp, 2);
        fputs($fp, "GET /online.gif?icq=".$icq_number."&img=5 HTTP/1.0\r\n\r\n");
        $s='';
        while($line=FGetS($fp,3)){
            $s.=$line;
        }
        return ereg('online1.gif',$s) ? true : false;
    } else {
        return false;
    }
}
?>