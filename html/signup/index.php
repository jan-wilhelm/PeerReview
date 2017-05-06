<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Signup</title>
	<meta name="description" content="">
	<meta name="viewport" content="width=device-width, initial-scale=1">
  
	<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
	<link href="../css/materialize.css" type="text/css" rel="stylesheet" media="screen,projection"/>
	<link rel="stylesheet" type="text/css" href="../css/font-awesome.min.css">
	<link rel="stylesheet" type="text/css" href="../css/font-awesome.min.css">
	<style>
		body {
		  display: flex;
		  min-height: 100vh;
		  flex-direction: column;
		}

		main {
		  flex: 1 0 auto;
		}
		.container {
			text-align: center;
		}
		.container > * {
			margin-left: auto;
			margin-right: auto;
			display: block;
		}

		#form-wrapper {
			margin-top: 40px;
		}

		.container .input-field {
			text-align: left;
		}

		.input-field input[type=date]:focus + label,
		.input-field input[type=text]:focus + label,
		.input-field input[type=email]:focus + label,
		.input-field input[type=password]:focus + label {
		  color: #e91e63;
		}

		.input-field input[type=date]:focus,
		.input-field input[type=text]:focus,
		.input-field input[type=email]:focus,
		.input-field input[type=password]:focus {
		  border-bottom: 2px solid #e91e63;
		  box-shadow: none;
		}
	</style>

</head>
<?php
	require '../../config.php';

	$filePath = $IS_LOCAL ? "../../" : "../../../info/";

	include $filePath. "mailer.php";

	$conn = new mysqli($cfg['db_host'], $cfg['db_user'], $cfg['db_password'], $cfg['db_name']);

	if ($conn->connect_error) {
		die("Database connection failed: " . $conn->connect_error);
	}

	/**
	 * Generate a random string of given length
	 * @param  int $length The required length of the password
	 * @return string         A new random passwort containg letters [a-z], [A-Z] and [0-9]
	 */
	function randomString($length){
		$alphabet    = 'abcdefghjkmnopqrstuvwxyzABCDEFGHJKMNOPQRSTUVWXYZ1234567890';
		$pass        = array();
		$alphaLength = strlen($alphabet) - 1;
		for ($i = 0; $i < $length; $i++) {
			$n      = rand(0, $alphaLength);
			$pass[] = $alphabet[$n];
		}
		return implode($pass);
	}

	function confirmationRequestExists($conn, $mail) {
		if($stmt = $conn->prepare("SELECT 1 FROM signup_confirmations WHERE mail = ?")) {
			$stmt->bind_param("s", $mail);
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

	function handleValidSignUp($conn, $username, $password, $mail, $root, $filePath) {
		$username = htmlspecialchars( trim( $username ) );
		$mail = htmlspecialchars( strtolower( trim( $mail ) ) );
		$password = password_hash($password, PASSWORD_DEFAULT);;

		$stmt = $conn->prepare("SELECT 1 FROM users WHERE name = ? AND email = ?");
		$stmt->bind_param("ss", $username, $mail);
		$stmt->execute();

		$result = $stmt->get_result();
		$found  = $result->num_rows > 0;

		if (!$found) {

			if(confirmationRequestExists($conn, $mail)) {
				echo '<span class="alert alert-warning">Ein Nutzer mit der Adresse ' . $mail . ' hat sich bereits registriert und wartet auf die Bestätigung des Accounts.</span>';
			} else {
				$stmt = $conn->prepare("INSERT INTO signup_confirmations (name, password, mail, signup_key, expire) VALUES (?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 2 WEEK) )");

				$key = randomString(128);

				$stmt->bind_param("ssss", $username, $password, $mail, $key);
				$stmt->execute();
				unset($stmt);

				$stmt = $conn->prepare("SELECT id FROM signup_confirmations WHERE name = ?");
				$stmt->bind_param("s", $username);
				$stmt->execute();
				$result = $stmt->get_result();

				$id = 0;
				if ($result->num_rows > 0) {
					if ($row = $result->fetch_assoc()) {
						$id = $row['id'];
					}
				}

				$link = "http://" . $_SERVER['SERVER_NAME'] . ( $_SERVER['SERVER_PORT'] == 80 ? "" : (":".$_SERVER['SERVER_PORT'])) . $root . "activate.php?confid=" . $id . "&key=" . $key;

				$c = file_get_contents($filePath . "mails/confirmaccount.htm");
				$c =str_replace("%name%", $username, $c);
				$c =str_replace("%link%", $link, $c);

				if( sendMail(array($mail => $username), "Peer Review - Aktivierung des Accounts", $c) ){
					echo "<span class=\"alert alert-success\">Der E-Mail Adresse $mail wurde ein Aktivierungslink gesendet.</span>";
				}
			}

		} else {
			echo "<span class=\"alert alert-danger\"><strong>Fehler</strong>: Ein Benutzer mit dieser E-Mail-Adresse existiert bereits.<a href=\"" . $ROOT_SITE . "login\">Möchtest du dich einloggen?</a></span>";
		}


	}
?>

<body>
	<script type="text/javascript" src="https://code.jquery.com/jquery-2.1.1.min.js"></script>
	<script type="text/javascript" src="../js/materialize.min.js"></script>
	<div class="navbar-fixed">
		<nav>
			<div class="nav-wrapper grey darken-3">
			  <a href="#" class="brand-logo center">Peer Review</a>
			</div>
		</nav>
	</div>
<?php
if (!isset($_SESSION["is_auth"])) {
	?>
	<div class="container" style="margin-top: 100px;">
		<?php
			if (isset($_POST['user-create'])) {
				if (!isset($_POST['name']) || !isset($_POST['pass']) || !isset($_POST['mail'])) {
					echo "<span>Fülle bitte das gesamte Formular aus!</span>";
				} else {
					handleValidSignUp($conn, $_POST["name"], $_POST["pass"], $_POST["mail"], $ROOT_SITE, $filePath);
				}
			}
		?>
		<h4 class="section-heading">Account erstellen</h4>
		<div id="form-wrapper" class="z-depth-1 grey lighten-4 row" style="width: 70%; display: inline-block; padding: 32px 48px 0px 48px; border: 1px solid #EEE;">
			<form class="col l12" method="post">
				<div class='row'>
					<div class='input-field col s12'>
						<input class='validate' type='text' name='name' id='name' />
						<label for='name'>Name</label>
					</div>
				</div>
				<div class='row'>
					<div class='input-field col s12'>
						<input class='validate' type='password' name='pass' id='pass' />
						<label for='pass'>Password</label>
					</div>
				</div>
				<div class='row'>
					<div class='input-field col s12'>
						<input class='validate' type='email' name='mail' id='mail' />
						<label for='mail'>E-Mail</label>
					</div>
				</div>
				<br />
				<center>
					<div class='row'>
						<button type='submit' name='user-create' class='col s12 btn btn-large waves-effect indigo'>Sign Up</button>
					</div>
				</center>
			</form>
		</div>
	  <a href="<?php echo $ROOT_SITE;?>login/">Du hast bereits ein Konto? Login</a>
	  </div>

<?php
} else {
	header("Location: $ROOT_SITE");
}
?>		
</body>
</html>