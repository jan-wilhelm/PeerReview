<?php
include '../check_auth.php';
include '../config.php';
include "../review.php";
include "../header.php";
$conn = new mysqli($cfg['db_host'], $cfg['db_user'], $cfg['db_password'], $cfg['db_name']);

if ($conn->connect_error) {
	die("Database connection failed: " . $conn->connect_error);
}

if(!isset($_GET['course']) && !isset($_POST['course'])) {
	die("No course get set");
}
if(isset($_GET['course'])) {
	$course = $_GET['course'];
} else {
	$course = $_POST['course'];
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
			foreach (getUsersOfCourse($conn, $course) as $users) {
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
		<?php
		if(isset($_POST['codes-set']) && isset($_POST['limit'])) {
	    	if(!setTargets($conn, $course, intval($_POST['limit']))) {
	    		echo "<p class=\"element red-text darken-4 z-depth-4\">Die Codes konnten nicht verteilt werden. <br />Vielleicht wurden nicht genügend (4) Benutzer eingetragen?</p>";
	    	} else {
	    		echo "<p class=\"element green-text lighten-2 z-depth-4\">Die Codes wurden erfolgreich verteilt.<br />Jeder Schüler kann nun bewerten!</p>";
	    	}
	    }
		?>
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
			        	"limit": limit,
			        	"course": <?php echo $course;?>
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
    	    	setCode($conn, $_SESSION['user_id'], $_POST['link'], $course);
    	    	echo "<p class=\"element green-text lighten-2 z-depth-4\">Vielen Dank dass du den Link zu deinem Code eingegeben hast!</p>";
    	    }
    	    ?>
			<form action="" method="post" class="element z-depth-4">
				<h4 class="header">Link zu deinem Code bearbeiten</h4>
				<span>
				Bitte gib den richtigen Link zu deinem Code ein, damit für dich ein Review verfasst werden kann!
				</span>
				<input id="link-set-link" name="link" type="text" placeholder="Link" value=<?php echo "\"". getCode($conn, $_SESSION['user_id'], $course)."\"";?>>
				<br/>
				<a class="waves-effect waves-light btn red darken-2"><i class="material-icons right">send</i>
						<input type="submit" name="link-set" value="Link absenden" id="link-set-button"></a>
			</form>
			
    		<?php
    		if($_SESSION['user_level'] !== 1) {			
				$review = getReviews($conn, $course, $_SESSION['user_id']);
				echo "<div class=\"element z-depth-4\">";
				echo "<h4 class=\"header\">Deine Reviews</h4>";
				if(count($review) == 0) {
					echo "<p class=\"red-text darken-4\">Es wurde für dich noch keine Review ausgefüllt!</hp>";
				} else {?> <ul class="collapsible" data-collapsible="expandable"> <?php
					$json = json_decode(getReviewScheme($conn, $course), JSON_UNESCAPED_UNICODE);
					$max_points = 0;
					foreach ($json as $cats) {
						foreach ($cats['categories'] as $cat) {
							$max_points = $max_points + $cat['max_points'];
						}
					}
					foreach ($review as $rv) {
					if($rv['review']!="{}" && strlen($rv['review']) > 2) {
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
								//$points = getPoints($conn,$rv['id'],$rv['code_reviewer'], $course);
								$points = 0;
								$rev = json_decode($rv['review'], JSON_UNESCAPED_UNICODE);
								for ($i=0; $i < sizeof($rev); $i++) { 
									foreach($rev[$i]['reviews'] as $p) { 
										$points = $points + $p['points'];
									}
								}
								echo $points." von ".$max_points." Punkten (".((int)(100 * $points / $max_points))."%)</h4>";

								$itemcount = 0;
								foreach ($json as $item) {
									echo '<div class="sect">';
									echo '<p>'.$item["name"].'</p>';
									$idx = 0;
									foreach($item["categories"] as $cat) {
										echo '<div class="cat"><span class="desc">'.$cat['description']."</span>";
										echo '<div class="points"><span>'.$rev[$itemcount]['reviews'][$idx]['points'].'/ '.$cat['max_points'].'</span></div>';
										$idx = $idx + 1;
									}
									echo '<div class="cat"><span class="comment">'.$rev[$itemcount]['comment'].'</span></div>';
									$itemcount = $itemcount + 1;
									echo '</div></div>';
								?>
							</div>
								<?php
								}
								?>
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
			    $tar = getReviewTargets($conn, $_SESSION['user_id'], $course);
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
							echo "<a href=\"review.php?id=".$rev['id']."&course=".$course."\">Review für " . $rev['name'] . " bearbeiten</a>";
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
	        url: "index.php",
	        data: {
	        	"link-set": null,
	        	"link": link,
	        	"course": <?php echo $course;?>
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