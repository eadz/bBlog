<?php

/**
 * authimage.php - Need a description for this file
 *
 * @package bBlog
 * @author bBlog Weblog, http://www.bblog.com/ - last modified by $LastChangedBy: $
 * @version $Id: $
 * @copyright The bBlog Project, http://www.bblog.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

require_once('./config.php');
$authimage = new authimage();
$authimage->createAICode();

?>