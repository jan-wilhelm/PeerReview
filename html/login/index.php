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
	<link rel="stylesheet" type="text/css" href="../style.css">

</head>
<?php
	session_start();
	require '../../config.php';
	// echo password_hash("password", PASSWORD_DEFAULT)."\n";
	if (isset($_POST['login-submit'])) {
		if (isset($_POST['name']) && isset($_POST['pass'])) {
			$name = $_POST['name'];
			$pass = $_POST['pass'];
			// Create connection
			$conn = new mysqli($cfg['db_host'], $cfg['db_user'], $cfg['db_password'], $cfg['db_name']);
			// Check connection
			if ($conn->connect_error) {
			    die("Database  connection failed: " . $conn->connect_error);
			}

			$stmt = $conn->prepare("SELECT * FROM users WHERE name = ?");
			$stmt->bind_param("s", $name);
			$stmt->execute();
			if(!($result = $stmt->get_result())) {
				$error = "Could not fetch user information from database. Please contact Admin";
			}
			if ($result->num_rows > 0) {
			    // output data of each row
			    if($row = $result->fetch_assoc()) {
					if ($pass == $row["password"]) {
						// is_auth is important here because we will test this to make sure they can view other pages
						// that are needing credentials.
						$_SESSION['is_auth'] = true;
						$_SESSION['user_id'] = $row['id'];
						$_SESSION['name'] = $row['name'];
						$_SESSION['user_level'] = $row['level'];

						// Once the sessions variables have been set, redirect them to the landing page / home page.
						header('Location: /');
						exit;
					} else {
						$error = "Ungültiger Name oder Password. Bitte nochmal versuchen!";
					}
			    }
				$stmt->free_result();
			} else {
				$error = "Ungültiger Name oder Password. Bitte nochmal versuchen!";
			}
			$stmt->free_result();
		} else {
			$error = "Bitte gib sowohl einen Namen als auch ein Password ein!";
		}
	}
?>



<body>
    <script type="text/javascript" src="https://code.jquery.com/jquery-2.1.1.min.js"></script>
    <script type="text/javascript" src="../js/materialize.min.js"></script>
    <div class="navbar-fixed">
		<nav>
		    <div class="nav-wrapper grey darken-3">
		      <a href="#" class="brand-logo center">Informatik Peer Review</a>
		    </div>
  		</nav>
  	</div>
<?php
if (!isset($_SESSION["is_auth"])) {
	?>
	<div class="container">
		<?php
            if (isset($error)) {
                echo "<p class=\"element red-text darken-4\">$error</p>";
            }
        ?>
		<form class="element" method ="post" action="">
			<h4 class="section-heading"><span>Peer Review Login</span></h4>
			<div class="row">
				<div class="column">
					<label for="name">NAME </label>
					<input name="name" id="name" class="full-width" type="text" placeholder="Vorname">
				</div>
			</div>
			<div class="row">
				<div class="column">
					<label for="pass">PASSWORD </label>
					<input name="pass" id="password" class="full-width" type="password" placeholder="Password">
				</div>
			</div>
			<br/>
			<input type="submit" value="Anmelden" class="btn waves-effect waves-light red lighten-1" name="login-submit">
			
		</form>
	</div>
<?php
} else {
	header("Location: /");
}
?>		
</body>
</html>