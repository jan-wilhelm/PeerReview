<?php
include '../check_auth.php';
include '../config.php';
include "../review.php";
include "../header.php";
$conn = new mysqli($cfg['db_host'], $cfg['db_user'], $cfg['db_password'], $cfg['db_name']);

if ($conn->connect_error) {
	die("Database connection failed: " . $conn->connect_error);
}
?>
<body>
    <script type="text/javascript" src="https://code.jquery.com/jquery-2.1.1.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.98.1/js/materialize.min.js"></script>
	<nav>
	    <div class="nav-wrapper grey darken-3" id="navbar">
	    	<a href="#" class="brand-logo center">Informatik Peer Review</a>
	    	<a href="#" data-activates="mobile-men" class="button-collapse"><i class="material-icons">menu</i></a>
	    	<ul class="right hide-on-med-and-down">
		        <li><a href="logout.php">Logout
		        <i class="material-icons right">power_settings_new</i></a>
	    	</ul>
	    	<ul class="side-nav" id="mobile-men">
		        <li><a href="logout.php">Logout
		        <i class="material-icons right">power_settings_new</i></a>
	    	</ul>
		</div>
	</nav>

  	<div class="absolute-centered container">
		<?php
		if (isset($_POST['sign-up']) && isset($_POST['course-key'])) {
			$result = handleKeyTyped($conn, $_SESSION['user_id'], trim($_POST['course-key']));
			$msg = "";
			$error = false;
			switch ($result) {
				case -3:
					$error = true;
					$msg = 'Es wurde kein Kurs für den Key gefunden!<br/><a href="signup.php">Zurück</a>';
					break;
				case -2:
					$error = true;
					$msg = 'Du bist bereits in dem Kurs!<br/><a href="signup.php">Zurück</a>';
					break;
				case -1:
					$error = true;
					$msg = 'Fehler beim Eintragen in den Kurs!<br/>Kontaktiere den Administrator: <a href="mailto:jan.b.h.wilhelm@gmx.de?subject=Fehler%20im%20ReviewSystem">E-Mail</a>';
					break;
				default:
					$courseId = getCourseByKey($conn, trim($_POST['course-key']));
					$course = getCourseName($conn, $courseId);
					$msg = 'Du wurdest in den Kurs '.$course.' erfolgreich eingetragen!<br/>Gehe hier zu der <a href="/?course='.$courseId.'">Seite des Kurses</a>';
					break;
			}
			$color = "red-text lighten-1";
			if($error == false) {
				$color = "green-text darken-2";
			}
			echo('<div class="row"><p class="element s12 center-align '.$color.'">'.$msg.'</p></div>');
		} else {?>
			<form action="" method="post" class="z-depth-4 element sign-up-course-form center-align absolute-centered">
				<h4 class="header center">Für Kurs eintragen</h4>
				<input name="course-key" type="text" placeholder="Key" class="center">
				<br/>
				<input type="submit" value="Eintragen" class="btn waves-effect waves-light" name="sign-up">
			</form>
		<?php
		}
		?>
	</div>
</body>
</html>
<?php
$conn->close();
?>