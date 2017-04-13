<?php

include '../config.php';
include "../review.php";
if(!isset($_GET['id']) || !isset($_GET['course'])) {
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
include '../check_auth.php';

$course = intval($_GET['course']);

$target = array(
	"id" => intval($_GET['id']),
	"name" => getName($conn,$_GET['id'])
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
  		<h2 class="header">Benutzer <span class="red-text"><?php echo $target["name"]?></span> bearbeiten</h2>
  		<?php
  		foreach (getAllReviewIDsOfUser($conn, $target['id'], $course) as $reviewId) {?>
		<div class="panel panel-primary">
		    <!-- heading -->
		    <div class="panel-heading panel-collapsed">
		     	<h3 class="panel-title">Review <span class="grey-text text-lighten-1"><?php echo getReviewNameForID($conn, $reviewId)?>
				</span></h3>
		    <!-- end heading -->
			</div>

	    <!-- body -->
		<div class="panel-body">
  		<div class="edit-el">
  			<h4>Link zum Code</h4>
		<?php
			$code = getCode($conn, $target['id'], $course, $reviewId);
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
			$review = getReviewsFor($conn, $course, $target["id"], $reviewId);
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
			    $tar = getReviewsBy($conn, $course, $target['id'], $reviewId);
			    # noch kein target
			    if (count($tar) == 0) {
					echo "<p class=\"red-text darken-4\">Es wurde für ".$target["name"]." noch kein Benutzer zum Review ausgewählt!</p>";
			    } else {?>
					<?php
					$review = getReviewsFor($conn, $course, $_SESSION['user_id'], $reviewId);
					if(count($review) == 0) {
						echo "<p class=\"red-text darken-4\">Dieses Review wurde noch nicht ausgefüllt!</p>";
					} else {?>
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
								<div class="panel panel-primary">
								    <!-- heading -->
								    <div class="panel-heading panel-collapsed">
										<span class="pull-left clickable">
											<i class="fa fa-commenting" aria-hidden="true"></i>
										</span>
								     	<h3 class="panel-title">Review von 
											<?php
											echo getName($conn, $rv['code_reviewer'])?>	
										</h3>
								    <!-- end heading -->
									</div>

								    <!-- body -->
									<div class="panel-body indigo lighten-5">
										<h4 class="green-text lighten-2">
											<?php

											$points = 0;
											$rev = json_decode($rv['review'], JSON_UNESCAPED_UNICODE);
											for ($i=0; $i < sizeof($rev); $i++) { 
												foreach($rev[$i]['reviews'] as $p) { 
													$points = $points + $p['points'];
												}
											}
											echo $points." von ".$max_points." Punkten (".((int)(100 * $points / $max_points))."%)";?>	
										</h4>
										<?php

										$itemcount = 0;
										foreach ($json as $item) {
											?>
											<div class="sect">
												<?php
												echo '<p>'.$item["name"].'</p>';
												$idx = 0;
												foreach($item["categories"] as $cat) {
													?>
													<div class="cat">
														<span class="desc">
															<?php echo $cat['description']; ?>
														</span>
														<div class="points">
															<span>
																<?php echo $rev[$itemcount]['reviews'][$idx]['points'].' / '.$cat['max_points'];?>
															</span>
														</div>
													</div>
													<?php 
													$idx = $idx + 1;
												}?>
												<div class="cat">
													<span class="pull-left">Kommentar: </span>
													<span class="comment">
														<?php echo $rev[$itemcount]['comment'];?>
													</span>
												</div><?php
												$itemcount = $itemcount + 1;
												?>
											</div>
											<?php
										}
										?>
								    <!-- end panel body -->
									</div>
								</div>
								<?php
							} else {?>
								<div class="panel panel-warning">
								    <div class="panel-heading panel-collapsed">
										<span class="pull-left clickable"><i class="fa fa-exclamation-circle" aria-hidden="true"></i></span>
								     	<h3 class="panel-title">Du hast noch keine Review von 
										<?php
										echo getName($conn, $rv['code_reviewer'])?>
										</h3>
									</div>
								</div>
								<?php
							}
						}
					}
			    }
			?>
		</div>
		</div>
		</div>
		<?php
		} ?>
	</div>
</body>
<script type="text/javascript" src="./js/panel.js"></script>
</html>
<?php
$conn->close();
?>