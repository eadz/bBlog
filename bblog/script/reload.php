<?php
/* javascript called from ../adminscript.js
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