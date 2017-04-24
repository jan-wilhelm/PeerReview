<?php
if (isset($_SESSION['info']['LAST_ACTIVITY']) && (time() - $_SESSION['info']['LAST_ACTIVITY'] > 1800)) {
    header("Location: " . $ROOT_SITE . "login");
    session_unset();     // unset $_SESSION['info'] variable for the run-time 
    session_destroy();   // destroy session data in storage
	exit();
}
$_SESSION['info']['LAST_ACTIVITY'] = time(); // update last activity time stamp

if(!isset($_SESSION['info']['is_auth']) || ($_SESSION['info']['is_auth']) === false || !isset($_SESSION['info']['user_id'])) {
    header("Location: " . $ROOT_SITE . "login");
    session_unset();     // unset $_SESSION['info'] variable for the run-time 
    session_destroy();   // destroy session data in storage
	exit();
}

if(isset($_GET['course']) && !isUserInCourse($conn, $_SESSION['info']['user_id'], intval($_GET['course']))) {
	header("Location: " . $ROOT_SITE);
}

?>