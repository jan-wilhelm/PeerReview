<?php

include '../config.php';

$filePath = $IS_LOCAL ? "../" : "../../info/";

include $filePath. 'review.php';

$admin = (isset($_SESSION['info']['user_level']) && $_SESSION['info']['user_level'] === 1);

if(!isset($_GET['course']) || !$admin) {
	header("Location: " . $ROOT_SITE);
	exit();
}


include $filePath. "header.php";

$conn = new mysqli($cfg['db_host'], $cfg['db_user'], $cfg['db_password'], $cfg['db_name']);

if ($conn->connect_error) {
	die("Database connection failed: " . $conn->connect_error);
}

include $filePath. 'check_auth.php';
include $filePath. 'profile_picture.php';


?>

<body>
	<script type="text/javascript" src="./js/panel.js"></script>
	<script type="text/javascript" src="http://code.jquery.com/color/jquery.color-2.1.2.min.js"></script>

	<script type="text/javascript">
		$(document).ready(function(){
		    $('[data-toggle="tooltip"]').tooltip();

		    function copyText(text) {
			    if (window.clipboardData && window.clipboardData.setData) {
			        // IE specific code path to prevent textarea being shown while dialog is visible.
			        return clipboardData.setData("Text", text); 

			    } else if (document.queryCommandSupported && document.queryCommandSupported("copy")) {
			        var textarea = document.createElement("textarea");
			        textarea.textContent = text;
			        textarea.style.position = "fixed";  // Prevent scrolling to bottom of page in MS Edge.
			        document.body.appendChild(textarea);
			        textarea.select();
			        try {
			            return document.execCommand("copy");  // Security exception may be thrown by some browsers.
			        } catch (ex) {
			            console.warn("Copy to clipboard failed.", ex);
			            return false;
			        } finally {
			            document.body.removeChild(textarea);
			        }
			    }
			}

		    $('#signupkey_copy').click(function(){
		    	const color = copyText($(this).attr("placeholder")) ? "#5cb85c" : "#a94442";
	    		$(this).animate({
					backgroundColor: jQuery.Color(color)
	    		}, 300, function() {
	    			$(this).animate({
						backgroundColor: jQuery.Color("#eeeeee")
	    			}, 300);
	    		});
		    });
		});
	</script>

	<header class="cd-main-header">
		<a class="cd-logo"><img src="img/cd-logo.svg" alt="Logo"></a>

		<a href="#0" class="cd-nav-trigger"><span></span></a>

		<nav class="cd-nav">
			<ul class="cd-top-nav">
				<li><a href="script">Scripts</a></li>
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
							<a href="index.php">Kurse</a>
							<ul>
								<?php
									$courses = getCoursesOfUser($conn, $_SESSION['info']['user_id']);
									foreach ($courses as $course) {
										echo '<li><a href="?course='.$course.'">'.getCourseName($conn, $course).'</a></li>';
									}
								?>
							</ul>
						</li>
						<li class="has-children">
							<a href="signup.php">In Kurs eintragen</a>
						</li>
					</ul>
				</nav>
			</div>
			<?php
			$course = $_GET['course'];
			?>
	
			<div class="right-col">		
				<div class="row">
					<div class="col-12">
						<ol class="breadcrumb">
						  <li class="breadcrumb-item"><a href="<?php echo $ROOT_SITE;?>">Startseite</a></li>
						  <li class="breadcrumb-item">Einstellungen für <a href="<?php echo "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"; ?>">
							<?php
							echo getCourseName($conn, $course);
							?>
							</a>
							</li>
						</ol>
					</div>
				</div>
				<div class="row equal">
					<div class="col-md-12">
						<div class="admin-cart">
							<h3>Einstellungen</h3>
							<div class="settings_div">
							<!--
								<div class="alert alert-danger" role="alert">
								  <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
								  <span class="sr-only">Error:</span>
								  Enter a valid email address
								</div>-->
								<h1>Kurs bearbeiten</h1>
								<div class="divider"></div>
								<?php

								if(isset($_POST['save-settings'])) {

									$newName = htmlspecialchars(trim($_POST['course_name']));
									$message = "";
									$class = "alert-danger";

									if(empty($newName)) {
										$message = "Gib bitte einen gültigen Namen ein!";
									} else {
										setCourseName($conn, $course, $newName);
										$message = "Der Kurs wurde erfolgreich in <i>$newName </i>umbenannt.";
										$class = "alert-success";
									}

									echo '<div class="alert '. $class . '" role="alert">';
									echo"$message";
									echo "</div>";

								}

								?>
								<form action="" method="post">
								  <div class="form-group row">
								    <label class="col-sm-2 col-form-label">SignUp-Key</label>
								    <div class="col-sm-10">
								      <input id="signupkey_copy" data-toggle="tooltip" data-placement="bottom" title="Zum kopieren klicken!" class="form-control" type="text" placeholder="<?php echo getKeyOfCourse($conn, $course); ?>" readonly>
								    </div>
								  </div>
								  <div class="form-group row">
								    <label for="course_name" class="col-sm-2 col-form-label">Name des Kurses</label>
								    <div class="col-sm-10">
								      <input type="text" name="course_name" class="form-control" id="course_name" placeholder="Name des Kurses" value="<?php echo getCourseName($conn, $course); ?>">
								    </div>
								  </div>
								  <input type="submit" id="save-settings" name="save-settings" class="btn btn-warning pull-right" value="Speichern">
								  <input type="submit" id="cancel" name="cancel" class="btn btn-danger pull-right" value="Abbrechen">
								</form>
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