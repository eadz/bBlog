<?php

/**
* Beginnings of comment handling consolidation
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
    function newComment(&$db, &$authImage, $post, $replyto){
        var_dump($replyto);
        $result = Comments::canProceed(&$db, $post, &$authImage, $_POST['spam_code'], $_POST['comment']);
        if($result['proceed'] === true){
            $vars['postername'] = my_addslashes(htmlspecialchars($_POST["name"]));
            if (empty($vars['postername']))
                $vars['postername'] = "Anonymous";
            $vars['posteremail'] = my_addslashes(htmlspecialchars($_POST["email"]));
            $vars['title'] = my_addslashes(htmlspecialchars($_POST["title"]));
            $vars['posterwebsite'] = my_addslashes(htmlspecialchars($_POST["website"]));
            if ((substr(strtolower($vars['posterwebsite']), 0, 7) != 'http://') && $vars['posterwebsite'] != '') {
                $vars['posterwebsite'] = 'http://'.$vars['posterwebsite'];
            }
            $vars['commenttext'] = Comments::processLinks(my_addslashes($_POST["comment"]));
            
            $vars['pubemail'] = ($_POST["public_email"] == 1) ? 1 : 0;
            $vars['pubwebsite'] = ($_POST["public_website"] == 1) ? 1 : 0;
            $vars['posternotify'] = ($_POST["notify"] == 1) ? 1 : 0;
            $vars['posttime'] = time();
            $vars['ip'] = $_SERVER['REMOTE_ADDR'];
            $vars['onhold'] = (Comments::needsModeration($vars['comment'])) ? 1 : 0;
            $vars['postid'] = $post->postid;
            if ($_POST['set_cookie']) {
                Comments::setCommentCookie($vars['postername'], $vars['posteremail'], $vars['posterwebsite']);
            }
            if ($replyto > 0)
                $vars['parentid'] = $replyto;
            $id = Comments::saveComment(&$db, $vars);
            if($id > 0){
                if(C_NOTIFY == true){
                    Comments::notify($vars['postername'], $post->permalink,$vars['onhold'], $vars['comment']);
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
            var_dump($sql);
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
            if(StringHandling::containsLinks($comment) === true){
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
    * Transforms raw text into hyperlinks
    *
    * @param string $comment Comment text
    * @return string
    */
    function processLinks($comment){
        if(StringHandling::containsExternalLinks($comment)){
            $lines = explode(" ", $comment);
            $result ='';
            foreach($lines as $k=>$line)
                $lines[$k] = StringHandling::transformLinks($line);
            $comment = implode(" ", $lines);
        }
        return $comment;
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
        $value = base64_encode(serialize(array ('web' => $website, 'mail' => $email, 'name' => $name)));
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
        $message = htmlspecialchars($name)." has posted a comment in reply to your blog entry at ".$link."\n\nComment: ".$comment."\n\n";
        if ($onhold == 1)
            $message .= "You have selected comment moderation and this comment will not appear until you approve it, so please visit your blog and log in to approve or reject any comments\n";
        notify_owner("New comment on your blog", $message);
    }
}
?>
