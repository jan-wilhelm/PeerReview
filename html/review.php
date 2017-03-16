<?php

include '../../info/check_auth.php';
include '../../info/config.php';
include "../../info/review.php";
if(!isset($_GET['id'])) {
	header("Location: /info");
}
$conn = new mysqli($cfg['db_host'], $cfg['db_user'], $cfg['db_password'], $cfg['db_name']);
if ($conn->connect_error) {
	die("Database connection failed: " . $conn->connect_error);
}
$contains = false;
foreach (getReviewTargets($conn, $_SESSION['user_id']) as $tar) {
	if($tar['id'] == $_GET['id']) {
		$contains = true;
	}
}
if (!$contains) {
	header("Location: /info");
	exit;
}

$target = array(
	"id" => $_GET['id'],
	"name" => getName($conn,$_GET['id']),
	"code" => getCode($conn,$_GET['id'])
);
?>
<!DOCTYPE html>
<html lang="en">
<head>

  <meta charset="utf-8">
  <title>Informatik Peer Review</title>
  <meta name="description" content="">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  
  <!-- CSS  -->
	<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
	<link href="css/materialize.css" type="text/css" rel="stylesheet" media="screen,projection"/>
	<link rel="stylesheet" type="text/css" href="css/font-awesome.min.css">
	<link rel="stylesheet" type="text/css" href="css/font-awesome.min.css">
	<link rel="stylesheet" type="text/css" href="style.css">

