<?php
include '../config.php';
include "../review.php";
$conn = new mysqli($cfg['db_host'], $cfg['db_user'], $cfg['db_password'], $cfg['db_name']);

if ($conn->connect_error) {
	die("Database connection failed: " . $conn->connect_error);
}
include '../check_auth.php';
include "../header.php";
?>
<body>
	<nav class="navbar navbar-inverse navbar-fixed-top">
      <div class="container-fluid">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="true" aria-controls="navbar">
            <span class="sr-only">Navigation ein-/ausblenden</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="#">Projekt-Titel</a>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
          <ul class="nav navbar-nav navbar-right">
            <li><a href="#">Dashboard</a></li>
            <li><a href="#">Einstellungen</a></li>
            <li><a href="#">Profil</a></li>
            <li><a href="#">Hilfe</a></li>
          </ul>
          <form class="navbar-form navbar-right">
            <input type="text" class="form-control" placeholder="Suchen...">
          </form>
        </div>
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