<?php
class img {
	
	var $image = '';
	var $temp = '';
	var $type = '';
	
	function img($sourceFile, $ext){
		if(file_exists($sourceFile)){
			$this->type = $this->TypeByExtension($ext);
			$ImageCreate = 'ImageCreateFrom'.$this->type;
			$this->image = $ImageCreate($sourceFile);
		} else {
			$this->errorHandler();
		}
		return;
	}
	
	function TypeByExtension($ext){
		switch($ext){
			case 'jpg':
				return 'JPEG';
				break;
			case 'png':
				return 'PNG';
				break;
			case 'gif':
				return 'GIF';
				break;
			default:
				return 'JPEG';
		}
	}
	
	function resize($width = 100, $height = 100, $aspectradio = true, $enlarge = false){
		$o_wd = imagesx($this->image);
		$o_ht = imagesy($this->image);
		if(!$enlarge && ($o_wd*$o_ht < $width*$height)){
			return false;
		}
		if(isset($aspectradio)&&$aspectradio) {
			$w = round($o_wd * $height / $o_ht);
			$h = round($o_ht * $width / $o_wd);
			if(($height-$h)<($width-$w)){
				$width =& $w;
			} else {
				$height =& $h;
			}
		}
		$this->temp = imageCreateTrueColor($width,$height);
		imageCopyResampled($this->temp, $this->image,
		0, 0, 0, 0, $width, $height, $o_wd, $o_ht);
		$this->sync();
		return true;
	}
	
	function sync(){
		$this->image =& $this->temp;
		unset($this->temp);
		$this->temp = '';
		return;
	}
	
	function show(){
		$this->_sendHeader();
		ImageJPEG($this->image);
		return;
	}
	
	function _sendHeader(){
		header('Content-Type: image/'.strtolower($this->type));
	}
	
	function errorHandler(){
		echo "error";
		exit();
	}
	
	function store($file){
		$ImageType = 'Image'.$this->type;
		$ImageType($this->image,$file);
		return;
	}
	
	function watermark($pngImage, $left = 0, $top = 0){
		ImageAlphaBlending($this->image, true);
		$layer = ImageCreateFromPNG($pngImage); 
		$logoW = ImageSX($layer); 
		$logoH = ImageSY($layer); 
		ImageCopy($this->image, $layer, $left, $top, 0, 0, $logoW, $logoH); 
	}
}
?>
