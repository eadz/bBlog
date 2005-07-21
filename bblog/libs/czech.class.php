<?php
/* 
======================================================================
 AutoCzech 1.0
======================================================================

PHP knihovna na prekodovani cestiny

	http://www.webdot.cz/autoczech/

AUTOR: Vojtech Semecky, webmaster@webdot.cz

----------------------------------------------------------------------
LICENCE

Tento program je svobodny software. Je distribuovan pod licenci
GNU GPL 2.0 nebo vyssi (dle vasi volby) tak, jak ji publikovala
nadace Free Software Foundation. Smite jej svobodne pouzivat,
modifikovat a/nebo redistribuovat. Pro detaily se podivejte na:

	anglicky original - http://www.gnu.org/copyleft/gpl.html

	cesky preklad - http://www.gnu.cz/gplcz.html

======================================================================
*/

/* TODO
*/

class czech {
// Funkce na prevod libovolneho ceskeho textu $str na kodovani $code
// (puvodni kodovani neni nutne znat)
	function autoCzech($str, $code = 'asc')
	{
		// Ceske znaky v ruznych kodovanich (ASCII, Win-1250, ISO 8859-2, UTF-8)
		$autocp['asc'] = array ('A','C','D','E','E','I','N','O','R','S','T','U','U','Y','Z',
					'a','c','d','e','e','i','n','o','r','s','t','u','u','y','z');
		$autocp['win'] = array ('Á','È','Ï','É','Ì','Í','Ò','Ó','Ø','Š','','Ú','Ù','Ý','Ž',
					'á','è','ï','é','ì','í','ò','ó','ø','š','','ú','ù','ý','ž');
		$autocp['iso'] = array ('Á','È','Ï','É','Ì','Í','Ò','Ó','Ø','©','«','Ú','Ù','Ý','®',
					'á','è','ï','é','ì','í','ò','ó','ø','¹','»','ú','ù','ý','¾');
		$autocp['utf'] = array("\xc3\x81", "\xc3\x88", "\xc3\x8f", "\xc3\x89", "\xc3\x83", "\xc3\x8d", "\xc3\x92", "\xc3\x93", "\xc3\x98", "\xc5\xa0", "\xc2\x8d", "\xc3\x9a", "\xc3\x99", "\xc3\x9d", "\xc5\xbd",
				       "\xc3\xa1", "\xc3\xa8", "\xc3\xaf", "\xc3\xa9", "\xc3\xac", "\xc3\xad", "\xc3\xb2", "\xc3\xb3", "\xc3\xb8", "\xc5\xa1", "\xc2\x9d", "\xc3\xba", "\xc3\xb9", "\xc3\xbd", "\xc5\xbe");
	
		// Vsechny ceske znaky ktere je mozne prevadet
		$autocp['merge'] = array_merge ($autocp['utf'], $autocp['win'], $autocp['iso']);
	
		// Prevod do UTF nelze primo, takze AutoCzech na ISO a pak FromToCzech ISO->UTF.
		if ($code=='utf') {
			$str = $this->auto($str, 'iso');
			return str_replace($autocp['iso'], $autocp['utf'], $str);
			}
		// ... do vseho osttaniho (ISO, WIN, ASC) muzeme prevezt primo
		else {
			$to = array_merge ($autocp[$code], $autocp[$code], $autocp[$code]);
			return str_replace($autocp['merge'], $to, $str);
		}
	}
	
	function getCharset ($code)
	{
		$code = strtolower(substr($code, 0, 3));
		$charset['iso'] = 'iso-8859-2';
		$charset['win'] = 'windows-1250';
		$charset['utf'] = '????';
		$charset['asc'] = 'iso-8859-1';
		return $charset[$code];
	}
}
?>