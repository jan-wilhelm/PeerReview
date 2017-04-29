<?php
include '../config.php';

$filePath = $IS_LOCAL ? "../" : "../../info/";

include $filePath. "review.php";

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

			<div class="right-col">
				<div class="row equal">
					<div class="col-md-4">
						<div class="admin-cart">
							<h3>Upload</h3>
							<?php
							if(!empty( $_FILES )) {
								$uploaddir = getPath($_SESSION['info']['user_id'], $IS_LOCAL);

								if(!is_dir($uploaddir)) {
									mkdir($uploaddir, 777, true);
								}

								$uploadfile = $uploaddir . basename($_FILES['avatarimage']['name']);

								$error = false;

								if ($_FILES['avatarimage']['error'] !== UPLOAD_ERR_OK) {
									$error = true;
								}

								$info = getimagesize($_FILES['avatarimage']['tmp_name']);
								if ($info === FALSE) {
									$error = true;
								}

								if (($info[2] !== IMAGETYPE_GIF) && ($info[2] !== IMAGETYPE_JPEG) && ($info[2] !== IMAGETYPE_PNG)) {
									$error = true;
								}

								echo '<pre>';

								if(!$error) {

									$files = glob($uploaddir."/*"); // get all file names present in folder
									foreach($files as $file){ // iterate files
									  if(is_file($file))
									    unlink($file); // delete the file
									}

									// scale the image down to a maximum of 500 pixels
									$result = move_uploaded_file( $_FILES['avatarimage']['tmp_name'], $uploadfile );
									if ($result) {
									    echo "File is valid, and was successfully uploaded.\n";
										$orig_image = imagecreatefromfile($uploadfile);
										$image_info = getimagesize($uploadfile); 
										$width_orig  = $image_info[0]; // current width as found in image file
										$height_orig = $image_info[1]; // current height as found in image file

										$MAX_PIXELS = 500;

										//
										// 	w / h 	= 	wo / ho
										//	w = wo / ho * h
										//	h / w = ho / wo
										//	h = ho / wo * w
										//
										
										if($width_orig > $height_orig) {
											$width = $MAX_PIXELS;
											$height = $height_orig * $width / $width_orig;
										} else {
											$height = $MAX_PIXELS;
											$width = $width_orig * $height / $height_orig;
										}

										$destination_image = imagecreatetruecolor($width, $height);
										imagealphablending( $destination_image, false );
										imagesavealpha( $destination_image, true );
										imagecopyresampled($destination_image, $orig_image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
										// This will just copy the new image over the original at the same filePath.
										imagepng($destination_image, $uploadfile, 0);
									} else {
										echo "Es gab einen Fehler bei dem Versuch, die Datei zu ändern. Bitte kontaktiere den Administrator oder einen Lehrer.<br>";
									}
								} else {
									echo "Es dürfen nur <code>.gif</code>, <code>.jpg</code>, <code>.jpeg</code> oder <code>.png</code> Dateien hochgeladen werden.";
								}

								echo '</pre>';
							?>
							<?php
							} else { ?>							
								<form action="" method="post" enctype="multipart/form-data" class="form form-horizontal">
									Select image to upload:
									<input type="file" name="avatarimage" id="file-upload">
									<input type="submit" class="btn btn-success" value="Upload Image" name="submit" id="upload-avatar">
								</form>
							<?php
							}
							?>
						</div>
					</div>
					<div class="col-md-4">
						<div class="admin-cart">
							<h3>Test</h3>
							<?php
							echo getImageTagForHTML( $_SESSION['info']['user_id'], $IS_LOCAL, $ROOT_SITE); ?>
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