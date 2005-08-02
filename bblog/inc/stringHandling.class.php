<?php

/**
* Class for handling string related functions
*
* A pseudo static class, it never needs instantiated. This class
* serves to centralize various string handling functions, such as
* transforming typed hyperlinks into clickable links.
*
* @author Kenneth Power <kenneth.power@gmail.com>
* $LastModified$
* $Revision$
*/
class StringHandling{
    /**
    * Converts typed links into clickable links
    *
    * @param string $str String to check and convert
    * @return string
    */
    function transformLinks($str){
        $match = array();
        $pattern = "/(.*)([fh]+[t]*tp[s]*:\/\/[a-zA-Z0-9_~#=&%\/\:;@,\.\?\+-]+)(.*)/";
        if (preg_match($pattern, $str, $match)){
            $str = StringHandling::encodeHTML($match[1]);
            $str.= "<a href=\"".$match[2]."\" target=_blank\">".$match[2]."</a>";
            $str.= StringHandling::encodeHTML($match[3]);
            $html_encoded = true;
        }
        $pattern = "/^(www|ftp)\.([a-zA-Z0-9_~#=&%\/\:;@,\.\?\+-]+)(.*)/";
        if(preg_match($pattern, $str, $match)){
            var_dump($match);
            //$str = StringHandling::encodeHTML($match[1]);
            $str = '';
            $str.= "<a href=\"http://".$match[0]."\" target=_blank\">".$match[2]."</a>";
            $str.= StringHandling::encodeHTML($match[3]);
            $html_encoded = true;
        }
        return $str;
        
    }
    /**
    * Remove HTML tags from a string
    *
    * This is merely a wrapper around the native strip_tags function
    *
    * @param string $str String to remove tags from
    * @param array $tags Optional. List of tags to allow
    * @return string
    */
    function removeTags($str, $tags = array()){
        return (count($tags) > 0) ? strip_tags($str, $tags) : strip_tags($str);
    }
    /**
    * Reports whether a string contains hyperlinks
    *
    * @param string $str The string to check
    * @return bool
    */
    function containsLinks($str){
        $rval = false;
        if(strpos($str, 'href') !== false)
            $rval = true;
        if(strpos($str, 'http') !== false)
            $rval = true;
        if(strpos($str, 'www.') !== false)
            $rval = true;
        if(strpos($str, 'ftp.') !== false)
            $rval = true;
        if(strpos($str, 'ftp:') !== false)
            $rval = true;
        if(strpos($str, 'mailto') !== false)
            $rval = true;
        return $rval;
        /*if($str != preg_replace('!<[^>]*?>!', ' ', $str)) {
            // found html tags
            $needsModerated = true;
        }
        if($str != preg_replace("#([\t\r\n ])([a-z0-9]+?){1}://([\w\-]+\.([\w\-]+\.)*[\w]+(:[0-9]+)?(/[^ \"\n\r\t<]*)?)#i", '\1<a href="\2://\3" target="_blank">\2://\3</a>', $str)) {
            $needsModerated = true;
        }
        if($str != preg_replace("#([\t\r\n ])(www|ftp)\.(([\w\-]+\.)*[\w]+(:[0-9]+)?(/[^ \"\n\r\t<]*)?)#i", '\1<a href="http://\2.\3" target="_blank">\2.\3</a>', $str)) {
            $needsModerated = true;
        }*/
    }
    
    /**
    * Replace various characters with their HTML entities equivalent
    *
    * @param string $str The string to parse
    * @return string
    */
    function encodeHTML($str){
        $result = $str;
        $result = str_replace("&", "&amp;", $result);
        $result = str_replace("<", "&lt;", $result);
        $result = str_replace(">", "&gt;", $result);
        $result = str_replace("\"", "&quot;", $result);
        return $result;
    }
    
    function containsExternalLinks($str){
        $str = $str;
    }
    /* Google link redirector
    function googlify_href($input_text) {
	$input_text = preg_replace("/href=\"/","href=\"http://www.google.com/url?sa=D&q=", $input_text);
	return $input_text;
    }
    
    function googlify_url($input_text) {
        $input_text = preg_replace("/http:\/\//","http://www.google.com/url?sa=D&q=http://", $input_text);
        return $input_text;
    }
*/
}
?>
