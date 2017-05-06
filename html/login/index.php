<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Login</title>
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

	if(!isset($_SESSION["info"])) {
		$_SESSION['info'] = array();
	}

	if(isset($_GET["ref"])) {
		$_SESSION["info"]["ref"] = rawurldecode($_GET["ref"]);
	}

	if (isset($_POST['login-submit'])) {
		
		if (isset($_POST['mail']) && isset($_POST['pass'])) {
			$name = $_POST['mail'];
			$pass = $_POST['pass'];
			// Create connection
			$conn = new mysqli($cfg['db_host'], $cfg['db_user'], $cfg['db_password'], $cfg['db_name']);
			// Check connection
			if ($conn->connect_error) {
				die("Database  connection failed: " . $conn->connect_error);
			}

			$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
			$stmt->bind_param("s", $name);
			$stmt->execute();
			if(!($result = $stmt->get_result())) {
				$error = "Could not fetch user information from database. Please contact Admin";
			}
			if ($result->num_rows > 0) {

				if($row = $result->fetch_assoc()) {
					if (password_verify($pass, $row["password"])) {

						$_SESSION['info']['is_auth'] = true;
						$_SESSION['info']['user_id'] = $row['id'];
						$_SESSION['info']['name'] = $row['name'];
						$_SESSION['info']['email'] = $row['email'];
						$_SESSION['info']['user_level'] = $row['level'];

						if($stmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id= ?")) {
							$stmt->bind_param("i", $row['id']);
							$stmt->execute();
							unset($stmt);
						}

						if($stmt = $conn->prepare("INSERT INTO login_history (for_date, user) VALUES (NOW(),?)")) {
							$stmt->bind_param("i", $row['id']);
							$stmt->execute();
							unset($stmt);
						}

						if(isset($_SESSION["info"]["ref"])) {
							$ref = $_SESSION['info']['ref'];
							header("Location: $ref");
							exit;
						}

						// Once the sessions variables have been set, redirect them to the landing page / home page.
						header('Location: '. $ROOT_SITE . "aa");
						exit;
					} else {
				$error = "Ungültige E-Mail-Adresse oder Passwort. Bitte nochmal versuchen!";
					}
				}
				$stmt->free_result();
			} else {
				$error = "Ungültige E-Mail-Adresse oder Passwort. Bitte nochmal versuchen!";
			}
			$stmt->free_result();
		} else {
			$error = "Bitte gib sowohl eine E-Mail-Adresse als auch ein Password ein!";
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
if (!isset($_SESSION["info"]["is_auth"])) {
	?>
	<div class="container">
		<?php
			if (isset($error)) {
				echo "<p class=\"element red-text darken-4\">$error</p>";
			}
		?>
		<h4 class="section-heading">Peer Review Login</h4>
		<div id="form-wrapper" class="z-depth-1 grey lighten-4 row" style="display: inline-block; padding: 32px 48px 0px 48px; border: 1px solid #EEE;">
			<form class="col s12" method="post">
				<div class='row'>
					<div class='input-field col s12'>
						<input class='validate' type='text' name='mail' id='mail' />
						<label for='mail'>E-Mail</label>
					</div>
				</div>
				<div class='row'>
					<div class='input-field col s12'>
						<input class='validate' type='password' name='pass' id='pass' />
						<label for='pass'>Passwort</label>
					</div>
					<label style='float: right;'><a class='pink-text' href='#!'><b>Forgot Password?</b></a></label>
				</div>
				<br />
				<center>
					<div class='row'>
						<button type='submit' name='login-submit' class='col s12 btn btn-large waves-effect indigo'>Login</button>
					</div>
				</center>
			</form>
		</div>
	  <a href="<?php echo $ROOT_SITE;?>signup/">Create account</a>
	  </div>

<?php
} else {
	header("Location: $ROOT_SITE");
}
?>		
</body>
</html>