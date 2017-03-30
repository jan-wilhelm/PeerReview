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
  <div class="container-fluid">
  <div class="row">
    <?php include "../sidenav.php"; ?>

    <div class="right-col">

	<?php
	if (isset($_SESSION['user_level']) && $_SESSION['user_level'] == 1) {?>


		<div class="row tiles_count">
			<div class="tiles_col col-md-2 col-sm-4 col-xs-6">
				<span class="tiles_desc">Users</span>
				<span class="tiles_number">1.509</span>
			</div>
			<div class="tiles_col col-md-2 col-sm-4 col-xs-6">
				<span class="tiles_desc">Users</span>
				<span class="tiles_number">1.509</span>
			</div>
			<div class="tiles_col col-md-2 col-sm-4 col-xs-6">
				<span class="tiles_desc">Users</span>
				<span class="tiles_number">1.509</span>
			</div>
			<div class="tiles_col col-md-2 col-sm-4 col-xs-6">
				<span class="tiles_desc">Users</span>
				<span class="tiles_number">1.509</span>
			</div>
			<div class="tiles_col col-md-2 col-sm-4 col-xs-6">
				<span class="tiles_desc">Users</span>
				<span class="tiles_number">1.509</span>
			</div>
			<div class="tiles_col col-md-2 col-sm-4 col-xs-6">
				<span class="tiles_desc">Users</span>
				<span class="tiles_number">1.509</span>
			</div>
		</div>


	    <div class="row equal">
		  	<div class="col-md-4 col-sm-12">
		  		<div class="admin-cart">
					<canvas id="logins_chart" height="100"></canvas>
					<script type="text/javascript">
						const ctx = $('#logins_chart');
						const options = {
							legend: {
								display: false
							},
							responsive: true
						};
						const dataset = [<?php 
								$data = getLoginsOfLastMonth($conn);
								$counts = array();
								foreach ($data as $time => $logins) {
									$counts[] = $logins;
								}
								echo implode(",", $counts);
						        ?>];
						const times = [
						    	<?php
								$times = array();
								foreach ($data as $time => $logins) {
									$dt = new DateTime(((string) $time));
									$times[] = "'".$dt->format('d.m')."'";
								}
								echo implode(",", $times);
						        ?>
						    ];
						console.log((times));
						console.log(JSON.stringify(times));
						const data = {
						    datasets: [{
						        data: dataset
						    }],
						    labels: times
						};

						console.log(JSON.stringify(data));

						var loginsChart = new Chart(ctx, {
						    type: 'line',
						    data: data,
						    options: options
						});
					</script>
				</div>
			</div>
		  	<div class="col-md-8 col-sm-12">
		  		<div class="admin-cart">
		  		awdadaw
		  		</div>
		  	</div>
		</div>
	<?php } ?>
    <div class="row equal">
	  	<div class="col-md-6 col-sm-12">
	  		<div class="admin-cart">
		    <?php
				echo '<h1> Hallo, ' . $_SESSION["name"] . "!</h1>";
		    ?>
			<p>
			Hier kannst du einen Link zu deinem Code hinterlegen, ein Review verfassen oder dein eigenes lesen.
			</p>
			</div>
		</div>

	  	<div class="col-md-6 col-sm-12">
	  		<div class="admin-cart">
			<?php
		    if(isset($_POST['link-set']) && isset($_POST['link'])) {
		    	setCode($conn, $_SESSION['user_id'], $_POST['link'], $course);
		    	echo "<p class=\"green-text lighten-2\">Vielen Dank dass du den Link zu deinem Code eingegeben hast!</p>";
		    }
	    	?>
			<h3>Link zu deinem Code bearbeiten</h3>
			<span>
			Bitte gib den richtigen Link zu deinem Code ein, damit für dich ein Review verfasst werden kann!
			</span>
			<form action="" method="post" class="form-horizontal">
				<input id="link-set-link" class="form-control" name="link" type="text" placeholder="Link" value=<?php echo "\"". getCode($conn, $_SESSION['user_id'], $course)."\"";?>>
				
				<button type="button" class="btn btn-success" name="link-set" id="link-set-button">
					<i class="fa fa-paper-plane" aria-hidden="true"></i> Link absenden
				</button>
			</form>
			</div>
		</div>
	</div>

	<?php
	if (isset($_SESSION['user_level']) && $_SESSION['user_level'] == 1) {

  		echo '<div class="row equal"> <div class="col-md-8 col-sm-12"> <div class="admin-cart"> <h3>Neuen Nutzer erstellen</h3>';
		if (isset($_POST['user-create'])) {
			if (!isset($_POST['username']) || !isset($_POST['password'])) {
				echo "<span>Fülle bitte das gesamte Formular aus!</span>";
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
					echo "<span>Benutzer <i>$new_user_name</i> wurde erstellt!</span>";
				} else {
					echo "<span class=\"alert alert-danger\"><strong>Fehler</strong>: Ein Benutzer mit dem Namen $new_user_name existiert bereits.</span>";
				}
			}
		} else {
	?>
		<form action="" method="post" class="form-horizontal">
			<input name="username" type="text" placeholder="Username">
			<input name="password" type="text" placeholder="Password" value="<?php echo randomPassword(8); ?>">
			<button type="button" class="btn btn-primary" name="user-create">Benutzer erstellen</button>
		</form>
		<?php } ?>
		</div>
		</div>
		<div class="col-md-4 col-sm-12">
		<div class="admin-cart">
		<h3>Links verteilen</h3>
		<span>Hier kannst du die Links an die Benutzer verteilen, damit diese dann die Bewertungen verfassen könen. Bitte erst klicken, wenn alle Nutzer eingetragen sind!</span>
		<form method="post" class="form-horizontal">

			<input name="limit" value="3" type="number" id="set-codes-field"> 
			<button type="button" id="set-codes" class="btn btn-warning" name="codes-set" data-toggle="modal" data-target="#set-link-modal">
				Links verteilen
			</button>

			<!-- Modal -->
			<div class="modal fade" id="set-link-modal" tabindex="-1" role="dialog" aria-labelledby="set-link-modal-label">
			  <div class="modal-dialog" role="document">
			    <div class="modal-content">
			      <div class="modal-header">
			        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			        <h4 class="modal-title" id="set-link-modal-label">Links verteilen</h4>
			      </div>
			      <div class="modal-body">
				      <span>Achtung: Die Verteilung kann später nicht mehr rückgängig gemacht werden! Bitte überprüfe deshalb gut, ob alle Benutzer erstellt sind, denn nur die zu diesem Zeitpunkt existierenden Benutzer können für die Reviews ausgewählt werden.</span>
			      </div>
			      <div class="modal-footer">
			        <button type="button" class="btn btn-default" data-dismiss="modal">Abbrechen</button>
					<button type="button" id="set-codes-agree" class="btn btn-warning" name="codes-set" data-toggle="modal" data-target="#set-link-modal">
						Links verteilen
					</button>
			      </div>
			    </div>
			  </div>
			</div>
		</form>
		</div>
		</div>
		</div>
		
		<?php
		if(isset($_POST['codes-set']) && isset($_POST['limit'])) {
	    	if(!setTargets($conn, $course, intval($_POST['limit']))) {
	    		echo "<p class=\" red-text darken-4 \">Die Codes konnten nicht verteilt werden. <br />Vielleicht wurden nicht genügend (4) Benutzer eingetragen?</p>";
	    	} else {
	    		echo "<p class=\" green-text lighten-2 \">Die Codes wurden erfolgreich verteilt.<br />Jeder Schüler kann nun bewerten!</p>";
	    	}
	    }?>


		<div class="row">
			<div class="col-12">
				<div class="admin-cart">
					<h3>Edit users</h3>
					<div class="panel-body">
						<?php
						foreach (getUsersOfCourse($conn, $course) as $users) {
						?>
						    <div class="panel panel-primary">
	    						<div class="panel-heading panel-collapsed">
	    							<span class="pull-left clickable"><i class="fa fa-pencil" aria-hidden="true"></i></span>
							     	<h3 class="panel-title">Benutzer 
										<?php
										echo getName($conn, $users) . " bearbeiten </span>   (Klick)"?>
									</h3>
								</div>
								<div class="panel-body indigo lighten-5">
									<?php
									echo "<a class=\"blue-text lighten-3\" href=\"user.php?id=$users\">Bearbeiten!</a>";
									?>
								</div>
							</div>
						<?php } ?>
					</div>
				</div>
			</div>
		</div>

		<script type="text/javascript">
			$('#set-codes').click(function(event) {
				event.preventDefault();
				$('#modal1').modal().modal("open");
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
			           	toastr.success('Reviews wurden erfolgreich verteilt!', 'Geschafft!');
			        },
			        error: function(error) {
			           	toastr.error(error, 'Fehler!');
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
    		if($_SESSION['user_level'] !== 1) {			
				$review = getReviews($conn, $course, $_SESSION['user_id']);
				echo "<div class=\"row equal\"><div class=\"col-md-8\"><div class=\"admin-cart\">";
				echo "<h3>Deine Reviews</h3>";
				if(count($review) == 0) {
					echo "<span class=\"red-text darken-4\">Es wurde für dich noch keine Review ausgefüllt!</span>";
				} else {?>
					<div class="panel panel-primary">

					<?php
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
						    <div class="panel-heading panel-collapsed">
						    	
								<span class="pull-left clickable"><i class="fa fa-commenting" aria-hidden="true"></i></span>
						     	<h3 class="panel-title">Review von 
								<?php
								echo getName($conn, $rv['code_reviewer']) . "</h3>"?>
							</div>
							<div class="panel-body indigo lighten-5">
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
										echo '<div class="points"><span>'.$rev[$itemcount]['reviews'][$idx]['points'].' / '.$cat['max_points'].'</span></div></div>';
										$idx = $idx + 1;
									}
									echo '<div class="cat"><span class="comment">'.$rev[$itemcount]['comment'].'</span></div>';
									$itemcount = $itemcount + 1;
								?>
							</div>
								<?php
								}
								?>
							</div>
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
					} echo "</div>";
				}
			echo "</div></div>";
		?>
			<div class="col-md-4">
				<div class="admin-cart">       <!--Anfang admin cart -->
				<h3>Bewertungen verfassen</h3>
				<?php
				    $tar = getReviewTargets($conn, $_SESSION['user_id'], $course);
				    # noch kein target
				    if($_SESSION['user_level'] === 1) {
						echo "<span class=\"red-text darken-4\">Als Administrator kannst du keine Reviews schreiben.</span>";
					}elseif (count($tar) == 0) {
						echo "<span class=\"red-text darken-4\">Es wurde für dich noch kein Benutzer zum Review ausgewählt!</span>";
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
				</div>			<!-- Ende admin cart -->
			</div>			<!-- Ende col-md-4 	-->
		</div>			<!-- Ende row -->
		</div>
		</div>
	</div>
</body>
<script type="text/javascript">	
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
	           	toastr.success('Link wurde erfolgreich geändert!', 'Geschafft!');
	        },
	        error: function(error) {
	           	toastr.error(error, 'Fehler');
	        }
    });
});
</script>
<script type="text/javascript" src="./js/panel.js"></script>
</html>
<?php
$conn->close();
?>