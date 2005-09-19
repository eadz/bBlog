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
    * The following tests are performed during the transformation
    * process:
    *   1. Is the text already transformed? If so, call redirectHref
    *   2. Is the protocal present (http:// or ftp://)?
    *      2a. Yes - call transformWithProtocol
    *      2b. No - call transformWithoutProtocol
    *
    * @param string $str String to check and convert
    * @return string
    */
    function transformLinks($str){
        /*if(StringHandling::containsTransformedLinks($str)){
            if(!StringHandling::containsRedirector($str))
                $str = StringHandling::redirectHref($str);
        }
        else{
            if(StringHandling::isProtocolPresent($str)){
                $str = StringHandling::wrapLink($str, true);
            }
            else{
                $str = StringHandling::wrapLink($str, false);
            }
        }*/
        $et_pattern = '/<a([\s]+[^>]*)href[\s]*=[\s]*["\']([^"]+[.\s]*)(["\'][^>]*[\s]*)>([^<]+[.\s]*)<\/a>/i';
        $prot_pattern = '/\s([fh]+[t]{0,1}tp[s]*:\/\/([a-zA-Z0-9_~#=&%\/\:;@,\.\?\+-]+))/i';
        $simple_pattern = '/\s(www|ftp)\.([a-zA-Z0-9_~#=&%\/\:;@,\.\?\+-]+)/i';
        $patterns = array($et_pattern, $prot_pattern, $simple_pattern);
        
        $et_replace = '<a$1href="http://www.google.com/url?sa=D&q=$2"$3>$4</a>';
        $prot_replace = ' <a href="http://www.google.com/url?sa=D&q=$1">$2</a>';
        $simple_replace = ' <a href="http://www.google.com/url?sa=D&q=$0">$2</a>';
        $repl = array($et_replace, $prot_replace, $simple_replace);
        
        $str = preg_replace($patterns, $repl, $str);
        
        //$u = preg_replace($et_pattern, $et_replace, $txt);
        //$u = preg_replace($prot_pattern, $prot_replace, $u);
        //$u = preg_replace($simple_pattern, $simple_replace, $u);
        return $str;
    }
    /**
    * Wraps a hyperlink with the proper HTML tag
    *
    * @param string $str The hyperlink to wrap
    * @param bool $present Does the link already contain the protocol
    * @return string
    */
    function wrapLink($str, $present=false){
        $match = array();
        if($present){
            $pattern = "/(.*)([fh]+[t]*tp[s]*:\/\/([a-zA-Z0-9_~#=&%\/\:;@,\.\?\+-]+))(.*)/";
            if (preg_match($pattern, $str, $match)){
                $str = StringHandling::encodeHTML($match[1]);
                $str.= '<a href="'.StringHandling::redirectUrl($match[2]).'">'.$match[3].'</a>';
                $str.= StringHandling::encodeHTML($match[4]);
                $html_encoded = true;
            }
        }
        else{
            $pattern = "/^(www|ftp)\.([a-zA-Z0-9_~#=&%\/\:;@,\.\?\+-]+)(.*)/";
            if(preg_match($pattern, $str, $match)){
                $str = '';
                $str.= '<a href="'.StringHandling::redirectUrl('http://'.$match[0]).'">'.$match[0].'</a>';
                $str.= StringHandling::encodeHTML($match[3]);
                $html_encoded = true;
            }
            /*else
                $str = StringHandling::encodeHTML($str);*/
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
        $str = strtolower($str);
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
    }
    
    /**
    * Simple check for presence of a protocol
    *
    * Checks a string for the http:// and ftp:// protocol
    * qualifiers
    *
    * @param string $str
    * @return bool
    */
    function isProtocolPresent($str){
        $str = strtolower($str);
        $rval = false;
        if(strpos($str, 'http://') !== false)
            $rval = true;
        if(strpos($str, 'ftp://') !== false)
            $rval = true;
        return $rval;
    }
    
    /**
    * Reports whether a string contains clickable links
    *
    * @param string $str The string to check
    * @return bool
    */
    function containsTransformedLinks($str){
        $rval = false;
        $str = strtolower($str);
        if(strpos($str, '<a') !== false)
            $rval = true;
        return $rval;
    }
    
    /**
     * Reports whether a link contains the Google Redirector.
     * This is meant to be used after a string successfully passes the containsTransformedLinks
     * check. If used without first passing that check unexpected results may return.
     * 
     * @param string $str The link to check
     * return bool True if contains the link redirector, False if it does not
     */
    function containsRedirector($str){
        $rval = false;
        $str = strtolower($str);
        if(strpos($str, 'http://www.google.com/url?sa=D&q=') !== false)
            $rval = true;
        return $rval;
    }
    
    /**
    * Replace various characters with their HTML entities equivalent
    *
    * @param string $str The string to parse
    * @return string
    */
    function encodeHTML($str){
        $result = $str;
        $search = array('&', '<', '>', '"', '&lt;a');
        $replace = array('&amp;', '&lt;', '&gt;', '&quot;');
        $result = str_replace($search, $replace, $result);
        return $result;
    }
    
    function containsExternalLinks($str){
        $str = $str;
        return true;
    }
    
    /**
    * Prepends the Google redirector service to the HREF attribute of anchor tags
    * Use this when a hyperlink already exists as a HTML tag
    *
    * @param string $str
    * @return string
    */
    function redirectHref($str){
    // Google link redirector
        return preg_replace("/href=\"/i","href=\"http://www.google.com/url?sa=D&q=", $str);
    }
    
    /**
    * Prepends the Google redirector service to raw hyperlinks
    * Use this when the hyperlink is raw text, not transformed into a HTML tag
    *
    * Only works for the http protocol
    *
    * @param string $str
    * @return str
    */
    function redirectUrl($str) {
        return preg_replace("/http:\/\//i","http://www.google.com/url?sa=D&q=http://", $str);
    }
}
?>
