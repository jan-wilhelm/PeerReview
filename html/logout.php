<?php
session_start();
unset($_SESSION);
session_destroy();
session_write_close();

include "../config.php";

header('Location: ' . $ROOT_SITE);
die;
?>