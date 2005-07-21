<?php
/****************************
  INIT
*****************************/
// Constants
define('RESIZE','/(\W*)(\d+)-(\d+)x(\d+)(k*)(\.(png|jpg|gif))$/i');
define('WATERMARK','/(\W*)(\d+)-wm.jpg$/i');
/*
example : images/thumbs/mountain-1-28x30k.jpg
$i[0] = mountain-1-28x30k.jpg
$i[1] = mountain_____________
$i[2] = _________1___________
$i[3] = ___________28________
$i[4] = ______________30_____
$i[5] = ________________k____
$i[6] = __________________jpg
*/

define('DIR_SEP', DIRECTORY_SEPARATOR);
define('DIR_CUR', dirname(__FILE__).DIR_SEP);
define('DIR_BBLOG', dirname(__FILE__).DIR_SEP.'bblog'.DIR_SEP); 
define('DIR_IMG_MAIN',  DIR_BBLOG . 'pbimages' . DIR_SEP); 
define('DIR_RESIZED', DIR_IMG_MAIN . 'thumbs' . DIR_SEP);
define('DIR_WM', DIR_IMG_MAIN . 'watermark' . DIR_SEP);
define('DIR_LIBS', DIR_BBLOG.'libs'.DIR_SEP); 
define('LIBRARY', DIR_LIBS.'image.php');
define('PARSER', DIR_LIBS.'parser.php');

// Libraries
require_once LIBRARY;

/****************************
  IMAGE RESIZE
*****************************/
if(preg_match(RESIZE,$_SERVER["REQUEST_URI"],$i)){
  // get image name
  $filename = DIR_IMG_MAIN . $i[2] .$i[6];

  // keep aspect ratio
  $keep = !empty($i[5]) ? false : true;

  $image = new img($filename, $i[7]);
  $image->resize($i[3],$i[4],!empty($i[5]) ? false : true);
  $image->store(DIR_RESIZED.$i[0]);
  $image->show();
  unset($image);
}
/****************************
  IMAGE WATERMARK
*****************************/	
elseif(preg_match(WATERMARK,$_SERVER["REQUEST_URI"],$i)){
  // get image name
  $filename = DIR_CUR . 'images' . DIR_SEP . $i[2] .'.jpg';
  $image = new img($filename);
  $image->watermark(DIR_CUR.'images'.DIR_SEP.'config'.DIR_SEP.'watermark.png', 10, 10);
  $image->store(DIR_WM.$i[0]);
  $image->show();
  unset($image);
}
elseif(preg_match('/\/([^\/]+).html$/i', $_SERVER["REQUEST_URI"], $i)){
	$parsed =& $i[1];
	header("HTTP/1.0 200 OK");
	require_once(DIR_CUR . 'index.php');
}
elseif(preg_match('/error.tester$/i', $_SERVER["REQUEST_URI"])){
	header("HTTP/1.0 200 OK");
	echo 'ok';
	exit();
}
/****************************
	URL PARSER
*****************************/
else {
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
?>
