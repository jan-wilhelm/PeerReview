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
        updateScript($conn, $_SESSION["info"]["user_id"], $id, $_POST['code']);

    } else {
        $id = createScript($conn, $_SESSION["info"]["user_id"], $_POST['code'], htmlspecialchars(trim($_POST['code-name'])));
    }
    $_SESSION["info"]["current_script_id"] = $id;
    echo $id;
    exit;

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
    <link rel="stylesheet" href="theme/monokai.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" type="text/css" href="./css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="./css/font-awesome.min.css">

    <script src="addon/fold/foldcode.js"></script>
    <script src="addon/fold/foldgutter.js"></script>
    <script src="addon/fold/brace-fold.js"></script>
    <script src="addon/fold/indent-fold.js"></script>
    <script src="addon/fold/comment-fold.js"></script>
    <script src="mode/python/python.js"></script>

    <script src="mode/python/python.js"></script>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
</head>

<body>
<div id="loader-wrapper">
    <div id="loader"></div>
</div>

    <div id="container">
        <div class="left" id="buttons">
            <ul>
                <li id="run-button"><i class="fa fa-play-circle" aria-hidden="true"></i></li>
                <li id="save-button"><i class="fa fa-floppy-o" aria-hidden="true"></i></li>
                <li><a href="<?php echo $ROOT_SITE;?>"><i class="fa fa-home" aria-hidden="true"></i></a></li>
            </ul>
        </div>
        <div class="left">
            <textarea id="code"><?php
                if(isset($_GET['id'])) {
                    $script = getScriptForScriptId($conn, intval($_GET['id']));
                    echo $script['script'];
                }
            ?></textarea>
        </div>
        <div id="right">
            <div id="console">
                <h1>Console</h1>
                <pre id="output"></pre>
            </div>
            <div id="debug">
                <h1>Errors</h1>
                <pre id="debugout"></pre>
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
        setTimeout(function() {
            $('#loader-wrapper').fadeOut(3000, function() {
                $(this).remove();
            });
        }, 3000);

        var savedCode = null;

        function saveCode() {
            var code = editor.getValue();

            if(code == savedCode) {
                toastr.warning("Du hast dieses Programm ohne Änderungen bereits gespeichert!", "Fehler!")
                return;
            }
            const data = {
                    "save-code": null,
                    "code": code,
                    "overwrite": $('#overwrite-check')[0].checked ? 1 : 0,
                    "code-name": $('#code-name').val()
                };
            $.ajax({ 
                url: location.protocol + '//' + location.host + location.pathname,
                data: data,
                type: 'post',
                success: function(result) {
                    toastr.success('Das Script mit der ID ' + result + " wurde gespeichert!", 'Geschafft!');
                    history.pushState(null, null, "?id=" + result);
                    savedCode = code;
                },
                error: function(error) {
                    toastr.error(error, 'Fehler!');
                }
            });
        }

        $('#save-agree').click(function() {
            $('#save-modal').modal('toggle');
            saveCode();
        });

        $('#save-button').click(function() {
            $('#overwrite-check')[0].checked = true;
            $('#name-form').css("display", "none");
            $('#save-modal').modal().modal("open");
        });

        $('#overwrite-check').click(function() {
            var display = $(this)[0].checked ? "none" : "block";
            $('#name-form').css("display", display);
        });

    </script>
</body>

</html>
