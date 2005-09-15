<?php

/**
 * trackback.php - Receives a trackback, and functions for sending a trackback
 *
 * @package bBlog
 * @author Eaden McKee - <email@eadz.co.nz> - last modified by $LastChangedBy: $
 * @version $Id: $
 * @copyright The bBlog Project, http://www.bblog.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */


/*
A Note about trackback and bBlog

At the moment we are using get varibles. That means that the bit that goes
The trackback URL for this post is:
http://www.example.com/blog/bblog/trackback.php?tbpost=1234
for a trackback on post 1234. 

Additionally bBlog is the first blog system to enable trackback replies to comments, and comment replies to trackbacks, so this is uncharted waters but it's pretty simple. Basicly, there is a trackback url for every post, and every comment. If a trackback is received for a comment it is handled like a reply to that comment in the database so displays threaded. Additionally, you can click reply to a trackback in the blog and rebut the excerpt if you so wish ;)

The trackback url for a comment is simply 
http://www.example.com/blog/bblog/trackback.php?tbpost=1234&cid=4141
 - adding the varible cid = commentid. 

If you so wished, you could use .htaccess or whatever and have trackback urls like : http://www.example.com/blog/trackback/1234 but that would require editing below. 

The future plan for bBlog is to have configurable URLs to the Nth degree, and when that happens this url will be configurable. 

*/

if(!defined('C_USER')) {
	include_once("./config.php");
}

if(isset($_SERVER['PATH_INFO']) && !empty($_SERVER['PATH_INFO'])){
    $tburi_ar = explode('/',$_SERVER['PATH_INFO']);
    $postid = intval($tburi_ar[1]);
    $commentid = intval($tburi_ar[2]);
/*} else {
    // GET is invalid for trackbacks, according to the spec
    $tbpost = intval($_POST['tbpost']);
    $tbcid  = intval($_POST['cid']);
    
}*/
    include_once('inc/trackback.class.php');
    $tb =& new trackback($bBlog->db, $post);
    if(isset($_POST['url']) && $tbpost !== 0) {
        $post = $bBlog->get_post($postid);
        $tb->receiveTrackback($_SERVER['REMOTE_ADDR'], $_POST, $commentid);
    }
}

}


// Send a trackback-ping.
function send_trackback($url, $title="", $excerpt="",$t){
    //parse the target-url
    $target = parse_url($t);
    
    if ($target["query"] != "") $target["query"] = "?".$target["query"];
    
    //set the port
    if (!is_numeric($target["port"])) $target["port"] = 80;
     
    //connect to the remote-host  
    $fp = fsockopen($target["host"], $target["port"]);
    
    if ($fp){

        // build the Send String
        $Send = "url=".rawurlencode($url).
                "&title=".rawurlencode($title).
                "&blog_name=".rawurlencode(C_BLOGNAME).
                "&excerpt=".rawurlencode($excerpt);
        
        // send the ping
        fputs($fp, "POST ".$target["path"].$target["query"]." HTTP/1.1\n");
        fputs($fp, "Host: ".$target["host"]."\n");
        fputs($fp, "Content-type: application/x-www-form-urlencoded\n");
        fputs($fp, "Content-length: ". strlen($Send)."\n");
        fputs($fp, "Connection: close\n\n");
        fputs($fp, $Send);
        
        //read the result
        while(!feof($fp)) {
            $res .= fgets($fp, 128);
        }
        
        //close the socket again  
        fclose($fp);
        
        //return success        
        return true;
    }else{
    
        //return failure
        return false;
    }
}

function trackback_response($error = 0, $error_message = '') {

}
?>