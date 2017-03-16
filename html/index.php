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

<?php
include '../check_auth.php';
include '../config.php';
include "../review.php";
$conn = new mysqli($cfg['db_host'], $cfg['db_user'], $cfg['db_password'], $cfg['db_name']);

if ($conn->connect_error) {
	die("Database connection failed: " . $conn->connect_error);
}

function randomPassword($length){
	$alphabet    = 'abcdefghjkmnopqrstuvwxyzABCDEFGHJKMNOPQRSTUVWXYZ1234567890';
	$pass        = array();
	$alphaLength = strlen($alphabet) - 1;
	for ($i = 0; $i < $length; $i++) {
		$n      = rand(0, $alphaLength);
		$pass[] = $alphabet[$n];
	}
	return implode($pass);
}?>

<body>
    <script type="text/javascript" src="https://code.jquery.com/jquery-2.1.1.min.js"></script>
    <script type="text/javascript" src="js/materialize.min.js"></script>
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
  <div class="container">
	  <div class="element white z-depth-4 welcomer">
	    <?php
			echo "<h2 class=\"header\"> Hallo, " . $_SESSION["name"] . "!</h2>";
			echo "<p>Hier kannst du einen Link zu deinem Code hinterlegen,";
			echo " ein Review verfassen oder dein eigenes lesen.";
			echo "</p>";
	    ?>
	  </div>
	<?php
	if (isset($_SESSION['user_level']) && $_SESSION['user_level'] == 1) {
		if (isset($_POST['user-create'])) {
			if (!isset($_POST['username']) || !isset($_POST['password'])) {
				echo "<h5 class=\"element\">Fülle bitte das gesamte Formular aus!</h5>";
			} else {
				$stmt          = $conn->prepare("SELECT name FROM users WHERE name = ?");
				$new_user_name = $_POST['username'];
				$stmt->bind_param("s", $new_user_name);
				$stmt->execute();
				$result = $stmt->get_result();
				$found  = false;
				if ($result->num_rows > 0) {
					if ($row = $result->fetch_assoc()) {
						$found = true;
					}
				}
				if (!$found) {
					$stmt = $conn->prepare("INSERT INTO users (name, password) VALUES (?, ?)");
					$new_user_pass  = $_POST['password'];
					$stmt->bind_param("ss", $new_user_name, $new_user_pass);
					$stmt->execute();
					echo "<br><p>Benutzer <i>$new_user_name</i> wurde erstellt!</p><br>";
				} else {
					echo "<p class=\"element red-text darken-4\">Fehler: Ein Benutzer mit dem Namen $new_user_name existiert bereits.</p>";
				}
			}
		}
	?>
		<form action="" method="post" class="element z-depth-4">
			<h4 class="header">Neuen Nutzer erstellen</h4>
			<input name="username" type="text" placeholder="Username">
			<br/>
			<input name="password" type="text" placeholder="Password" value="<?php echo randomPassword(8); ?>">
			<br/>
			<input type="submit" value="Benutzer erstellen" class="btn waves-effect waves-light" name="user-create">
		</form>
		<div class="element z-depth-4">
			<h4 class="header">Edit users</h4>
			<ul class="collapsible" data-collapsible="accordion">
			<?php
			foreach (getUsers($conn) as $users) {
			?>
			    <li>
				    <div class="collapsible-header">
				    	<i class="material-icons circle left-align">perm_identity</i>
				     	<span class="red-text darken-4">Benutzer 
						<?php
						echo getName($conn, $users) . " bearbeiten </span>   (Klick)"?>
					</div>
					<div class="collapsible-body white"><?php
						echo "<a class=\"blue-text lighten-3\" href=\"user.php?id=$users\">Bearbeiten!</a>";
						?>
					</div>
				</li>
			<?php } ?>
			</ul>
		</div>

		<form action="" method="post" class="element z-depth-4 red lighten-3">
			<h4 class="header">Links verteilen</h4>
			<p>Hier kannst du die Lniks an die Benutzer verteilen, damit diese dann die Bewertungen verfassen könen. Bitte erst klicken, wenn alle Nutzer eingetragen sind!</p>
			<br />
			<input name="limit" value="3" type="number" id="set-codes-field"> 
			<input type="submit" id="set-codes" value="Codes verteilen" class="btn waves-effect waves-light" name="codes-set">

			<div id="modal1" class="modal red lighten-4">
		    <div class="modal-content">
		      <h4>Links verteilen</h4>
		      <p>Achtung: Die Verteilung kann später nicht mehr rückgängig gemacht werden! Bitte überprüfe deshalb gut, ob alle Benutzer erstellt sind, denn nur die zu diesem Zeitpunkt existierenden Benutzer können für die Reviews ausgewählt werden.</p>
		    </div>
		    <div class="modal-footer">
		      <a href="#!" class=" modal-action modal-close waves-effect waves-green btn-flat white-text green lighten-1" id="set-codes-agree">Verstanden</a>
		      <a href="#!" class=" modal-action modal-close waves-effect waves-green btn-flat red-text darken-2">Abbrechen</a>
		    </div>
		  </div>
		</form>
		<script type="text/javascript">
			$('#set-codes').click(function(event) {
				event.preventDefault();
				$('#modal1').modal({
			      dismissible: true, // Modal can be dismissed by clicking outside of the modal
			      opacity: .7, // Opacity of modal background
			      inDuration: 300, // Transition in duration
			      outDuration: 200, // Transition out duration
			      startingTop: '4%', // Starting top style attribute
			      endingTop: '10%', // Ending top style attribute
			    }).modal("open");
			});
			$('#set-codes-agree').click(function(){
			    var limit = $('#set-codes-field').val();
			    $.ajax({ 
			        url: 'index.php',
			        data: {
			        	"codes-set": null,
			        	"limit": limit
			    	},
			        type: 'post',
			        success: function(result) {
			           	Materialize.toast('Reviews wurden erfolgreich verteilt!', 3000, 'rounded green darken-2');
			        },
			        error: function(error) {
			           	Materialize.toast('Fehler! ' + error, 3000, 'rounded red darken-2');
			        }
		        });
		    });
		</script>
			<?php
	} // Ende von if Bedingung  User hat Level 1
	?>



    <!-- NORMAL -->
    <!-- NORMAL -->
    <!-- NORMAL -->
    <!-- NORMAL -->

		<?php
    	    if(isset($_POST['link-set']) && isset($_POST['link'])) {
    	    	setCode($conn, $_SESSION['user_id'], $_POST['link']);
    	    	echo "<p class=\"element green-text lighten-2 z-depth-4\">Vielen Dank dass du den Link zu deinem Code eingegeben hast!</p>";

    	    }
    	    if(isset($_POST['codes-set']) && isset($_POST['limit'])) {
    	    	if(!setTargets($conn, intval($_POST['limit']))) {
    	    		echo "<p class=\"element red-text darken-4 z-depth-4\">Die Codes konnten nicht verteilt werden. <br />Vielleicht wurden nicht genügend (4) Benutzer eingetragen?</p>";
    	    	} else {
    	    		echo "<p class=\"element green-text lighten-2 z-depth-4\">Die Codes wurden erfolgreich verteilt.<br />Jeder Schüler kann nun bewerten!</p>";
    	    	}
    	    }?>
			<form action="" method="post" class="element z-depth-4">
				<h4 class="header">Link zu deinem Code bearbeiten</h4>
				<span>
				Bitte gib den richtigen Link zu deinem Code ein, damit für dich ein Review verfasst werden kann!
				</span>
				<input  id="link-set-link" name="link" type="text" placeholder="Link" value=<?php echo "\"". getCode($conn, $_SESSION['user_id'])."\"";?>>
				<br/>
				<a class="waves-effect waves-light btn red darken-2"><i class="material-icons right">send</i>
						<input type="submit" name="link-set" value="Link absenden" id="link-set-button"></a>
			</form>
			
    		<?php
    		if($_SESSION['user_level'] !== 1) {			
				$review = getReviews($conn, $_SESSION['user_id']);
				echo "<div class=\"element z-depth-4\">";
				echo "<h4 class=\"header\">Deine Reviews</h4>";
				if(count($review) == 0) {
					echo "<p class=\"red-text darken-4\">Es wurde für dich noch keine Review ausgefüllt!</hp>";
				} else {?> <ul class="collapsible" data-collapsible="expandable"> <?php
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
					     	<a class="red-text darken-4">Du hast noch keine Review von 
							<?php
							echo getName($conn, $rv['code_reviewer']) . "</a>"?>
						</div>
						<?php
					}
					} echo "</ul>";
				}
			echo "</div>";
		?>
		<div class="element z-depth-4">
			<h4 class="header">Bewertungen verfassen</h4>
			<?php
			    $tar = getReviewTargets($conn, $_SESSION['user_id']);
			    # noch kein target
			    if($_SESSION['user_level'] === 1) {
					echo "<p class=\"red-text darken-4\">Als Administrator kannst du keine Reviews schreiben.</hp>";
				}elseif (count($tar) == 0) {
					echo "<p class=\"red-text darken-4\">Es wurde für dich noch kein Benutzer zum Review ausgewählt!</p>";
			    } else {
			    	?>
			    	<ul class="collapsible" data-collapsible="accordion"> <?php
					foreach ($tar as $rev) {
						    ?>
						    <li>
						    <div class="collapsible-header">
						    	<i class="material-icons circle left-align">chat_bubble</i>
						     	<a class="red-text darken-4">Review für 
								<?php
								echo $rev['name'] . "</a>   (Klick)"?>
							</div>
							<div class="collapsible-body white"><?php
								echo "<a href=\"review.php?id=".$rev['id']."\">Review für " . $rev['name'] . " bearbeiten</a>";
								?>
							</div>
							</li>
					<?php
			    	}
			    	echo "</ul>";
			    }
			} // Ende von "if is admin"
			?>
		</div>
	</div>
</body>
<script type="text/javascript">	
	$(".button-collapse").sideNav();
	$('#link-set-button').click(function(event){
		event.preventDefault();
	    var link = $('#link-set-link').val();
	    $.ajax
	    ({ 
	        url: 'index.php',
	        data: {
	        	"link-set": null,
	        	"link": link
	    	},
	        type: 'post',
	        success: function(result) {
	           	Materialize.toast('Link wurde erfolgreich geändert!', 3000, 'rounded green darken-2');
	        },
	        error: function(error) {
	           	Materialize.toast('Fehler! ' + error, 3000, 'rounded red darken-2');
	        }
    });
});
</script>
</html>
<?php
$conn->close();
?>