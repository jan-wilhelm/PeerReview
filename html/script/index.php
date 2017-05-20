<?php
include '../../config.php';

$filePath = $IS_LOCAL ? "../../" : "../../../info/";
include $filePath. 'review.php';

include $filePath. 'check_auth.php';

$conn = new mysqli($cfg['db_host'], $cfg['db_user'], $cfg['db_password'], $cfg['db_name']);

if ($conn->connect_error) {
	die("Database connection failed: " . $conn->connect_error);
}

if(isset($_GET['id'])) {
	$_SESSION["info"]["current_script_id"] = intval($_GET['id']);
}

if(isset($_POST['save-code']) && isset($_POST['code']) && isset($_POST['code-name'])) {
	
	if(isset($_POST['overwrite']) && intval($_POST['overwrite']) == 1 && isset($_SESSION["info"]["current_script_id"])) {
		$id = $_SESSION["info"]["current_script_id"];

		if(userOwnsScript($conn, $_SESSION["info"]["user_id"], $id)) {
			updateScript($conn, $_SESSION["info"]["user_id"], $id, $_POST['code']);
		}

	} else {
		$id = createScript($conn, $_SESSION["info"]["user_id"], htmlspecialchars(trim($_POST['code-name'])));
		updateScript($conn, $_SESSION["info"]["user_id"], $id, $_POST['code']);
	}
	$_SESSION["info"]["current_script_id"] = $id;
	echo $id;
	exit;
}

