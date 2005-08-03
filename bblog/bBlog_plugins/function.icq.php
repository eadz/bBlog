<?php
/**
 * function.icq.php
 * <p>
 * @copyright Copyright (C) 2003  Eaden McKee <email@eadz.co.nz>
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 * @package bblog
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