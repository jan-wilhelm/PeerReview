<?php
include '../config.php';

if( isset( $_SESSION["info"]["is_auth"] ) ) {
	header("Location: $ROOT_SITE");
	exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Account Aktivierung</title>
	<meta name="description" content="">
	<meta name="viewport" content="width=device-width, initial-scale=1">
  
	<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
	<link href="./css/materialize.css" type="text/css" rel="stylesheet" media="screen,projection"/>
	<link rel="stylesheet" type="text/css" href="./css/font-awesome.min.css">
	<link rel="stylesheet" type="text/css" href="./css/font-awesome.min.css">
</head>

<?php

$filePath = $IS_LOCAL ? "../" : "../../info/";
include $filePath . "mailer.php";

function confirmationRequestExists($conn, $id, $key) {
	if($stmt = $conn->prepare("SELECT 1 FROM signup_confirmations WHERE id = ? AND signup_key = ?")) {
		$stmt->bind_param("is", $id, $key);
		$stmt->execute();
		$result = $stmt->get_result();
		if ($result->num_rows > 0) {
			return true;
		}
		$stmt->free_result();
	} else {
		echo $conn->error;
	}
	return false;
}

function isExpired($conn, $id, $key) {
	if($stmt = $conn->prepare("SELECT 1 FROM signup_confirmations WHERE id = ? AND signup_key = ? AND expire BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 2 WEEK)")) {
		$stmt->bind_param("is", $id, $key);
		$stmt->execute();
		$result = $stmt->get_result();
		if ($result->num_rows > 0) {
			return false;
		}
		$stmt->free_result();
	} else {
		echo $conn->error;
	}
	return true;
}

function copyData($conn, $id, $key) {
	if($stmt = $conn->prepare("INSERT INTO users(name, email, password, created_at) SELECT name, mail, password, NOW() FROM signup_confirmations WHERE id = ? AND signup_key = ?")) {
		$stmt->bind_param("is", $id, $key);
		$stmt->execute();
	} else {
		echo $conn->error;
	}
}

function deleteRequest($conn, $id, $key) {
	if($stmt = $conn->prepare("DELETE FROM signup_confirmations WHERE id = ? AND signup_key = ?")) {
		$stmt->bind_param("is", $id, $key);
		$stmt->execute();
	} else {
		echo $conn->error;
	}
}

$conn = new mysqli($cfg['db_host'], $cfg['db_user'], $cfg['db_password'], $cfg['db_name']);

if ($conn->connect_error) {
	die("Database connection failed: " . $conn->connect_error);
}
?>

<body>
	<div class="navbar-fixed">
		<nav>
			<div class="nav-wrapper grey darken-3">
			  <a href="#" class="brand-logo center">Peer Review</a>
			</div>
		</nav>
	</div>
	<div class="container center z-depth-4" style="padding: 30px 0px; margin-top: 100px;">
	 	<?php
	 	if( !isset( $_GET["confid"] ) || !isset( $_GET["key"] )) {
	 		echo '<h4 class="alert alert-warning text-red">Ungültige Anfrage.</h4></div></body></html>';
	 		exit();
	 	}

		$id = intval($_GET["confid"]);
		$key = $_GET["key"];

		if(confirmationRequestExists($conn, $id, $key)) {
			if(!isExpired($conn, $id, $key)) {
				copyData($conn, $id, $key);
				echo '<span class="alert alert-success"><strong>Der Account wurde erfolgreich aktiviert!</strong><a href="' . $ROOT_SITE . '"> Gehe hier zur Startseite!</a></span>';
				deleteRequest($conn, $id, $key);
			} else {
				echo '<span class="alert alert-warning">Der Link ist abgelaufen. <a href="' . $ROOT_SITE . 'signup/">Bitte registriere dich erneut!</a></span>';
				//deleteRequest($conn, $id, $key);
			}
		} else {
			echo '<span class="alert alert-error"><strong>Fehler:</strong> Ungültiger link!</span>';
		}

	 	?>
	</div>
</body>
</html>