<?php
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 1800)) {
    header("Location: /login");
    session_unset();     // unset $_SESSION variable for the run-time 
    session_destroy();   // destroy session data in storage
	exit();
}
$_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp

if(!isset($_SESSION['is_auth']) || ($_SESSION['is_auth']) === false || !isset($_SESSION['user_id'])) {
	header("Location: /login");
    session_unset();     // unset $_SESSION variable for the run-time 
    session_destroy();   // destroy session data in storage
	exit();
}

if(isset($_GET['course']) && !isUserInCourse($conn, $_SESSION['user_id'], intval($_GET['course']))) {
	header("Location: /a");
}

?>