</head>
<body>
    <script type="text/javascript" src="https://code.jquery.com/jquery-2.1.1.min.js"></script>
    <script type="text/javascript" src="js/materialize.min.js"></script>
    <div class="navbar-fixed">
		<nav>
		    <div class="nav-wrapper grey darken-3">
		      <a href="#" class="brand-logo center">Informatik Peer Review</a>
		      <ul id="nav-mobile" class="right hide-on-med-and-down">
		        <li>
		        <a href="logout.php">Logout
		        <i class="material-icons right">power_settings_new</i>
		        </a>
		        </li>
		        <li>
		        <a href="index.php">Home
		        <i class="material-icons right">home</i>
		        </a>
		        </li>
		      </ul>
		    </div>
  		</nav>
  	</div>
  <div class="container">
  	<div class="element welcomer z-depth-4">
  		<h2 class="header">Review für <span class="red-text lighten-2"><?php echo $target["name"]?></span> verfassen</h2>
  		<span style="text-align: justify;">
  			Hier kannst du dein Review für den angegeben Benutzer bearbeiten oder verfassen.<br>
  			Bitte halte dich an die Beschreibung der (Kritik-)Punkte und <i>bewerte ernsthaft</i>.
  		</span>
  	</div>
  	<?php

	if(isset($_POST['save-review'])) {
		$c = $conn;
		$i = $target['id'];
		$a = $_SESSION['user_id'];
		$v = $_POST;
		setReviewParam($c,$i, $a, 'make_move_0', $v['make_move_0']);
		setReviewParam($c,$i, $a, 'make_move_1', $v['make_move_1']);
		setReviewParam($c,$i, $a, 'make_move_c', $v['make_move_c']);
		setReviewParam($c,$i, $a, 'change_player_0', $v['change_player_0']);
		setReviewParam($c,$i, $a, 'change_player_c', $v['change_player_c']);
		setReviewParam($c,$i, $a, 'test_line_0', $v['test_line_0']);
		setReviewParam($c,$i, $a, 'test_line_1', $v['test_line_1']);
		setReviewParam($c,$i, $a, 'test_line_2', $v['test_line_2']);
		setReviewParam($c,$i, $a, 'test_line_c', $v['test_line_c']);
		setReviewParam($c,$i, $a, 'test_win_0', $v['test_win_0']);
		setReviewParam($c,$i, $a, 'test_win_1', $v['test_win_1']);
		setReviewParam($c,$i, $a, 'test_win_2', $v['test_win_2']);
		setReviewParam($c,$i, $a, 'test_win_3', $v['test_win_3']);
		setReviewParam($c,$i, $a, 'test_win_4', $v['test_win_4']);
		setReviewParam($c,$i, $a, 'test_win_c', $v['test_win_c']);
		setReviewParam($c,$i, $a, 'end_game_0', $v['end_game_0']);
		setReviewParam($c,$i, $a, 'end_game_1', $v['end_game_1']);
		setReviewParam($c,$i, $a, 'end_game_2', $v['end_game_2']);
		setReviewParam($c,$i, $a, 'end_game_c', $v['end_game_c']);
		setReviewParam($c,$i, $a, 'convert_move_0', $v['convert_move_0']);
		setReviewParam($c,$i, $a, 'convert_move_1', $v['convert_move_1']);
		setReviewParam($c,$i, $a, 'convert_move_2', $v['convert_move_2']);
		setReviewParam($c,$i, $a, 'convert_move_c', $v['convert_move_c']);
		setReviewParam($c,$i, $a, 'get_move_0', $v['get_move_0']);
		setReviewParam($c,$i, $a, 'get_move_1', $v['get_move_1']);
		setReviewParam($c,$i, $a, 'get_move_2', $v['get_move_2']);
		setReviewParam($c,$i, $a, 'get_move_3', $v['get_move_3']);
		setReviewParam($c,$i, $a, 'get_move_4', $v['get_move_4']);
		setReviewParam($c,$i, $a, 'get_move_5', $v['get_move_5']);
		setReviewParam($c,$i, $a, 'get_move_6', $v['get_move_6']);
		setReviewParam($c,$i, $a, 'get_move_c', $v['get_move_c']);
		setReviewParam($c,$i, $a, 'stil_0', $v['stil_0']);
		setReviewParam($c,$i, $a, 'stil_1', $v['stil_1']);
		setReviewParam($c,$i, $a, 'stil_2', $v['stil_2']);
		setReviewParam($c,$i, $a, 'stil_c', $v['stil_c']);
		echo "<div class=\"element z-depth-4\"><span class=\"green-text darken-2\">Vielen Dank, dass du deine Bewertung abgegeben hast!</span></div>";
	}

  	?>
	<div class="element z-depth-4">
  			<h4>Link zum Code</h4>
		<?php
			$code = getCode($conn, $target['id']);
			if(is_null($code) or empty($code)) {
				echo "<span class=\"red-text darken-4\">".$target["name"]." hat noch keinen Link angegeben</span>";
    	    } else {
				echo "<span>Link zum Code von ".$target["name"].": <a class=\"red-text darken-4\" href=\"".$code."\" target=\"_blank\">Hier klicken</a></span>";
    	    }
		?>
	</div>
	<div class="element z-depth-4">
  		<h4>Review verfassen</h4>
  		<br>
		<?php
			$rv = getReview($conn, $target["id"], $_SESSION['user_id']);
		?>
		<form action="" method="post">

			<!-- MAKE MOVE -->
			<div class="sect">
				<p>makeMove(board,row,col)</p>

				<div class="cat">
				<span class="desc">Überprüft, ob board[row][col] frei ist.</span>
				<div class="points">
				<input type="number" max="1" min="0" name="make_move_0" value=<?php echo "\"".$rv['make_move_0']."\"";?> >
				<span>/1</span>
				</div>
				</div>

				<div class="cat">
				<span class="desc">Setzt dann je nach Spieler board[row][col] = +/- 1.</span>
				<div class="points">
				<input type="number" max="1" min="0" name="make_move_1" value=<?php echo "\"".$rv['make_move_0']."\"";?> >
				<span>/1</span>
				</div>

				</div>
				<div class="cat">
				<span class="desc">Kommentar makeMove</span>
        		<div class="input-field">
				<textarea class="materialize-textarea" name="make_move_c" placeholder="Kommentar"><?php echo $rv['make_move_c'];?></textarea>
				</div>
				</div>

			</div>



			<!-- CHANGEPLAYER -->
			<div class="sect">
				<p>changePlayer()</p>

				<div class="cat">
				<span class="desc">Wechselt die globale Variable player.</span>
				<div class="points">
				<input type="number" max="2" min="0" name="change_player_0" value=<?php echo "\"".$rv['change_player_0']."\"";?> >
				<span>/2</span>
				</div>
				</div>
				<div class="cat">
				<span class="desc">Kommentar changePlayer</span>
				<textarea class="materialize-textarea" name="change_player_c" placeholder="Kommentar"><?php echo $rv['change_player_c'];?></textarea>
				</div>

			</div>


			<!-- testLine -->
			<div class="sect">
				<p>testLine( line )</p>

				<div class="cat">
				<span class="desc">Rückgabe 1, wenn alle 3 Einträge == 1.</span>
				<div class="points">
				<input type="number" max="1" min="0" name="test_line_0" value=<?php echo "\"".$rv['test_line_0']."\"";?> >
				<span>/1</span>
				</div>
				</div>

				<div class="cat">
				<span class="desc">Rückgabe -1, wenn alle 3 Einträge == -1.</span>
				<div class="points">
				<input type="number" max="1" min="0" name="test_line_1" value=<?php echo "\"".$rv['test_line_1']."\"";?> >
				<span>/1</span>
				</div>
				</div>


				<div class="cat">
				<span class="desc">Rückgabe 0, sonst.</span>
				<div class="points">
				<input type="number" max="1" min="0" name="test_line_2" value=<?php echo "\"".$rv['test_line_2']."\"";?> >
				<span>/1</span>
				</div>
				</div>

				<div class="cat">
				<span class="desc">Kommentar testLine</span>
				<textarea class="materialize-textarea" name="test_line_c" placeholder="Kommentar"><?php echo $rv['test_line_c'];?></textarea>
				</div>

			</div>




			<!-- testWin -->
			<div class="sect">
				<p>testWin( board )</p>

				<div class="cat">
				<span class="desc">Alle Zeilen richtig getestet.</span>
				<div class="points">
				<input type="number" max="2" min="0" name="test_win_0" value=<?php echo "\"".$rv['test_win_0']."\"";?> >
				<span>/2</span>
				</div>
				</div>

				<div class="cat">
				<span class="desc">Alle Spalten richtig getestet.</span>
				<div class="points">
				<input type="number" max="2" min="0" name="test_win_1" value=<?php echo "\"".$rv['test_win_1']."\"";?> >
				<span>/2</span>
				</div>
				</div>

				<div class="cat">
				<span class="desc">Alle Diagonalen richtig getestet.</span>
				<div class="points">
				<input type="number" max="2" min="0" name="test_win_2" value=<?php echo "\"".$rv['test_win_2']."\"";?> >
				<span>/2</span>
				</div>
				</div>

				<div class="cat">
				<span class="desc">Winner richtig gesetzt.</span>
				<div class="points">
				<input type="number" max="2" min="0" name="test_win_3" value=<?php echo "\"".$rv['test_win_3']."\"";?> >
				<span>/2</span>
				</div>
				</div>

				<div class="cat">
				<span class="desc">Richtige Rückgabe True/False</span>
				<div class="points">
				<input type="number" max="1" min="0" name="test_win_4" value=<?php echo "\"".$rv['test_win_4']."\"";?> >
				<span>/1</span>
				</div>
				</div>

				<div class="cat">
				<span class="desc">Kommentar testWin</span>
				<textarea class="materialize-textarea" name="test_win_c" placeholder="Kommentar"><?php echo $rv['test_win_c'];?></textarea>
				</div>

			</div>


			<!-- endGame -->
			<div class="sect">
				<p>endGame( board )</p>

				<div class="cat">
				<span class="desc">Ruft testWin() auf.</span>
				<div class="points">
				<input type="number" max="1" min="0" name="end_game_0" value=<?php echo "\"".$rv['end_game_0']."\"";?> >
				<span>/1</span>
				</div>
				</div>

				<div class="cat">
				<span class="desc">Testet, ob noch Felder frei sind</span>
				<div class="points">
				<input type="number" max="4" min="0" name="end_game_1" value=<?php echo "\"".$rv['end_game_1']."\"";?> >
				<span>/4</span>
				</div>
				</div>

				<div class="cat">
				<span class="desc">Richtige Rückgabe True/False</span>
				<div class="points">
				<input type="number" max="1" min="0" name="end_game_2" value=<?php echo "\"".$rv['end_game_2']."\"";?> >
				<span>/1</span>
				</div>
				</div>

				<div class="cat">
				<span class="desc">Kommentar endGame</span>
				<textarea class="materialize-textarea" name="end_game_c" placeholder="Kommentar"><?php echo $rv['end_game_c'];?></textarea>
				</div>

			</div>


			<!-- convertMove -->
			<div class="sect">
				<p>convertMove()</p>

				<div class="cat">
				<span class="desc">Festlegung auf sinnvolles Eingabeformat</span>
				<div class="points">
				<input type="number" max="2" min="0" name="convert_move_0" value=<?php echo "\"".$rv['convert_move_0']."\"";?> >
				<span>/2</span>
				</div>
				</div>

				<div class="cat">
				<span class="desc">Richtige Umwandlung in Reihe und Spalte</span>
				<div class="points">
				<input type="number" max="2" min="0" name="convert_move_1" value=<?php echo "\"".$rv['convert_move_1']."\"";?> >
				<span>/2</span>
				</div>
				</div>

				<div class="cat">
				<span class="desc">Fehlerbehandlung (Rückgabe (-1,-1)) bei falscher Eingabe</span>
				<div class="points">
				<input type="number" max="1" min="0" name="convert_move_2" value=<?php echo "\"".$rv['convert_move_2']."\"";?> >
				<span>/1</span>
				</div>
				</div>

				<div class="cat">
				<span class="desc">Kommentar convertMove</span>
				<textarea class="materialize-textarea" name="convert_move_c" placeholder="Kommentar"><?php echo $rv['convert_move_c'];?></textarea>
				</div>

			</div>




			<!-- getMove -->
			<div class="sect">
				<p>getMove()</p>

				<div class="cat">
				<span class="desc">Ruft convertMove() auf, um Reihe und Spalte zu bekommen</span>
				<div class="points">
				<input type="number" max="1" min="0" name="get_move_0" value=<?php echo "\"".$rv['get_move_0']."\"";?> >
				<span>/1</span>
				</div>
				</div>

				<div class="cat">
				<span class="desc">Testet, ob Move legal war</span>
				<div class="points">
				<input type="number" max="1" min="0" name="get_move_1" value=<?php echo "\"".$rv['get_move_1']."\"";?> >
				<span>/1</span>
				</div>
				</div>

				<div class="cat">
				<span class="desc">Ruft makeMove() auf</span>
				<div class="points">
				<input type="number" max="1" min="0" name="get_move_2" value=<?php echo "\"".$rv['get_move_2']."\"";?> >
				<span>/1</span>
				</div>
				</div>

				<div class="cat">
				<span class="desc">Testet, ob endGame()</span>
				<div class="points">
				<input type="number" max="1" min="0" name="get_move_3" value=<?php echo "\"".$rv['get_move_3']."\"";?> >
				<span>/1</span>
				</div>
				</div>

				<div class="cat">
				<span class="desc">Gibt ggf. Gewinner / Unentschieden aus</span>
				<div class="points">
				<input type="number" max="2" min="0" name="get_move_4" value=<?php echo "\"".$rv['get_move_4']."\"";?> >
				<span>/2</span>
				</div>
				</div>

				<div class="cat">
				<span class="desc">Ruft changePlayer() auf</span>
				<div class="points">
				<input type="number" max="1" min="0" name="get_move_5" value=<?php echo "\"".$rv['get_move_5']."\"";?> >
				<span>/1</span>
				</div>
				</div>

				<div class="cat">
				<span class="desc">spielerAnzeige wird entsprechend Spieler angepasst.</span>
				<div class="points">
				<input type="number" max="1" min="0" name="get_move_6" value=<?php echo "\"".$rv['get_move_6']."\"";?> >
				<span>/1</span>
				</div>
				</div>

				<div class="cat">
				<span class="desc">Kommentar getMove</span>
				<textarea class="materialize-textarea" name="get_move_c" placeholder="Kommentar"><?php echo $rv['get_move_c'];?></textarea>
				</div>

			</div>




			<!-- stil -->
			<div class="sect">
				<p>Code-Stil</p>

				<div class="cat">
				<span class="desc">Alle Funktionen ausführlich getestet.</span>
				<div class="points">
				<input type="number" max="5" min="0" name="stil_0" value=<?php echo "\"".$rv['stil_0']."\"";?> >
				<span>/5</span>
				</div>
				</div>

				<div class="cat">
				<span class="desc">Alle Funktionen vollständig kommentiert (Docstrings)</span>
				<div class="points">
				<input type="number" max="4" min="0" name="stil_1" value=<?php echo "\"".$rv['stil_1']."\"";?> >
				<span>/4</span>
				</div>
				</div>

				<div class="cat">
				<span class="desc">Ein sinnvolles assert-statement eingefügt.</span>
				<div class="points">
				<input type="number" max="1" min="0" name="stil_2" value=<?php echo "\"".$rv['get_move_2']."\"";?> >
				<span>/1</span>
				</div>
				</div>

				<div class="cat">
				<span class="desc">Kommentar Code-Stil</span>
				<textarea class="materialize-textarea" name="stil_c" placeholder="Kommentar"><?php echo $rv['stil_c'];?></textarea>
				</div>

			</div>

			<br>
			<input type="submit" value="Bewertung speichern" class="btn waves-effect waves-light" name="save-review">
		</form>
	</div>
</body>
</html>
<?php
$conn->close();
?>