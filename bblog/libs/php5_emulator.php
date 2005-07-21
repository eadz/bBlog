<?php
if(!function_exists('scandir')){
	function scandir($dir){
		$dh = opendir($dir);
		while (false !== ($filename = readdir($dh))) {
			$files[] = $filename;
		}
		sort($files);
		return $files;
	}
}
?>