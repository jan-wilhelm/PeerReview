<?php
include '../config.php';

$filePath = $IS_LOCAL ? "../" : "../../info/";
include $filePath. 'review.php';

include $filePath. "header.php";

$conn = new mysqli($cfg['db_host'], $cfg['db_user'], $cfg['db_password'], $cfg['db_name']);

if ($conn->connect_error) {
	die("Database connection failed: " . $conn->connect_error);
}

$admin = (isset($_SESSION['info']['user_level']) && $_SESSION['info']['user_level'] === 1);

include $filePath. 'check_auth.php';
include $filePath. 'profile_picture.php';

?>

<body>
	<script type="text/javascript">
		$(function() {

		    //create instance
		    $('.pie-chart').easyPieChart({
		        animate: 2000,
		        lineWidth: 16,
		        lineCap: "butt",
		        barColor: "#2A3F54",
		        scaleColor: "#aaa",
		        trackColor: "#E3E3E3",
		        scaleLength: 2,
		        onStep: function(from, to, value) {
		        	value = Math.round(value);
		        	$(this.el).parents().find('.pie-percentage').html(value);
		        }
		    });

		    $('.admin-cart').each(function (index, element) {
		    	const closeItem = $('<i class="close-item fa fa-times-circle" aria-hidden="true">');
		    	closeItem.appendTo(element);
		    });
		    $('.close-item').click(function() {
		    	$(this).parent('.admin-cart').parent("div[class*='col-']").remove();
		    });

		});
	</script>
	<script type="text/javascript" src="./js/panel.js"></script>

	<header class="cd-main-header">
		<a class="cd-logo"><img src="img/cd-logo.svg" alt="Logo"></a>

		<a href="#0" class="cd-nav-trigger"><span></span></a>

		<nav class="cd-nav">
			<ul class="cd-top-nav">
				<li><a href="scripts">Scripts</a></li>
				<li class="has-children account">
					<a href="#0"><?php
				  		$path = getPicName($_SESSION["info"]["user_id"], $IS_LOCAL, $ROOT_SITE);
				  		echo '<img src="'.$path.'" alt="Avatar">'; ?>
						Account
					</a>
					<ul>
						<li><a href="settings.php">Einstellungen</a></li>
						<li><a href="logout.php">Logout</a></li>
					</ul>
				</li>
			</ul>
		</nav>
	</header> <!-- .cd-main-header -->

  	<div class="container-fluid">
	  <div class="row">
	    <div class="sidebar cd-main-content">
			<nav class="cd-side-nav">
				<ul class="nav nav-sidebar">
					<li class="cd-label">Main</li>
					<li class="has-children overview">
						<a href="index.php"><i class="fa fa-users" aria-hidden="true"></i>Kurse</a>
						<ul>
							<?php
								$courses = getCoursesOfUser($conn, $_SESSION['info']['user_id']);
								foreach ($courses as $course) {
									echo '<li><a href="?course='.$course.'">'.getCourseName($conn, $course).'</a></li>';
								}
							?>
						</ul>
					</li>
					<li>
						<a href="signup.php"><i class="fa fa-user-plus" aria-hidden="true"></i>In Kurs eintragen</a>
					</li>
				</ul>

				<?php
				if($admin && isset($_GET["course"])) { ?>
				<ul class="nav nav-sidebar">
					<li class="cd-label">Admin</li>
					<li>
						<a href="coursesettings.php?course=<?php echo $_GET["course"];?>"><i class="fa fa-cogs" aria-hidden="true"></i>Kurseinstellungen</a>
					</li>
				</ul>
				<?php
				}
				?>

			</nav>
		</div>

    <div class="right-col">

	<?php

	// render page if no get params are set
	if( !isset($_GET['course']) && !isset($_POST['course'])) {
		?>
		<div class="row equal">
			<div class="col-md-12">
				<div class="admin-cart">
					<h3>Deine Kurse</h3>
					<?php
					echo '<ul class="list-group">';
					foreach ($courses as $course) {
						echo '<li class="list-group-item">Kurs ';
						echo '<a href="?course='.$course.'">'.getCourseName($conn, $course).'</a>';
						echo '</li>';
					}
					echo '</ul>';
					?>
				</div>
			</div>
		</div>
		<div class="centered">
			<button class="btn btn-primary"><a href="signup.php" class="white-text">In neuen Kurs einschreiben</a></button>
		</div>
		<?php
		if($admin) {
			?>
			<div class="row equal">
				<div class="col-md-12">
					<div class="admin-cart">
						<h3>Neuen Kurs erstellen</h3>
						<div id="create_course_group" class="centered">
							<span>Hier kannst du einen neuen Kurs erstellen. Bitte wählen den Namen des Kurses sorgfältig!<br>Die Schüler, die diesem Kurs beitreten sollen, benötigen dafür einen <code>SignUp-Key</code>. Diesen findest du in der Seite deines Kurses unter <mark>Kurseinstellungen</mark>. Gib ihnen diesen 6-stelligen Code, damit diese ihn unter <a href="signup.php">diesem Link</a> benutzen können.</span>
							<?php
							if(isset($_POST['create-course'])) {
								$error = 0;
								$newId = -1;
								if(!isset($_POST['course-name'])) {
									$error = 1;
								} else {
									$courseName = htmlspecialchars(trim($_POST['course-name']));
									if (empty($courseName)) {
										$error = 1;
									} else {
										$key = "";
										do {
											$key = randomPassword(6);
										} while (getCourseByKey($conn, $key) != 0);

										createCourse($conn, $key, $courseName);
										$newId = getCourseByKey($conn, $key);
										addUserToCourse($conn, $_SESSION['info']['user_id'], $newId);
									}
								}
								if($error != 0) {
									echo '<span class="alert alert-danger">Bitte gib einen gültigen Namen für den Kurs ein!</span>';
								} else {
									echo '<span class="alert alert-success">Du hast erfolgreich den Kurs <i>' . $courseName . '</i> erstellt, und wurdest ihm hinzugefügt.<br>Du möchtest ihn direkt bearbeiten? Klicke <a href="' . $ROOT_SITE . "?course=$newId" .'">hier</a>!</span>';
								}
							}
							?>
					    	<form class="form-horizontal" method="post" action="">
							    <div class="input-group">
								    	<input type="text" class="form-control" placeholder="Name..." name="course-name">
								    	<span class="input-group-btn">
								    		<button class="btn btn-success" type="submit" name="create-course">Erstellen!</button>
								    	</span>
							    </div>
					    	</form>
						</div>
					</div>
				</div>
			</div>
			<?php
		}
		?>
        </div>
        </div>
        </div>
        </body>
        <?php
		exit();
	}


	if(isset($_GET['course'])) {
		$course = $_GET['course'];
	} else {
		$course = $_POST['course'];
	}

	if ($admin) {
		?>
		<div class="row tiles_count">
			<div class="tiles_col col-md-2 col-sm-4 col-xs-6">
				<span class="tiles_desc">Benutzer</span>
				<span class="tiles_number">
				<?php
					echo getNumberOfUsersTotal($conn);
				?>
				</span>
			</div>
			<div class="tiles_col col-md-2 col-sm-4 col-xs-6">
				<span class="tiles_desc">Mitglieder dieses Kurses</span>
				<span class="tiles_number">
				<?php
					echo getNumberOfUsersInCourse($conn, $course);
				?>
				</span>
			</div>
			<div class="tiles_col col-md-2 col-sm-4 col-xs-6">
				<span class="tiles_desc">Logins</span>
				<span class="tiles_number">
					<?php
						echo getTotalLogins($conn);
					?>
				</span>
			</div>
			<div class="tiles_col col-md-2 col-sm-4 col-xs-6">
				<span class="tiles_desc">Logins der letzten 24 Stunden</span>
				<span class="tiles_number">
					<?php
						echo getTotalLoginsOfTimeInterval($conn, "1 DAY");
					?>
				</span>
			</div>
			<div class="tiles_col col-md-2 col-sm-4 col-xs-6">
				<span class="tiles_desc">Kurse</span>
				<span class="tiles_number">
					<?php
						echo getNumberOfCoursesTotal($conn);
					?>
				</span>
			</div>
			<div class="tiles_col col-md-2 col-sm-4 col-xs-6">
				<span class="tiles_desc">Geschriebene Reviews</span>
				<span class="tiles_number">
					<?php
						echo getTotalNumberOfWrittenReviews($conn);
					?>
				</span>
			</div>
		</div>

	<?php
	} else {
		$reviewsSinceLastLoginForUser = getReviewsSinceLastLoginForUser($conn, $_SESSION['info']['user_id'], $course); ?>
		<div class="row tiles_count">
			<div class="tiles_col col-md-2 col-sm-4 col-xs-6">
				<span class="tiles_desc">Bewertungen seit letztem Login</span>
				<span class="tiles_number"><?php echo $reviewsSinceLastLoginForUser;?></span>
			</div>
		</div>
	<?php
	}

	// Render page if no specific review is selected
	if(!isset($_GET['review']) && !isset($_POST['review'])) {

		?>
		<div class="row">
			<div class="col-12">

				<ol class="breadcrumb">
				  <li class="breadcrumb-item"><a href="<?php echo $ROOT_SITE; ?>">Startseite</a></li>
				  <li class="breadcrumb-item">Kurs <a href="<?php echo "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"; ?>">
					<?php
					echo getCourseName($conn, $course);
					?>
					</a>
					</li>
				</ol>
			</div>
		</div>

	    <div class="row equal">
		  	<div class="col-md-4 col-xs-12">
		  		<div class="admin-cart">
		  			<h1>Hallo, <?php echo $_SESSION['info']['name'];?>!</h1>
		  			<span>Hier kannst du dir Statistiken anschauen, neue Reviews erstellen und dir bereits existierende Reviews anschauen. Klicke dafür einfach auf ein Review im Abschnitt <code>Reviews in diesem Kurs</code></span>
		  		</div>
		  	</div>
		  	<div class="col-md-4 col-xs-12">
		  		<div class="admin-cart">
					<h3>Logins pro Tag</h3>
					<canvas id="logins_chart" height="100"></canvas>
				</div>
			</div>
			<div class="col-md-4 col-xs-12">
				<div class="admin-cart">
					<h3>Geschriebene Reviews in diesem Kurs</h3>
					<?php $finishedReviewsOfCourse = getFinishedReviewsOfCourse($conn, $course); ?>
					<div class="pie-container">
						<span class="pie-percentage">0</span>

						<div class="pie-chart" data-percent="<?php 
							echo $finishedReviewsOfCourse;
						?>">
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="row equal">
			<div class="col-md-6">
				<div class="admin-cart">
					<h3>Reviews in diesem Kurs</h3>
					<span>Klicke einfach auf ein Review, um die darin enthaltenen Abgaben durchzulesen oder eine neue zu verfassen!</span>
					<ul class="list-group">
						<?php
						$reviews = getAllReviewIDsOfCourse($conn, $course);
						foreach ($reviews as $id) {
							$link = "index.php?course=".$course."&review=".$id;
							?>
							<li class="list-group-item">
							<a href="<?php echo $link;?>">
								<?php
								echo "Review ".getReviewNameForID($conn, $id);
								?>
							</a>
							</li>
						<?php
						}
						?>
					</ul>
				</div>
			</div>
			<?php
			if($admin) {?>
			<div class="col-md-6 col-sm-12">
				<div class="admin-cart">
					<h3>Benutzer bearbeiten</h3>
					<span>
						Hier kannst du alle Benutzer dieses Kurses bearbeiten, und<mark> ihre geschriebenen Reviews lesen</mark>.<br>Klicke einfach auf einen bestimmten Nutzer und wähle das passende Review aus!
					</span>
					<div class="row" id="edit_users_row">
						<?php
						foreach (getUsersOfCourseApartFromAdmin($conn, $course) as $users) {					?>
							<div class="col-md-4 col-xs-12">
    							<span class="pull-left clickable">
    								<i class="fa fa-pencil" aria-hidden="true"></i>
	    							<?php
	    							echo "<a class=\"blue-text\" href=\"user.php?id=$users&course=$course\">".getName($conn, $users)."</a>"
	    							?>
    							</span>
							</div>
							<?php
						}
						?>
					</div>
				</div>
			</div>
		</div>
			<div class="row equal">
				<div class="col-xs-12">
					<div class="admin-cart">
						<h3>Neues Review erstellen</h3>
						<span>Hier kannst du ein neues Review erstellen! Füge einzelne Abschnitte des Reviews über die Schaltfläche <code>"Neuer Abschnitt"</code> hinzu. Innerhalb dieses Abschnittes kannst du über den Knopf <code>"Neue Kategorie"</code> eine neue Unterkategorie dieses Abschnittes hinzufügen.<br>Um die <mark>Platzhalter und einzelnen Werte zu ändern</mark>, klicke sie einfach an, gebe einen passenden Wert ein und klicke woanders hin, um den Wert zu übernehmen.</span>
						<noscript>
							<h1 class="red-text">Achtung! Du hast JavaScript deaktiviert! Daher wirst du leider kein neues Review erstellen können!<a href="http://www.enable-javascript.com/de/"> Falls du wissen möchtest, wie du JavaScript einschalten kannst, klicke hier!</a></h1>
						</noscript>
						<ul id="create_review" class="create_review">

							<form class="form-horizontal">
								<input class="form-control" id="create_review_name" type="text" placeholder="Name des Reviews">
							</form>
							<span class="badge btn-success create_section text-center">
								<i class="fa fa-plus" aria-hidden="true"></i> Neuer Abschnitt
							</span>

						</ul>
						<button type="button" class="btn btn-primary" id="create_review_button">Review erstellen</button>
					</div>
				</div>
			</div>	
			<?php
			} else {
				echo "</div>";
			}
			?>
		</div>
		</div>
		</div>
		</body>
		<?php
		if($admin) { ?>
			<script type="text/javascript" src="./js/review_creator.js"></script>
			<?php
		}?>
		<script type="text/javascript">
			const ctx = $('#logins_chart');
			const options = {
				legend: {
					display: false
				},
				responsive: true,
				animation: {
					animateScale: true
				},                                                                             
			    scales: {
			      yAxes: [{id: 'y-axis-1', type: 'linear', position: 'left', ticks: {min: 0}}]
			    }
			};
			const dataset = [<?php 
					$data = getLoginsOfLastTwoWeeks($conn);
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
			const data = {
			    datasets: [{
			        data: dataset,
			        backgroundColor: "rgba(75,192,192,0.2)",
		            borderColor: "rgba(75,192,192,1)"
			    }],
			    labels: times
			};

			$(document).ready(function() {
				var loginsChart = new Chart(ctx, {
				    type: 'line',
				    data: data,
				    options: options
				});
			});

			$(document).on('click', '#edit_users_row a', function(e){
				e.preventDefault();
			    var $this = $(this);
				const link = $this.attr('href');

				if(!$('#edit-user-modal').length) {
					$('.container-fluid').append('<div class="modal fade" id="edit-user-modal" tabindex="-1" role="dialog" aria-labelledby="edit-user-modal-label"> <div class="modal-dialog modal-lg" role="document"><div class="modal-content"><div class="modal-header"> <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button><h4 class="modal-title" id="set-link-modal-label">Benutzer bearbeiten</h4></div><div class="modal-body"></div><div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Abbrechen</button></div></div></div></div>');
				}
				$('#edit-user-modal .modal-body').load(link + " .element", function( response, status, xhr ) {
					$('#edit-user-modal').find(".panel-body").css("display", "none");
				  	$('#edit-user-modal').modal().modal("open");
				});
				
			});
		</script>
		<?php
		echo "</html>";
		exit();
	}
	if(isset($_GET['review'])) {
		$reviewId = intval($_GET['review']);
	} elseif (isset($_POST['review'])) {
		$reviewId = intval($_POST['review']);
	}
					?>
	<div class="row">
		<div class="col-12">

			<ol class="breadcrumb">
				  <li class="breadcrumb-item"><a href="<?php echo $ROOT_SITE; ?>">Startseite</a></li>
				<li class="breadcrumb-item">Kurs <a href="<?php echo $ROOT_SITE . "?course=$course"; ?>">
					<?php
					echo getCourseName($conn, $course);
					?>
					</a>
				</li>
				<li class="breadcrumb-item">Review <a href="<?php echo "$_SERVER[REQUEST_URI]"; ?>">
					<?php
					echo getReviewNameForID($conn, $reviewId);
					?>
					</a>
					</h2>
				</li>
			</ol>
		</div>
	</div>
    <div class="row equal">
	  	<div class="col-md-6 col-sm-12">
	  		<div class="admin-cart">
		    <?php
				echo '<h1> Hallo, ' . $_SESSION['info']["name"] . "!</h1>";
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

		    	$json = json_decode($_POST['link'], JSON_UNESCAPED_UNICODE);
		    	$payload = htmlspecialchars(trim($json['payload']));
		    	$subType = intval($json['type']);

				setSubmissionType($conn, $_SESSION['info']['user_id'], $subType, $course, $reviewId);

		    	switch ($subType) {
		    		case 0:
		    			setCode($conn, $_SESSION['info']['user_id'], $payload, $course, $reviewId);
		    			echo "<p class=\"green-text lighten-2\">Vielen Dank dass du den Link zu deinem Code eingegeben hast!</p>";
		    			break;
		    		case 1:
		    			setScript($conn, $_SESSION['info']['user_id'], intval($payload), $course, $reviewId);
		    			echo "<p class=\"green-text lighten-2\">Vielen Dank dass du ein Programm ausgewählt hast!</p>";
		    			break;
		    		
		    		default:
		    			break;
		    	}
		    }
	    	?>
			<h3>Link zu deinem Code bearbeiten</h3>
			<span>
			Hier kannst du nur den Link für das <mark>aktuelle Review</mark> ändern, um Abgaben im Nachhinein nicht zu verfälschen. <br>
			Bitte gib den richtigen Link zu deinem Code ein, damit für dich ein Review verfasst werden kann!
			</span>

			<div class="set-link-wrapper text-center">
				<div class="btn-group" data-toggle="buttons" id="set-link-radio">
				  <label class="btn btn-primary active">
				    <input type="radio" id="option1" value="1" autocomplete="off" checked>Link
				  </label>
				  <label class="btn btn-primary">
				    <input type="radio" id="option2" value="2" autocomplete="off">Programm
				  </label>
				</div>

				<input id="link-set-link" class="form-control set-link-type" name="link" type="text" placeholder="Link">

				<div id="set-link-script" class="form-group row text-left set-link-type" style="display: none;">
					<a class="btn btn-primary pull-right" href="<?php echo $ROOT_SITE . "script";?>">Neues Script</a>
					<label for="set-link-script-select" class="col-xs-2 col-form-label">Programm</label>
					<div class="col-xs-10">
						<select class="form-control" id="set-link-script-select">
							<?php
							$scripts = getScriptsForUser($conn, $_SESSION["info"]["user_id"]);
							foreach ($scripts as $script) {
								echo '<option data-id="' . $script["script_id"] . '">' . $script["name"] . '</option>';
							}
							?>
						</select>
					</div>
				</div>

				<button class="btn btn-success" name="link-set" id="link-set-button">
					<i class="fa fa-paper-plane" aria-hidden="true"></i> Link absenden
				</button>
			</div>
			</div>
		</div>
	</div>

	<?php
	if ($admin) {
	?>
		<div class="row equal">
			<div class="col-md-4 col-sm-12">
				<div class="admin-cart">
					<h3>Links verteilen</h3>
					<span>Hier kannst du die Links an die Benutzer verteilen, damit diese dann die Bewertungen verfassen könen. Bitte erst klicken, wenn alle Nutzer eingetragen sind!<br>Das folgende <code>Limit</code> gibt an, für wieviele andere Nutzer jeder Nutzer ein Review schreiben soll.</span>
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
	    	if(!setTargets($conn, $course, intval($_POST['limit']), $reviewId)) {
	    		echo "<p class=\" red-text darken-4 \">Die Codes konnten nicht verteilt werden. <br />Vielleicht wurden nicht genügend (4) Benutzer eingetragen?</p>";
	    		http_response_code(500);
	    	} else {
	    		echo "<p class=\" green-text lighten-2 \">Die Codes wurden erfolgreich verteilt.<br />Jeder Schüler kann nun bewerten!</p>";
	    	}
	    }?>

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
			        	"course": <?php echo $course;?>,
			        	"review": <?php echo $reviewId;?>
			    	},
			        type: 'post',
			        success: function(result) {
			           	toastr.success('Reviews wurden erfolgreich verteilt!', 'Geschafft!');
			        },
			        error: function(error) {
			           	toastr.error(error, 'Fehler!');
	           			toastr.error("Vielleicht wurden nicht genügend Schüler (" + limit + ") in den Kurs eingetragen");
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
	if(!$admin) {?>
		<div class="row equal">
			<div class="col-md-8">
				<div class="admin-cart">
					<h3>Deine Reviews</h3>
					<?php
					$review = getReviewsFor($conn, $course, $_SESSION['info']['user_id'], $reviewId);
					if(sizeof($review) < 1) {?>
						<span class="alert alert-warning">Es wurde für dich noch keine Review ausgefüllt!</span>
					<?php
					} else {
						if($reviewsSinceLastLoginForUser > 0) {
							echo '<span class="admin-badge badge green accent-4">'.$reviewsSinceLastLoginForUser.'</span>';
						}
						$json = json_decode(getReviewSchemeForID($conn, $course, $reviewId), JSON_UNESCAPED_UNICODE);
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
																<?php echo $rev[$itemcount]['reviews'][$idx]['points'].' / '.$cat['max_points'] . " Pkt";?>
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
					?>
				</div>
			</div>
			<div class="col-md-4">
				<div class="admin-cart">       <!--Anfang admin cart -->
					<h3>Review verfassen</h3>

					<?php
				    $tar = getReviewTargets($conn, $_SESSION['info']['user_id'], $course, $reviewId);
				    # noch kein target
				    if (count($tar) == 0) {
						echo "<span class=\"red-text darken-4\">Es wurde für dich noch kein Benutzer zum Review ausgewählt!</span>";
				    } else {?>
				    	<ul class="edit-review-list list-group">
				    	<?php
						foreach ($tar as $rev) {
						    ?>
						    <li class="list-group-item">
							<span class="pull-left">
								<i class="fa fa-pencil" aria-hidden="true"></i>
							</span>
						    <?php
							echo '<a href="review.php?id='.$rev['id'].'&course='.$course.'&review='.$reviewId.'"> Review für ' . $rev['name'] . ' bearbeiten</a>';
							?>
							</li>
							<?php
				    	}?>
				    	</ul>
				    	<?php
			    	}?>
				</div>
			</div>
		</div>
				<?php
		} // Ende von "if is admin"
		?>
		</div>
	</div>
	</div>
</body>
<script type="text/javascript">
	$(document).ready(function() {
		var show = $('#link-set-link');
		$('#link-set-button').click(function(event){
			event.preventDefault();
			var json = {};
		    if(show.attr("id") === "link-set-link") {
		    	json.type = 0;
		    	json.payload = show.val();
		    } else if(show.attr("id") === "set-link-script") {
		    	json.type = 1;
		    	json.payload = show.find('select').find(':selected').data('id');
		    }
		    $.ajax
		    ({ 
		        url: "index.php",
		        data: {
		        	"link-set": null,
		        	"link": JSON.stringify(json),
		        	"course": <?php echo $course;?>,
		        	"review": <?php echo $reviewId;?>
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
		$('#set-link-radio label').click(function() {
			const th = $(this);
			setTimeout(function() {
				var checked = $('#set-link-radio .active').find('input').val() - 1;
				th.blur();
				if (checked == 0) {
					show = $('#link-set-link');
				} else if(checked == 1) {
					show = $('#set-link-script');
				}

				show.css("display", "block");
				$('.set-link-type').not(show).css("display", "none");

			}, 5);
		});
	});
</script>

</html>
<?php
$conn->close();
?>