<?php

include '../check_auth.php';
include '../config.php';
include "../review.php";
if(!isset($_GET['id'])) {
	header("Location: /info");
}
if (!isset($_SESSION['user_level']) || $_SESSION['user_level'] > 1) {
	header("Location: /info");
	exit;
}
$conn = new mysqli($cfg['db_host'], $cfg['db_user'], $cfg['db_password'], $cfg['db_name']);

if ($conn->connect_error) {
	die("Database connection failed: " . $conn->connect_error);
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
  	<div class="element welcomer">
  		<?php
  			if(!userExists($conn, $target['id'])) {
  				echo "<h1 class=\"red-text\">Es existiert kein Benutzer mit der ID ".$target['id']."</h1></div></div></body></html>";
  				exit;
  			}
  		?>
  		<h2 class="header">Benutzer <span class="red-text lighten-2"><?php echo $target["name"]?></span> bearbeiten</h2>
  		<div class="edit-el">
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
		<div class="edit-el">
  			<h4>Reviews für <?php echo $target["name"]; ?></h4>
		<?php
			$review = getReviews($conn, $target["id"]);
			if(count($review) == 0) {
				echo "<p class=\"red-text darken-4\">Es wurde für ".$target["name"]." noch keine Review ausgefüllt!</hp>";
			} else {?>
				<ul class="collapsible" data-collapsible="expandable">
				<?php
				foreach ($review as $rv) {
				if(!is_null($rv['isset'])) {
					    ?>
					    <li>
						    <div class="collapsible-header">
						    	<i class="material-icons circle left-align">chat_bubble</i>
						     	<a class="red-text darken-4">Review von 
								<?php
								echo getName($conn, $rv['code_reviewer']) . "</a>   (Klick)"?>
							</div>
						<div class="collapsible-body indigo lighten-5">
							<h4 class="green-text lighten-2"><?php
							$points = getPoints($conn,$rv['id'],$rv['code_reviewer']);
							echo $points." von 45 Punkten (".((int)(100 * $points / 45))."%)"?></h4>

							<!-- MAKE MOVE -->
							<div class="sect">
								<p>makeMove(board,row,col)</p>

								<div class="cat">
								<span class="desc">Überprüft, ob board[row][col] frei ist.</span>
								<div class="points">
								<span><?php echo $rv['make_move_0'];?>/1</span>
								</div>
								</div>

								<div class="cat">
								<span class="desc">Setzt dann je nach Spieler board[row][col] = +/- 1.</span>
								<div class="points">
								<span><?php echo $rv['make_move_1'];?>/1</span>
								</div>

								</div>
								<div class="cat">
								<span class="comment"><?php echo $rv['make_move_c'];?></span>
								</div>

							</div>



							<!-- CHANGEPLAYER -->
							<div class="sect">
								<p>changePlayer()</p>

								<div class="cat">
								<span class="desc">Wechselt die globale Variable player.</span>
								<div class="points">
								<span><?php echo $rv['change_player_0'];?>/2</span>
								</div>
								</div>
								<div class="cat">
								<span class="comment"><?php echo $rv['change_player_c'];?></span>
								</div>

							</div>


							<!-- testLine -->
							<div class="sect">
								<p>testLine( line )</p>

								<div class="cat">
								<span class="desc">Rückgabe 1, wenn alle 3 Einträge == 1.</span>
								<div class="points">
								<span><?php echo $rv['test_line_0'];?>/1</span>
								</div>
								</div>

								<div class="cat">
								<span class="desc">Rückgabe -1, wenn alle 3 Einträge == -1.</span>
								<div class="points">
								<span><?php echo $rv['test_line_1'];?>/1</span>
								</div>
								</div>


								<div class="cat">
								<span class="desc">Rückgabe 0, sonst.</span>
								<div class="points">
								<span><?php echo $rv['test_line_2'];?>/1</span>
								</div>
								</div>

								<div class="cat">
								<span class="comment"><?php echo $rv['test_line_c'];?></span>
								</div>

							</div>




							<!-- testWin -->
							<div class="sect">
								<p>testWin( board )</p>

								<div class="cat">
								<span class="desc">Alle Zeilen richtig getestet.</span>
								<div class="points">
								<span><?php echo $rv['test_win_0'];?>/2</span>
								</div>
								</div>

								<div class="cat">
								<span class="desc">Alle Spalten richtig getestet.</span>
								<div class="points">
								<span><?php echo $rv['test_win_1'];?>/2</span>
								</div>
								</div>

								<div class="cat">
								<span class="desc">Alle Diagonalen richtig getestet.</span>
								<div class="points">
								<span><?php echo $rv['test_win_2'];?>/2</span>
								</div>
								</div>

								<div class="cat">
								<span class="desc">Winner richtig gesetzt.</span>
								<div class="points">
								<span><?php echo $rv['test_win_3'];?>/2</span>
								</div>
								</div>

								<div class="cat">
								<span class="desc">Richtige Rückgabe True/False</span>
								<div class="points">
								<span><?php echo $rv['test_win_4'];?>/1</span>
								</div>
								</div>

								<div class="cat">
								<span class="comment"><?php echo $rv['test_win_c'];?></span>
								</div>

							</div>


							<!-- endGame -->
							<div class="sect">
								<p>endGame( board )</p>

								<div class="cat">
								<span class="desc">Ruft testWin() auf.</span>
								<div class="points">
								<span><?php echo $rv['end_game_0'];?>/1</span>
								</div>
								</div>

								<div class="cat">
								<span class="desc">Testet, ob noch Felder frei sind</span>
								<div class="points">
								<span><?php echo $rv['end_game_1'];?>/4</span>
								</div>
								</div>

								<div class="cat">
								<span class="desc">Richtige Rückgabe True/False</span>
								<div class="points">
								<span><?php echo $rv['end_game_2'];?>/1</span>
								</div>
								</div>

								<div class="cat">
								<span class="comment"><?php echo $rv['end_game_c'];?></span>
								</div>

							</div>


							<!-- convertMove -->
							<div class="sect">
								<p>convertMove()</p>

								<div class="cat">
								<span class="desc">Festlegung auf sinnvolles Eingabeformat</span>
								<div class="points">
								<span><?php echo $rv['convert_move_0'];?>/2</span>
								</div>
								</div>

								<div class="cat">
								<span class="desc">Richtige Umwandlung in Reihe und Spalte</span>
								<div class="points">
								<span><?php echo $rv['convert_move_1'];?>/2</span>
								</div>
								</div>

								<div class="cat">
								<span class="desc">Fehlerbehandlung (Rückgabe (-1,-1)) bei falscher Eingabe</span>
								<div class="points">
								<span><?php echo $rv['convert_move_2'];?>/1</span>
								</div>
								</div>

								<div class="cat">
								<span class="comment"><?php echo $rv['convert_move_c'];?></span>
								</div>

							</div>




							<!-- getMove -->
							<div class="sect">
								<p>getMove()</p>

								<div class="cat">
								<span class="desc">Ruft convertMove() auf, um Reihe und Spalte zu bekommen</span>
								<div class="points">
								<span><?php echo $rv['get_move_0'];?>/1</span>
								</div>
								</div>

								<div class="cat">
								<span class="desc">Testet, ob Move legal war</span>
								<div class="points">
								<span><?php echo $rv['get_move_1'];?>/1</span>
								</div>
								</div>

								<div class="cat">
								<span class="desc">Ruft makeMove() auf</span>
								<div class="points">
								<span><?php echo $rv['get_move_2'];?>/1</span>
								</div>
								</div>

								<div class="cat">
								<span class="desc">Testet, ob endGame()</span>
								<div class="points">
								<span><?php echo $rv['get_move_3'];?>/1</span>
								</div>
								</div>

								<div class="cat">
								<span class="desc">Gibt ggf. Gewinner / Unentschieden aus</span>
								<div class="points">
								<span><?php echo $rv['get_move_4'];?>/2</span>
								</div>
								</div>

								<div class="cat">
								<span class="desc">Ruft changePlayer() auf</span>
								<div class="points">
								<span><?php echo $rv['get_move_5'];?>/1</span>
								</div>
								</div>

								<div class="cat">
								<span class="desc">spielerAnzeige wird entsprechend Spieler angepasst.</span>
								<div class="points">
								<span><?php echo $rv['get_move_6'];?>/1</span>
								</div>
								</div>

								<div class="cat">
								<span class="comment"><?php echo $rv['get_move_c'];?></span>
								</div>

							</div>




							<!-- stil -->
							<div class="sect">
								<p>Code-Stil</p>

								<div class="cat">
								<span class="desc">Alle Funktionen ausführlich getestet.</span>
								<div class="points">
								<span><?php echo $rv['stil_0'];?>/5</span>
								</div>
								</div>

								<div class="cat">
								<span class="desc">Alle Funktionen vollständig kommentiert (Docstrings)</span>
								<div class="points">
								<span><?php echo $rv['stil_1'];?>/4</span>
								</div>
								</div>

								<div class="cat">
								<span class="desc">Ein sinnvolles assert-statement eingefügt.</span>
								<div class="points">
								<span><?php echo $rv['stil_2'];?>/1</span>
								</div>
								</div>

								<div class="cat">
								<span class="comment"><?php echo $rv['stil_c'];?></span>
								</div>

							</div>


						</div>
						</li>
				<?php
					} else {?>
						<div class="collapsible-header">
					    	<i class="material-icons circle left-align red-text darken-4">error</i>
					     	<a class="red-text darken-4">Ausstehende Review von 
							<?php
							echo getName($conn, $rv['code_reviewer']) . "</a>"?>
						</div>
						<?php
					}
				}
				echo "</ul>";
			}
		?>
		</div>
		<div class="edit-el">
  			<h4>Reviews von <?php echo $target["name"]; ?></h4>
			<?php
			    $tar = getReviewsBy($conn, $target['id']);
			    # noch kein target
			    if (count($tar) == 0) {
					echo "<p class=\"red-text darken-4\">Es wurde für ".$target["name"]." noch kein Benutzer zum Review ausgewählt!</p>";
			    } else {?>
					<ul class="collapsible" data-collapsible="expandable"><?php
			    	foreach ($tar as $rv) {
					if(!is_null($rv['isset'])) {
						    ?>
						    <li>
						    <div class="collapsible-header">
						    	<i class="material-icons circle left-align">chat_bubble</i>
						     	<a class="red-text darken-4">Review für 
								<?php
								echo getName($conn, $rv['id']) . "</a>   (Klick)"?>
							</div>
							<div class="collapsible-body indigo lighten-5">


							<!-- MAKE MOVE -->
							<div class="sect">
								<p>makeMove(board,row,col)</p>

								<div class="cat">
								<span class="desc">Überprüft, ob board[row][col] frei ist.</span>
								<div class="points">
								<span><?php echo $rv['make_move_0'];?>/1</span>
								</div>
								</div>

								<div class="cat">
								<span class="desc">Setzt dann je nach Spieler board[row][col] = +/- 1.</span>
								<div class="points">
								<span><?php echo $rv['make_move_1'];?>/1</span>
								</div>

								</div>
								<div class="cat">
								<span class="comment"><?php echo $rv['make_move_c'];?></span>
								</div>

							</div>



							<!-- CHANGEPLAYER -->
							<div class="sect">
								<p>changePlayer()</p>

								<div class="cat">
								<span class="desc">Wechselt die globale Variable player.</span>
								<div class="points">
								<span><?php echo $rv['change_player_0'];?>/2</span>
								</div>
								</div>
								<div class="cat">
								<span class="comment"><?php echo $rv['change_player_c'];?></span>
								</div>

							</div>


							<!-- testLine -->
							<div class="sect">
								<p>testLine( line )</p>

								<div class="cat">
								<span class="desc">Rückgabe 1, wenn alle 3 Einträge == 1.</span>
								<div class="points">
								<span><?php echo $rv['test_line_0'];?>/1</span>
								</div>
								</div>

								<div class="cat">
								<span class="desc">Rückgabe -1, wenn alle 3 Einträge == -1.</span>
								<div class="points">
								<span><?php echo $rv['test_line_1'];?>/1</span>
								</div>
								</div>


								<div class="cat">
								<span class="desc">Rückgabe 0, sonst.</span>
								<div class="points">
								<span><?php echo $rv['test_line_2'];?>/1</span>
								</div>
								</div>

								<div class="cat">
								<span class="comment"><?php echo $rv['test_line_c'];?></span>
								</div>

							</div>




							<!-- testWin -->
							<div class="sect">
								<p>testWin( board )</p>

								<div class="cat">
								<span class="desc">Alle Zeilen richtig getestet.</span>
								<div class="points">
								<span><?php echo $rv['test_win_0'];?>/2</span>
								</div>
								</div>

								<div class="cat">
								<span class="desc">Alle Spalten richtig getestet.</span>
								<div class="points">
								<span><?php echo $rv['test_win_1'];?>/2</span>
								</div>
								</div>

								<div class="cat">
								<span class="desc">Alle Diagonalen richtig getestet.</span>
								<div class="points">
								<span><?php echo $rv['test_win_2'];?>/2</span>
								</div>
								</div>

								<div class="cat">
								<span class="desc">Winner richtig gesetzt.</span>
								<div class="points">
								<span><?php echo $rv['test_win_3'];?>/2</span>
								</div>
								</div>

								<div class="cat">
								<span class="desc">Richtige Rückgabe True/False</span>
								<div class="points">
								<span><?php echo $rv['test_win_4'];?>/1</span>
								</div>
								</div>

								<div class="cat">
								<span class="comment"><?php echo $rv['test_win_c'];?></span>
								</div>

							</div>


							<!-- endGame -->
							<div class="sect">
								<p>endGame( board )</p>

								<div class="cat">
								<span class="desc">Ruft testWin() auf.</span>
								<div class="points">
								<span><?php echo $rv['end_game_0'];?>/1</span>
								</div>
								</div>

								<div class="cat">
								<span class="desc">Testet, ob noch Felder frei sind</span>
								<div class="points">
								<span><?php echo $rv['end_game_1'];?>/4</span>
								</div>
								</div>

								<div class="cat">
								<span class="desc">Richtige Rückgabe True/False</span>
								<div class="points">
								<span><?php echo $rv['end_game_2'];?>/1</span>
								</div>
								</div>

								<div class="cat">
								<span class="comment"><?php echo $rv['end_game_c'];?></span>
								</div>

							</div>


							<!-- convertMove -->
							<div class="sect">
								<p>convertMove()</p>

								<div class="cat">
								<span class="desc">Festlegung auf sinnvolles Eingabeformat</span>
								<div class="points">
								<span><?php echo $rv['convert_move_0'];?>/2</span>
								</div>
								</div>

								<div class="cat">
								<span class="desc">Richtige Umwandlung in Reihe und Spalte</span>
								<div class="points">
								<span><?php echo $rv['convert_move_1'];?>/2</span>
								</div>
								</div>

								<div class="cat">
								<span class="desc">Fehlerbehandlung (Rückgabe (-1,-1)) bei falscher Eingabe</span>
								<div class="points">
								<span><?php echo $rv['convert_move_2'];?>/1</span>
								</div>
								</div>

								<div class="cat">
								<span class="comment"><?php echo $rv['convert_move_c'];?></span>
								</div>

							</div>




							<!-- getMove -->
							<div class="sect">
								<p>getMove()</p>

								<div class="cat">
								<span class="desc">Ruft convertMove() auf, um Reihe und Spalte zu bekommen</span>
								<div class="points">
								<span><?php echo $rv['get_move_0'];?>/1</span>
								</div>
								</div>

								<div class="cat">
								<span class="desc">Testet, ob Move legal war</span>
								<div class="points">
								<span><?php echo $rv['get_move_1'];?>/1</span>
								</div>
								</div>

								<div class="cat">
								<span class="desc">Ruft makeMove() auf</span>
								<div class="points">
								<span><?php echo $rv['get_move_2'];?>/1</span>
								</div>
								</div>

								<div class="cat">
								<span class="desc">Testet, ob endGame()</span>
								<div class="points">
								<span><?php echo $rv['get_move_3'];?>/1</span>
								</div>
								</div>

								<div class="cat">
								<span class="desc">Gibt ggf. Gewinner / Unentschieden aus</span>
								<div class="points">
								<span><?php echo $rv['get_move_4'];?>/2</span>
								</div>
								</div>

								<div class="cat">
								<span class="desc">Ruft changePlayer() auf</span>
								<div class="points">
								<span><?php echo $rv['get_move_5'];?>/1</span>
								</div>
								</div>

								<div class="cat">
								<span class="desc">spielerAnzeige wird entsprechend Spieler angepasst.</span>
								<div class="points">
								<span><?php echo $rv['get_move_6'];?>/1</span>
								</div>
								</div>

								<div class="cat">
								<span class="comment"><?php echo $rv['get_move_c'];?></span>
								</div>

							</div>




							<!-- stil -->
							<div class="sect">
								<p>Code-Stil</p>

								<div class="cat">
								<span class="desc">Alle Funktionen ausführlich getestet.</span>
								<div class="points">
								<span><?php echo $rv['stil_0'];?>/5</span>
								</div>
								</div>

								<div class="cat">
								<span class="desc">Alle Funktionen vollständig kommentiert (Docstrings)</span>
								<div class="points">
								<span><?php echo $rv['stil_1'];?>/4</span>
								</div>
								</div>

								<div class="cat">
								<span class="desc">Ein sinnvolles assert-statement eingefügt.</span>
								<div class="points">
								<span><?php echo $rv['stil_2'];?>/1</span>
								</div>
								</div>

								<div class="cat">
								<span class="comment"><?php echo $rv['stil_c'];?></span>
								</div>

							</div>


						</div>
							</li>
					<?php
						} else {?>
							<div class="collapsible-header">
						    	<i class="material-icons circle left-align red-text darken-4">error</i>
						     	<a class="red-text darken-4">Austehende Review für 
								<?php
								echo getName($conn, $rv['id']) . "</a>"?>
							</div>
							<?php
						}
					}?>
					</ul><?php
			    }
			?>
		</div>
	</div>
</body>
</html>
<?php
$conn->close();
?>