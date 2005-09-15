<?php

/**
 * trackback.class.php - Implements trackback handling according to the Trackback specification found at http://www.sixapart.com/pronet/docs/trackback_spec
 *
 * @package bBlog
 * @author Eaden McKee - <email@eadz.co.nz> - last modified by $LastChangedBy: $
 * @version $Id: $
 * @copyright The bBlog Project, http://www.bblog.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */
 
 
class trackback {
    var $_db;
    var $_tbdata;
    var $_post;
    var $_ip;
    /**
     * Contrustor
     * 
     * @param object $db
     * @param object $post The post receiving the trackback
     */
    function trackback($db, $post){
        $this->_db =& $db;
        $this->_post =& $post;
    }
    /**
     * Process a trackback someone sent to us
     * 
     * @param string $ip IP Address of the pinger
     * @param array $ext_vars The trackback data, in the format:
     * +================================================+
     * | key       |   value                            |
     * +-----------+------------------------------------+
     * | url*      | URL of the pinging site            |
     * +-----------+------------------------------------+
     * | title     | Title of the referring article     |
     * +-----------+------------------------------------+
     * | excerpt   | Excerpt from the referring article |
     * +-----------+------------------------------------+
     * | blog_name | Name of the referring blog         |
     * +===========+====================================+
     * @param int $commentid If given, the ID of a comment in a blog
     */
    function receiveTrackback($ip, $ext_vars, $commentid = null){
        $this->_ip = $ip;
        $this->_tbdata = $ext_vars;
        $allow = $this->allowTrackback();
        if(is_array($allow)){
            foreach($allow['message'] as $msg)
                $err .= ' '.$msg;
            $this->userResponse(1,$msg);
        }
        else{
            $replyto = is_null($commentid) ? $commentid : 0;
            
            /*
             * According to the spec, only URL is required, all else is optional
             */
            $vars['posterwebsite'] = my_addslashes($this->_tbdata['url']);
            $vars['title'] = (isset($this->_tbdata['title'])) ? my_addslashes($this->_tbdata['title']) : '';
            $vars['commenttext'] = (isset($this->_tbdata['excerpt'])) ? my_addslashes($this->_tbdata['excerpt']) : '';
            $vars['postername'] = (isset($this->_tbdata['blog_name'])) ? my_addslashes($this->_tbdata['blog_name']) : '';
            $vars['posttime'] = time();
            $vars['ip'] = $this->_ip;
            $vars['postid'] = $this->_post->postid;
            if($replyto > 0)
                $vars['parentid'] = $replyto;
            
            /*
            * Added check for moderation.
            * Follow the same rules as for comments
            */
            $vars['commenttext'] = Comments::processLinks(my_addslashes($vars['commenttext']));
            $vars['onhold'] = (Comments::needsModeration($vars['commenttext'])) ? 1 : 0;
            $vars['type'] = 'trackback';
            
            //Save the trackback
            $id = Comments::saveComment(&$db, $vars);
            if($id > 0) { 
                // notify owner
                if(C_NOTIFY == true){
                    Comments::notify($vars['postername'], $this->_post->permalink, $vars['onhold'], $vars['commenttext']);
                }
                Comments::updateCommentCount($this->_db, $this->_post->postid);
                $this->userResponse(0);
            } else {
                $this->userResponse(1,"Error adding trackback : ".mysql_error());
            }
        }
    }
    /**
     * Respond to a trackback ping
     * 
     * According to the specification:
     * <quote>
     * In the event of a succesful ping, the server MUST return a response in
     * the following format:
     * <code>
     * <?xml version="1.0" encoding="utf-8"?>
     * <response>
     *  <error>0</error>
     * </response>
     * </code>
     * In the event of an unsuccessful ping, the server MUST return an HTTP
     * response in the following format:
     * <code>
     * <?xml version="1.0" encoding="utf-8"?>
     * <response>
     * <error>1</error>
     * <message>The error message</message>
     * </response>
     * </code>
     * </quote>
     * 
     * @param int $err 0 or 1 only. 0 == Success; 1 == Failure
     * @param string $msg Only necessary when an error is raised. In this case
     * it is an error message
     * @return void
     */
    function userResponse($err, $msg){
        if($err !== 0 && $err !== 1){
            exit('Improper value given for trackback handling status');
        }
        else{
            $result = '<?xml version="1.0" encoding="utf-8"?'.">\n<response>\n<error>".$err."</error>\n";
            if(!empty($msg))
                $result .= "<message>".$msg."</message>\n";
            $result .= "</response>"; 
            header("Content-Type: application/xml");
            exit($result);
        }
    }
    /**
     * Performs various checks to determine whether the trackback is allowed.
     * 
     * Most checks are user-configurable via the admin options control panel.
     * The following are checked:
     *  	Flooding
     * 	  	User Input (comments, trackbacks) disallowed
     * 		URL (a requirement of the spec)
     * @return mixed If a trackback is allowed, return true, else return an array of error messages
     */
    function allowTrackback($db, $post, $ip){
        $rval = true;
        $rs = array();
        if(Comments::isDisabled($this->_post)){
            $rs['message'][] = array('Trackbacks are disabled for this post');
        }
        if(Comments::isFlooding($this->_db, $this->_ip, time())){
            $rs['message'][] = array("Error adding trackback. You tried to make a comment too soon after your last one. Please try again later. This is a bBlog spam prevention measure");
        }
        if(!isset($this->_tbdata['url'])){
            $rs['message'][] = array("Error: No URL supplied. The trackback specification stipulates the URL is required.");
        }
        if(count($rs) > 0)
            $rval = $rs;
        return $rval;
    }
}
?>