<?php
/*
Plugin Name: AuthImage
Plugin URI: http://www.gudlyf.com/index.php?p=376
Description: Creates an authentication image (or phonetic text) to help combat spam in comments.
Version: 2.0.4
Author: Keith McDuffee
Author URI: http://www.gudlyf.com/
*/

class authimage {

function checkAICode($code)
{
	global $bBlog;
  // Comment out following two lines to be case sensitive
  $code = md5(strtoupper($code).$_SERVER["REMOTE_ADDR"]);
  $valid = $bBlog->db->get_var("
	SELECT `id` FROM `".T_CHECKCODE."` WHERE `checksum` = '".$code."' LIMIT 1");
	
  return $valid > 0 ? true : false;
}

function createAICode()
{
	global $bBlog;
  $code = $this->randomString();
	$bBlog->db->query("
	DELETE FROM `".T_CHECKCODE."` WHERE `timestamp`+3000<NOW()");
	$bBlog->db->query("
	INSERT INTO `".T_CHECKCODE."` ( `id` , `checksum` , `timestamp` )
	VALUES ('', '".md5($code.$_SERVER["REMOTE_ADDR"])."', NOW( ))");
	
    if (!isset($plugins_dir))
      $plugins_dir = dirname(__FILE__).'/';

    $fontfile = "atomicclockradio.ttf";
    $font = $plugins_dir . $fontfile;

    $im = @imageCreate(110, 50) or die("Cannot Initialize new GD image stream");

    $background_color = imageColorAllocate($im, 195, 217, 255);
    $text_color = imageColorAllocate($im, 168, 18, 19);

    ImageTTFText($im, 20, 5, 18, 38, $text_color, $font, $code);

    // Date in the past
    header("Expires: Thu, 28 Aug 1997 05:00:00 GMT");

    // always modified
    $timestamp = gmdate("D, d M Y H:i:s");
    header("Last-Modified: " . $timestamp . " GMT");
 
    // HTTP/1.1
    header("Cache-Control: no-store, no-cache, must-revalidate");
    header("Cache-Control: post-check=0, pre-check=0", false);

    // HTTP/1.0
    header("Pragma: no-cache");

    // dump out the image
    header("Content-type: image/png");
    ImagePNG($im);
}


function randomString($type='num',$length=4)
{
  $randstr='';
  srand((double)microtime()*1000000);

	$odd = array('A','E','I','O','U','Y');
	$even = array('B','F','H','J','K','L','P','R','S','T','X','Z');


  for ($rand = 0; $rand < $length; $rand++)
  {
    $odd_random = rand(0, count($odd) -1);
		$even_random = rand(0, count($even) -1);
    $randstr .= round($rand/2) != $rand/2 ? $odd[$odd_random] : $even[$even_random];
  }
  return $randstr;
}
}
?>
