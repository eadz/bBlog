<?php

/**
 * reload.php - javascript called from ../adminscript.js
 *
 * @package bBlog
 * @author Eaden McKee - <email@eadz.co.nz> - last modified by $LastChangedBy: $
 * @version $Id: $
 * @copyright The bBlog Project, http://www.bblog.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */
 
/** 
 * use to keep session
 */

// destroy old session
session_start();
$SESSION = $_SESSION;
session_destroy();

// save new session
session_start();
session_regenerate_id();
$_SESSION = $SESSION;
session_write_close();
?>