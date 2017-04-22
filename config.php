<?php

session_start();
require "review.class.php";
$cfg = array();

// Der Name der Datenbank
$cfg["db_name"] = 'info';

// Der Host der Datenbank
$cfg['db_host'] = 'localhost';

// Der Benutzername der Datenbank
$cfg['db_user'] = 'root';

// Das Password der Datenbank
$cfg['db_password'] = 'password';

$IS_LOCAL = true;

$ROOT_SITE = "/";

?>