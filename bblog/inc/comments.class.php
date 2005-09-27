<?php
/**
 * Beginnings of comment handling consolidation
 *
 * @package bBlog
 * @author Kenneth Power <kenneth.power@gmail.com>, http://www.bblog.com/ - last modified by $LastChangedBy: $
 * @version $Id: $
 * @copyright Kenneth Power <kenneth.power@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

class Comments{
    
    /**
    * Add a new comment to an article
    *
    * @param object $db EZ SQL instance
    * @param object $authImage AuthImage instance
    * @param object $post The post receiving the comment
    * @param int    $replyto The ID of the parent comment
    */
    function newComment(&$db, &$authImage, $post, $replyto, $post_vars){
        $result = Comments::canProceed(&$db, $post, &$authImage, $post_vars['spam_code'], $post_vars['comment']);
        if($result['proceed'] === true){
            $vars = Comments::prepFields($post_vars, $replyto, $post->postid);
            if ($post_vars['set_cookie']) {
                Comments::setCommentCookie($vars['postername'], $vars['posteremail'], $vars['posterwebsite']);
            }
            
            $id = Comments::saveComment(&$db, $vars);
            if($id > 0){
                if(C_NOTIFY == true){
                    Comments::notify($vars['postername'], $post->permalink,$vars['onhold'], $vars['commenttext']);
                }
                $newnumcomments = $db->get_var('SELECT count(*) as c FROM `'.T_COMMENTS.'` WHERE postid='.$post->postid.' and deleted="false" group by postid');
                $db->query('UPDATE `'.T_POSTS.'` SET commentcount='.$newnumcomments.' WHERE postid='.$post->postid);
                $result = $id;
            }
            else{
                $result['error'] = true;
                $result['message'][] = array("Error", "Error inserting comment for post ".$post->title);
                error_log(mysql_error(), 0);
            }
        }
        return $result;
    }
    
    function prepFields($vars, $replyto, $id){
        $rval['postername'] = my_addslashes(htmlspecialchars($vars["name"]));
        if (empty($rval['postername']))
            $rval['postername'] = "Anonymous";
        $rval['posteremail'] = my_addslashes(htmlspecialchars($vars["email"]));
        $rval['title'] = my_addslashes(htmlspecialchars($vars["title"]));
        $rval['posterwebsite'] = my_addslashes(StringHandling::transformLinks(htmlspecialchars($vars["website"])));
        $rval['commenttext'] = Comments::processCommentText(my_addslashes($vars["comment"]));
        
        $rval['pubemail'] = ($vars["public_email"] == 1) ? 1 : 0;
        $rval['pubwebsite'] = ($vars["public_website"] == 1) ? 1 : 0;
        $rval['posternotify'] = ($vars["notify"] == 1) ? 1 : 0;
        $rval['posttime'] = time();
        $rval['ip'] = $_SERVER['REMOTE_ADDR'];
        $rval['onhold'] = (Comments::needsModeration($rval['commenttext'])) ? 1 : 0;
        $rval['postid'] = $id;
        if ($replyto > 0)
            $rval['parentid'] = $replyto;
        $rval['type'] = 'comment';
        return $rval;
    }
    /**
    * Save the comment/trackback
    *
    * The SQL statement for saving data is built based upon the values of
    * `$vars`. It is an associative array where the keys are the `T_CONFIG`
    * field names and the elements are values for the fields. On success, the
    * row id(integer) is returned, on failure either false (boolean) or
    * an error message (string) is returned.
    *
    * @param object $db EX SQL Instance
    * @param array $vars An associative array
    * @return mixed
    */
    function saveComment(&$db, $vars){
        $rval = false;
        if(is_array($vars)){
            $q = 'INSERT INTO `'.T_COMMENTS.'` SET ';
            foreach($vars as $fld=>$val)
                $q .= $fld.'="'.$val.'",';
            $sql = substr($q, 0, -1);
            //var_dump($sql);
            if($db->query($sql))
                $rval = $db->insert_id;
            else
                $rval = mysql_error();
        }
        return $rval;
    }
    
    /**
    * Tests comment text against moderation criteria
    *
    * @param string $comment The comment text
    * @return bool
    */
    function needsModeration($comment){
        $rval = false;
        if (C_COMMENT_MODERATION == 'all') {
            $rval = true;
        }
        elseif (C_COMMENT_MODERATION == 'urlonly') {
            if(strpos($comment, '<a') !== false){
                $rval = true;
            }
        }
        return $rval;
    }
    
    /**
    * Initiates a variety of tests
    *
    * An array is returned with the following fields
    * and values:
    * +=============================================+
    * | proceed    |  true if all passed all tests  |
    * |            |  false if failed any test      |
    * +============+================================+
    * | message    | An array of error messages:    |
    * |            | array(message_title,           |
    * |            |   message_text);               |
    * +============+================================+
    *
    * @param object $db EZ SQL instance
    * @param object $post The article receiving the comment
    * @param object $authImage AuthImage instance
    * @param string $code Captcha code as typed by the user
    * @param string $comment Comment text
    * @return array
    */
    function canProceed(&$db, $post, &$authImage, $code, $comment){
        $rval['proceed'] = true;
        $rval['message'] = array();
        if(Comments::isFlooding(&$db, $_SERVER['REMOTE_ADDR'], time())){
            $rval['proceed'] = false;
            $rval['message'][] = array("Comment Flood Protection", "Error adding comment. You have tried to make a comment too soon after your last one. Please try again later. This is a bBlog spam prevention measure");
        }
        if(Comments::isDisabled($post)){
            $rval['proceed'] = false;
            $rval['message'][] = array("Error adding comment", "Comments have been turned off for this post");
        }
        if(Comments::failsCaptcha($authImage, $code)){
            $rval['proceed'] = false;
            $_SESSION['postercomment'] = $comment;
            $rval['message'][] = array("Spam prevention", "Error adding comment. Please check that the characters you typed for the image verification are correct");
        }
        return $rval;
    }
    
    /**
    * Checks whether commenting is disabled for this post
    *
    * @param object $post
    * @return bool
    */
    function isDisabled($post){
        $rval = false;
        if ($post->allowcomments == ('disallow') or ($post->allowcomments == 'timed' and $post->autodisabledate < time()))
            $rval = true;
        return $rval;
    }
    
    /**
    * Performs various transformations on text. Hyperlinks have
    * the redirector added and are wrapped in A tags (if not already wrapped).
    * Special characters are transformed into HTML entities.
    *
    * @param string $comment Comment text
    * @return string
    */
    function processCommentText($comment){
        //Policy: only a, b, i, strong, code, acrynom, blockquote, abbr are allowed
        $comment = StringHandling::removeTags($comment, '<a><b><i><strong><code><acronym><blockquote><abbr>');
        if(StringHandling::containsLinks($comment)){
            $comment = StringHandling::transformLinks($comment);
        }
        //Policy: translate HTML special characters to their HTML entities
        $comment = Comments::encodeHTML($comment);
        //Policy: line breaks converted automatically
        return nl2br($comment);
    }
    
    /**
    * Checks whether an attempt at comment flooding is being made
    *
    * @param object $db EZ SQL instance
    * @param string $ip IP Address of commentor
    * @param int $now Unix Timestamp of current time
    */
    function isFlooding(&$db, $ip, $now){
        $rval = false;
        if (C_COMMENT_TIME_LIMIT > 0) {
            $fromtime = $now - (C_COMMENT_TIME_LIMIT * 60);
            $db->query("select * from ".T_COMMENTS." where ip='".$ip."' and posttime > ".$fromtime);
            if ($db->num_rows > 0) {
                $rval = true;
            }
        }
        return $rval;
    }
    
    /**
    * Saves comment details in a cookie
    *
    * @param string $name Commentors name
    * @param string $email Commentors email address
    * @param string $website Commentors website
    * @return void
    */
    function setCommentCookie($name, $email, $website){
        $ctime = time()+3600*24*30;
        setcookie("postername", $name, $ctime);
        setcookie("posteremail", $email, $ctime);
        setcookie("posterwebsite", $website, $ctime);
        $value = base64_encode(serialize(array ('web' => str_replace("\/","/", $website), 'mail' => $email, 'name' => $name)));
        setcookie("bBcomment", $value, time() + (86400 * 360));
    }
    
    /**
    * Tests what user typed against the captcha
    *
    * @param object $authImage Instance of AuthImage
    * @param string $code Captcha code typed by user
    * @return bool
    */
    function failsCaptcha(&$authImage, $code){
        $rval = false;
        if(C_IMAGE_VERIFICATION == 'true' && !empty($code)) { //Some templates may not have the iamge verification enabled
            if(!$authImage->checkAICode($code)){
                $rval = true;
            }
        }
        return $rval;
    }
    
    /**
    * Notifies blog author of new comment
    *
    * @param string $name Commentors name
    * @param string $link Link to comment entry
    * @param int    $onhold Whether or not comment requires moderation
    * @param string $comment Text of the comment
    * @return void
    */
    function notify($name, $link, $onhold, $comment){
        include_once (BBLOGROOT."inc/mail.php");
        $message = $name." has posted a comment in reply to your blog entry at ".$link."\n\nComment: ".$comment."\n\n";
        if ($onhold == 1)
            $message .= "You have selected comment moderation and this comment will not appear until you approve it, so please visit your blog and log in to approve or reject any comments\n";
        notify_owner("New comment on your blog", $message);
    }
    function updateCommentCount($db, $postid){
        $newnumcomments = $db->get_var("SELECT count(*) as c FROM ".T_COMMENTS." WHERE postid='$postid' and deleted='false' group by postid");
        $db->query("update ".T_POSTS." set commentcount='$newnumcomments' where postid='$postid'");
    }
    
    /**
     * Enforces HTML Encoding policy on comment text
     * 
     * Policy states HTML special characters (&, ", etc) be translated to
     * their HTML entity equivalents for HTML display purposes. In doing this,
     * we must maintain the HTML tags (a, b, i, strong, code, acrynom, blockquote,
     * abbr) policy allows.
     */
    function encodeHTML($comment){
        //Make certain we don't encode the allowed tags
        //Policy: only a, b, i, strong, code, acrynom, blockquote, abbr are allowed
        $_blocks = array(
            'a' => array('pattern'=>'/<a[^>]+>.*?<\/a>/is'),
            'abbr' => array('pattern'=>'/<abbr[^>]+>.*?<\/abbr>/is'),
            'b' => array('pattern'=>'/<b>.*?<\/b>/is'),
            'i' => array('pattern'=>'/<i>.*?<\/i>/is'),
            'strong' => array('pattern'=>'/<strong>.*?<\/strong>/is'),
            'code' => array('pattern' => '/<code[^>]+>.*?<\/code>/is'),
            'acronym' => array('pattern' => '/<acronym[^>]+>.*?<\/acronym>/is'),
            'blockquote' => array('pattern' => '/<blockquote[^>]+>.*?<\/blockquote>/is')
            );
        foreach($_blocks as $tag=>$arr){
            $match = array();
            preg_match_all($arr['pattern'], $comment, $match);
            //if(count($match) > 0){
                $_blocks[$tag]['match'] = $match[0];
                $replace = '%%%COMMENT:TRANSFORM:'.strtoupper($tag).'%%%';
                $comment = preg_replace($arr['pattern'], $replace, $comment);
            //}
        }
        $comment = htmlspecialchars($comment);
        foreach($_blocks as $tag=>$arr){
            $search_str= '%%%COMMENT:TRANSFORM:'.strtoupper($tag).'%%%';
            $_len = strlen($search_str);
            $_pos = 0;
            for ($_i=0, $_count=count($arr['match']); $_i<$_count; $_i++)
                if (($_pos=strpos($comment, $search_str, $_pos))!==false)
                    $comment = substr_replace($comment, $arr['match'][$_i], $_pos, $_len);
                else
                    break;
        }
        return $comment;
    }
}
?>