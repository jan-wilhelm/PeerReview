<?php
include '../config.php';

$filePath = $IS_LOCAL ? "../" : "../../info/";
include $filePath. 'review.php';

include $filePath. "header.php";

$conn = new mysqli($cfg['db_host'], $cfg['db_user'], $cfg['db_password'], $cfg['db_name']);

$admin = (isset($_SESSION['info']['user_level']) && $_SESSION['info']['user_level'] === 1);

if ($conn->connect_error) {
	die("Database connection failed: " . $conn->connect_error);
}

include $filePath. 'check_auth.php';
include $filePath. 'profile_picture.php';

?>

<body>
	<script type="text/javascript">
		$(function() {
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
	</script>

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
	</header>

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
				</nav>
			</div>
    		<div class="right-col">
			    <div class="row equal">
				  	<div class="col-md-6 col-sm-12">
				  		<div class="admin-cart">
				  			<h3>Zeitleiste</h3>
				  			<div class="timeline">
				  				<?php
				  				$reviews = $admin ? getLastReviewsForAdmin($conn, $_SESSION["info"]["user_id"], 3) : getLastReviewsForUser($conn, $_SESSION["info"]["user_id"], 3);

				  				foreach ($reviews as $reviewObject) {
									$scheme = json_decode(getReviewSchemeForID($conn, $reviewObject["course"], $reviewObject["review_id"]), JSON_UNESCAPED_UNICODE);
									if($reviewObject['review']== "{}" || strlen($reviewObject['review']) <= 2) {
										continue;
									}?>

					  				<div class="timeline-item">
										<?php

										// output the headline
										echo "<h4>Review von <a href=\"{$ROOT_SITE}user.php?id={$reviewObject["code_reviewer"]}&course={$reviewObject["course"]}\">" . getName($conn, $reviewObject["code_reviewer"]) .  "</a>";
										if($admin) {
											echo " für <a href=\"{$ROOT_SITE}user.php?id={$reviewObject["id"]}&course={$reviewObject["course"]}\">" . getName($conn, $reviewObject["id"]) .  "</a>";
										}
										echo "</h4>";
										$rev = json_decode($reviewObject['review'], JSON_UNESCAPED_UNICODE);

										$dt = new DateTime( ((string) $reviewObject["modified"])) ;
										echo '<span class="timeline-date">vom ' .$dt->format('d.m.Y'). "</span>";
										echo "<p>";
										$comments = array();
										foreach ($rev as $section) {
											$comments[] = $section['comment'];
										}
										echo implode(" ", $comments);
										echo "</p>";
										?>
									</div>
									<?php
				  				}
				  				?>
							</div>
						</div>
					</div>

				  	<div class="col-md-6 col-sm-12">
				  		<div class="admin-cart">
							<div class="progress">
								<div class="progress-bar" role="progressbar" style="width: 72%"></div>
							</div>

							<div class="user-card">
								<div class="user-pic-wrapper">
									<img src="https://www.xing.com/image/9_0_2_f13ec55ff_11604536_1/gunnar-wilhelm-foto.1024x1024.jpg" class="user-pic">
								</div>
								<div class="user-body">
									<h1 class="user-name">Gunnar Wilhelm</h1>
									<span class="user-role"><i class="fa fa-thumb-tack" aria-hidden="true"></i>Schüler</span>
									<span class="user-role"><i class="fa fa-calendar" aria-hidden="true"></i>Beigetreten am 04.05.2017</span>
									<span class="user-role"><i class="fa fa-users" aria-hidden="true"></i>Kurse Informatik AG 10. Klasse, Deutsch 10a</span>
								</div>
							</div>
							<div class="user-card">
								<div class="user-pic-wrapper">
									<img src="https://assets.entrepreneur.com/content/16x9/822/20150406145944-dos-donts-taking-perfect-linkedin-profile-picture-selfie-mobile-camera-2.jpeg" class="user-pic">
								</div>
								<div class="user-body">
									<h1 class="user-name">Katja Schmidt</h1>
									<span class="user-role"><i class="fa fa-thumb-tack" aria-hidden="true"></i>Lehrer</span>
									<span class="user-role"><i class="fa fa-calendar" aria-hidden="true"></i>Beigetreten am 04.05.2017</span>
									<span class="user-role"><i class="fa fa-users" aria-hidden="true"></i>Kurse Informatik AG 10. Klasse, Deutsch 10a</span>
								</div>
							</div>
							<div class="user-card">
								<div class="user-pic-wrapper">
									<img src="/assets/avatar.png" class="user-pic">
								</div>
								<div class="user-body">
									<h1 class="user-name">Max Mustermann</h1>
									<span class="user-role"><i class="fa fa-thumb-tack" aria-hidden="true"></i>Schüler</span>
									<span class="user-role"><i class="fa fa-calendar" aria-hidden="true"></i>Beigetreten am 04.05.2017</span>
									<span class="user-role"><i class="fa fa-users" aria-hidden="true"></i>Kurse Informatik AG 10. Klasse, Deutsch 10a</span>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</body>
</html>
<?php
$conn->close();
?>