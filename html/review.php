<?php

include '../check_auth.php';
include '../config.php';
include "../review.php";
if(!isset($_GET['id']) || !isset($_GET['course'])) {
	header("Location: /");
}
$conn = new mysqli($cfg['db_host'], $cfg['db_user'], $cfg['db_password'], $cfg['db_name']);
if ($conn->connect_error) {
	die("Database connection failed: " . $conn->connect_error);
}
$course = $_GET['course'];
$contains = false;
foreach (getReviewTargets($conn, $_SESSION['user_id'], $course) as $tar) {
	if($tar['id'] == $_GET['id']) {
		$contains = true;
	}
}
if (!$contains) {
	header("Location: /info");
	exit;
}

$target = array(
	"id" => $_GET['id'],
	"name" => getName($conn,$_GET['id']),
	"code" => getCode($conn,$_GET['id'], $course)
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
  	<div class="element welcomer z-depth-4">
  		<h2 class="header">Review für <span class="red-text lighten-2"><?php echo $target["name"]?></span> verfassen</h2>
  		<span style="text-align: justify;">
  			Hier kannst du dein Review für den angegeben Benutzer bearbeiten oder verfassen.<br>
  			Bitte halte dich an die Beschreibung der (Kritik-)Punkte und <i>bewerte ernsthaft</i>.
  		</span>
  	</div>
  	<?php

	if(isset($_POST['save-review'])) {
		$i = $target['id'];
		$a = $_SESSION['user_id'];
		$review = array();
		$idx = 0;
		$jdx = 0;

		// I could probably make this OOP as well using Review::fromJSON( getReviewScheme($conn, $course) );, but that's quite a lot of work.
		$json = json_decode(getReviewScheme($conn, $course), JSON_UNESCAPED_UNICODE);
		foreach ($_POST as $name => $value) {
			if($name == "save-review" || startsWith($name, "comment")) {
				continue;
			}
			$n = str_replace("points_", "", $name);
			$idx = ((int) explode("_", $n)[0]);
			$jdx = ((int) explode("_", $n)[1]);
			if(!isset($review[$idx])) {
				$review[] = array();
			}
			if(!isset($review[$idx]['reviews'])) {
				$review[$idx]['reviews'] = array();
			}
			if(!isset($review[$idx]['reviews'][$jdx])) {
				$review[$idx]['reviews'][] = array();
			}
			$review[$idx]['comment'] = $_POST['comment_'.$idx];
			$v = ((int)$value);
			$v = max($v, 0);
			$v = min($v, $json[$idx]['categories'][$jdx]['max_points']);
			$review[$idx]['reviews'][$jdx] = array("points" => ($v));
		}
		setReview($conn, $i, $a, $course, json_encode($review));

		echo "<div class=\"element z-depth-4\"><span class=\"green-text darken-2\">Vielen Dank, dass du deine Bewertung abgegeben hast!</span></div>";
	}

  	?>
	<div class="element z-depth-4">
		<h4>Link zum Code</h4>
		<?php
			$code = getCode($conn, $target['id'], $course);
			if(is_null($code) or empty($code)) {
				echo "<span class=\"red-text darken-4\">".$target["name"]." hat noch keinen Link angegeben</span>";
    	    } else {
				echo "<span>Link zum Code von ".$target["name"].": <a class=\"red-text darken-4\" href=\"".$code."\" target=\"_blank\">Hier klicken</a></span>";
    	    }
		?>
	</div>
	<div class="element z-depth-4">
  		<h4>Review verfassen</h4>
  		<br>
		<?php
			
			$rv = json_decode(
				getReview($conn, $target["id"], $_SESSION['user_id'], $course)['review'],
				JSON_UNESCAPED_UNICODE);
			$review = Review::fromJSON( getReviewScheme($conn, $course) );

			echo '<form action="" method="post">';

			$itemcount = 0;

			foreach ($review->objects as $object) {
				$idx = 0;

				echo '<div class="sect"><p>'.$object->name.'</p>';

				foreach ($object->categories as $cat) {
					echo '<div class="cat"><span class="desc">'	.$cat->description.	'</span>';
					echo '<div class="points"><input type="number" min="0" max="' .$cat->max_points. '" ';
					echo 'value="'.$rv[$itemcount]['reviews'][$idx]['points'].'" name="points_'.$itemcount.'_'.$idx.'">';
					echo '<span> / ' .$cat->max_points. '</span></div></div>';
					$idx = $idx + 1;
				}
				echo '<div class="cat"><span class="desc">Kommentar ' .$object->name. '</span>';
				echo '<div class="input-field"><textarea class="materialize-textarea" name="comment_'.$itemcount.'" ';
				echo 'placeholder="Kommentar">'.$rv[$itemcount]['comment'].'</textarea></div></div></div>';
				$itemcount = $itemcount + 1;
			}
		?>
			<br>
			<input type="submit" value="Bewertung speichern" class="btn waves-effect waves-light" name="save-review">
		</form>
	</div>
</body>
</html>
<?php
$conn->close();
?>