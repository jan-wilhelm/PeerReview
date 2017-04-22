<?php
include '../config.php';

$filePath = $IS_LOCAL ? "../" : "../../info/";

include $filePath. "review.php";
$conn = new mysqli($cfg['db_host'], $cfg['db_user'], $cfg['db_password'], $cfg['db_name']);

if ($conn->connect_error) {
	die("Database connection failed: " . $conn->connect_error);
}
include $filePath. 'check_auth.php';
include $filePath. "header.php";
?>
<body>
  	<div class="absolute-centered container text-center">
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
			<form action="" method="post" class="form-horizontal sign-up-course-form center-align absolute-centered">
				<h3 class="center">Für Kurs eintragen</h4>
				<span>Hier kannst du dich mit einem <code>Berechtigungsschlüssel</code> in einen Kurs eintragen. Wenn dir dieser Schlüssel nicht bekannt ist, frage bitte deinem Lehrer danach.</span>
				<input name="course-key" type="text" placeholder="Key" class="center form-control">
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