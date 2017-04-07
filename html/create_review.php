<?php 

include '../check_auth.php';
include '../config.php';
include "../review.php";

if(!isset($_GET['course'])) {
	http_response_code(400);
}

if (!isset($_SESSION['user_level']) || $_SESSION['user_level'] > 1) {
	http_response_code(401);
	exit;
}

$conn = new mysqli($cfg['db_host'], $cfg['db_user'], $cfg['db_password'], $cfg['db_name']);

if ($conn->connect_error) {
	die("Database connection failed: " . $conn->connect_error);
}

$section = 0;
$category = 0;
$idx = 0;
$review = array();

foreach ($_POST as $name => $value) {
	$d = explode("_", $value);
	$section = ((int) $d[1]);
	$category = ((int) $d[2]);
	$idx = ((int) $d[3]);

	if(!isset($review[ $section ])) {
		$review[ $section ] = array();
	}
	
	if(!isset($review[ $section ][ $category ])) {
		$review[ $section ][ $category ] = array();
	}

	

}

?>