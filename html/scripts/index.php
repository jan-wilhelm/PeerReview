<?php
include '../../config.php';

$filePath = $IS_LOCAL ? "../../" : "../../../info/";
include $filePath. 'review.php';

include $filePath. "header.php";
include $filePath. "mailer.php";

$conn = new mysqli($cfg['db_host'], $cfg['db_user'], $cfg['db_password'], $cfg['db_name']);

if ($conn->connect_error) {
	die("Database connection failed: " . $conn->connect_error);
}

$admin = (isset($_SESSION['info']['user_level']) && $_SESSION['info']['user_level'] === 1);

include $filePath. 'check_auth.php';
include $filePath. 'profile_picture.php';

$scripts = getScriptsForUser($conn, $_SESSION["info"]["user_id"]);

?>

<body>
	<script type="text/javascript" src="<?php echo $relativeFilePath; ?>js/panel.js"></script>
	<header class="cd-main-header">
		<a class="cd-logo"><img src="<?php echo $relativeFilePath; ?>img/cd-logo.svg" alt="Logo"></a>

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
						<li><a href="<?php echo $ROOT_SITE;?>settings.php">Einstellungen</a></li>
						<li><a href="<?php echo $ROOT_SITE;?>logout.php">Logout</a></li>
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
							<a href="<?php echo $ROOT_SITE;?>index.php"><i class="fa fa-users" aria-hidden="true"></i>Kurse</a>
							<ul>
								<?php
									$courses = getCoursesOfUser($conn, $_SESSION['info']['user_id']);
									foreach ($courses as $course) {
										echo '<li><a href="' . $ROOT_SITE . '?course='.$course.'">'.getCourseName($conn, $course).'</a></li>';
									}
								?>
							</ul>
						</li>
						<li>
							<a href="<?php echo $ROOT_SITE;?>signup.php"><i class="fa fa-user-plus" aria-hidden="true"></i>In Kurs eintragen</a>
						</li>
					</ul>
					<ul class="nav nav-sidebar">
						<li class="cd-label">Scripts</li>
						<li>
							<a href="http://www.codeskulptor.org/docs.html#tabs-Python"><i class="fa fa-book" aria-hidden="true"></i>Docs</a>
						</li>
						<li class="action-btn"><a href="<?php echo $ROOT_SITE;?>script"><i class="fa fa-file-text" aria-hidden="true"></i>Neues Script</a></li>
					</ul>
				</nav>
			</div>
    		<div class="right-col">
				<div class="row tiles_count">
					<div class="tiles_col col-md-2 col-sm-4 col-xs-6">
						<span class="tiles_desc">Deine Programme</span>
						<span class="tiles_number">
						<?php
							echo sizeof($scripts);
						?>
						</span>
					</div>
				</div>

				<div class="row">
					<div class="col-12">
						<ol class="breadcrumb">
						  <li class="breadcrumb-item"><a href="<?php echo $ROOT_SITE; ?>">Startseite</a></li>
						  <li class="breadcrumb-item"><a href="<?php echo "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"; ?>">Scripts</a>
							</li>
						</ol>
					</div>
				</div>

			    <div class="row equal">
				  	<div class="col-md-6 col-xs-12">
				  		<div class="admin-cart">
				  			<h1>Hallo, <?php echo $_SESSION['info']['name'];?>!</h1>
				  			<span>Hier kannst du ein neues Programm erstellen, existierenden Code bearbeiten und Scripts ausführen. Viel Spaß und Happy Coding!</span>
				  		</div>
				  	</div>
				  	<div class="col-md-6 col-xs-12">
				  		<div class="admin-cart">
				  			<h1>Deine Programme</h1>
				  			<table class="table table-hover">
				  				<thead>
				  					<tr>
				  						<th>#</th>
				  						<th>Name</th>
				  						<th>Änderungsdatum</th>
				  					</tr>
				  				</thead>
				  				<tbody>
				  					<?php
				  					foreach ($scripts as $scr) {
				  						echo '<tr><td>' . $scr["script_id"] . '</td><td><a target="_blank" href="'.$ROOT_SITE.'script/?id=' . $scr["script_id"] . '">' . $scr["name"] . '</a></td><td>' . $scr["last_modified"] . '</td>';
				  					}
				  					?>
				  				</tbody>
				  			</table>
				  		</div>
				  	</div>
				</div>
				<div class="row">
					<div class="col-xs-12 text-center">
						<a href="<?php echo $ROOT_SITE;?>script" class="btn btn-primary btn-lg"><i class="fa fa-file-text" aria-hidden="true"></i>Neues Script</a>
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