if(isset($_GET['id'])) {
	if(isset($_GET["v"])) {
		$version = intval($_GET["v"]);
		$script = getScriptForScriptId($conn, intval($_GET['id']), $version);
		if(is_null($script) || empty($script)) {
			$script = getNewestScriptForScriptId($conn, intval($_GET['id']));
		}
	} else {
		$script = getNewestScriptForScriptId($conn, intval($_GET['id']));
	}
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<script origsrc="http://www.codeskulptor.org/js/codemirror-compressed.js"></script>
	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js"></script>
	<script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>
	<script src="http://www.codeskulptor.org/js/jquery.flot.min.js"></script>
	<script src="http://www.codeskulptor.org/js/jquery.flot.axislabels.min.js"></script>
	<script src="http://www.codeskulptor.org/js/jquery.flot.orderbars.min.js"></script>
	<script src="http://www.codeskulptor.org/js/numeric-1.2.6.min.js"></script>
	<script src="http://www.codeskulptor.org/skulpt/skulpt.min.js"></script>
	<script src="http://www.codeskulptor.org/skulpt/skulpt-stdlib.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
	<link rel="stylesheet" type="text/css" href="https:////cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">
	<script src="lib/codemirror.js"></script>
	<link rel="stylesheet" href="lib/codemirror.css">
	<link rel="stylesheet" href="theme/<?php $theme = isset($_COOKIE["scripttheme"]) ? $_COOKIE["scripttheme"] : "monokai"; echo $theme;?>.css">
	<link rel="stylesheet" href="style.css">
	<link rel="stylesheet" type="text/css" href="./css/font-awesome.min.css">
	<link rel="stylesheet" type="text/css" href="./css/font-awesome.min.css">
	<link rel="stylesheet" href="addon/hint/show-hint.css">
	<?php if(isset($script)) {
		echo "<title>{$script["name"]}</title>";
	}?>
	<script src="addon/fold/foldcode.js"></script>
	<script src="addon/fold/foldgutter.js"></script>
	<script src="addon/fold/brace-fold.js"></script>
	<script src="addon/fold/indent-fold.js"></script>
	<script src="addon/fold/comment-fold.js"></script>
	<script src="addon/hint/python-hint.js"></script>
	<script src="addon/hint/show-hint.js"></script>
	<script src="addon/edit/matchbrackets.js"></script>
	<script src="addon/edit/closebrackets.js"></script>
	<script src="mode/python/python.js"></script>

	<script src="mode/python/python.js"></script>
	<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
</head>

<body>
<!--<div id="loader-wrapper">
	<div id="loader"></div>
</div>-->

	<div id="container">
		<div class="left" id="buttons">
			<ul>
				<li id="run-button"><i class="fa fa-play-circle" aria-hidden="true"></i></li>
				<li id="save-button"><i class="fa fa-floppy-o" aria-hidden="true"></i></li>
				<li id="theme-button"><i class="fa fa-cog" aria-hidden="true"></i></li>
				<li><a href="http://www.codeskulptor.org/docs.html#tabs-Python"><i class="fa fa-book" aria-hidden="true"></i></a></li>
				<li><a href="<?php echo $ROOT_SITE;?>"><i class="fa fa-home" aria-hidden="true"></i></a></li>
			</ul>
		</div>
		<div class="left">
			<textarea id="code"><?php
				if(isset($script)) {
					echo $script['script'];
				}
				?></textarea>
		</div>
		<div id="right">
			<div id="console">
				<h1>Console</h1>
				<pre id="output"></pre>
			</div>
			<div id="scripts">
				<h1>Öffne Programm</h1>
				<div class="scripts-wrapper">
					<?php
					echo '<ul class="list-group">';
					$scripts = getScriptsForUser($conn, $_SESSION["info"]["user_id"]);
					foreach ($scripts as $script) {
						echo '<li class="list-group-item"><a href="?id=' . $script["script_id"] . '">' . $script["name"] . '</a> (' . $script["last_modified"] . ')</li>';
					}
					echo '</ul>';
					?>
				</div>
			</div>
		</div>
		<div class="modal fade" id="theme-modal" tabindex="-1" role="dialog" aria-labelledby="theme-modal-label">
		  <div class="modal-dialog" role="document">
			<div class="modal-content">
			  <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="theme-modal-label">Einstellungen</h4>
			  </div>
				<div class="modal-body">
					<form class="form-horizontal">
						<label for="theme-select">Wähle ein Style-Theme aus:</label>
						<select class="form-control" id="theme-select">

						<?php $themes = array('3024-day', '3024-night', 'abcdef', 'all-hallow-eve', 'ambiance-mobile', 'ambiance', 'amy', 'argonaut', 'arona', 'base16-dark', 'base16-light', 'bbedit', 'bespin', 'birds-of-paradise', 'black-pearl-ii', 'black-pearl', 'blackboard-black', 'blackboard', 'bongzilla', 'chanfle', 'chrome-devtools', 'classic-modified', 'clouds-midnight', 'clouds', 'cobalt', 'coda', 'colorforth', 'cssedit', 'cube2media', 'darkpastel', 'dawn', 'demo', 'django-smoothy', 'django', 'dracula', 'duotone-dark', 'duotone-light', 'eclipse', 'eiffel', 'elegant', 'emacs-strict', 'erlang-dark', 'espresso-libre', 'espresso-soda', 'espresso-tutti', 'espresso', 'fade-to-grey', 'fake', 'fantasyscript', 'fluidvision', 'freckle', 'friendship-bracelet', 'github', 'glitterbomb', 'happy-happy-joy-joy-2', 'hopscotch', 'icecoder', 'idle', 'idlefingers', 'iplastic', 'ir_black', 'ir_white', 'isotope', 'johnny', 'juicy', 'krtheme', 'kuroir', 'lazy', 'lesser-dark', 'liquibyte', 'lowlight', 'mac-classic', 'made-of-code', 'magicwb-(amiga)', 'material', 'mbo', 'mdn-like', 'merbivore-soft', 'merbivore', 'midnight', 'monoindustrial', 'monokai-bright', 'monokai-fannonedition', 'monokai-sublime', 'monokai', 'mreq', 'neat', 'neo', 'night', 'nightlion', 'notebook', 'oceanic-muted', 'oceanic', 'panda-syntax', 'paraiso-dark', 'paraiso-light', 'pastel-on-dark', 'pastels-on-dark', 'pastie', 'plasticcodewrap', 'prospettiva', 'putty', 'rails-envy', 'railscasts', 'rdark', 'rhuk', 'rubyblue', 'ryan-light', 'seti', 'sidewalkchalk', 'slush-&-poppies', 'smoothy', 'solarized-(dark)', 'solarized-(light)', 'solarized', 'spacecadet', 'spectacular', 'summer-sun', 'summerfruit', 'sunburst', 'swyphs-ii', 'tango', 'text-ex-machina', 'the-matrix', 'tomorrow-night-blue', 'tomorrow-night-bright', 'tomorrow-night-eighties', 'tomorrow-night', 'tomorrow', 'toulousse-lautrec', 'toy-chest', 'ttcn', 'tubster', 'twilight', 'venom', 'vibrant-fin', 'vibrant-ink', 'vibrant-tango', 'xq-dark', 'xq-light', 'yeti', 'zenburn', 'zenburnesque');

							foreach ($themes as $option) {
								echo '<option' . ($option==$theme ? " selected" : "") . '>' . $option . "</option>";
							}
						?>
						</select>
					</form>

				</div>
			  <div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Abbrechen</button>
			  </div>
			</div>
		  </div>
		</div>

		<div class="modal fade" id="save-modal" tabindex="-1" role="dialog" aria-labelledby="save-modal-label">
		  <div class="modal-dialog" role="document">
			<div class="modal-content">
			  <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="save-modal-label">Programm speichern</h4>
			  </div>
			  <div class="modal-body">

				<div class="text-center">
					<label for="overwrite-check">Überschreiben?</label>
					<input id="overwrite-check" type="checkbox" autocomplete="off" checked>
				</div>
				<form class="form-horizontal" id="name-form" style="display: none;">
					<label for="code-name">Name des Programms</label>
					<input type="text" class="form-control" id="code-name" placeholder="Mein Programm">
				</form>

			  </div>
			  <div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Abbrechen</button>
				<button type="button" id="save-agree" class="btn btn-warning" data-toggle="modal" data-target="#set-link-modal">
					Speichern
				</button>
			  </div>
			</div>
		  </div>
		</div>
	</div>

	<script src="codeskulptor.js"></script>
	<script type="text/javascript">

		$('#theme-button').click(function() {
		    $('#theme-modal').modal().modal('open');
		});

		function errorLoadingTheme(name) {
		    toastr.error("Theme " + name + " konnte nicht geladen werden.");
		}

		function setCookie(cname, cvalue, exdays) {
		    var d = new Date();
		    d.setTime(d.getTime() + (exdays*24*60*60*1000));
		    var expires = "expires="+ d.toUTCString();
		    document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
		}

		$('#theme-select').change(function(){
		    const name = $('#theme-select')[0].value;
		    try {
		        loadStyleSheet( '<?php echo $ROOT_SITE;?>script/theme/' + name + ".css", function( success, link ) {
		            if ( success ) {
		                editor.setOption("theme", name);
		                toastr.success("Theme " + name + " wurde ausgewählt und gespeichert!");
		                setCookie("scripttheme", name, 30);
		            } else {
		                errorLoadingTheme(name);
		            }
		        });
		    } catch(e) {
		        errorLoadingTheme(name);
		    }
		});


		$('#overwrite-check').click(function() {
		    var display = $(this)[0].checked ? "none" : "block";
		    $('#name-form').css("display", display);
		});
	</script>
</body>

</html>
