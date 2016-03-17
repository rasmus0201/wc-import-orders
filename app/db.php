<?php

ini_set('display_errors', 'On');
error_reporting(E_ALL);
mb_internal_encoding("UTF-8");

session_start();

try {
	$db = new PDO('mysql:host=localhost;dbname=wc_invoices;charset=utf8', 'root', '');
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$db->exec("SET NAMES utf8mb4");
} catch (PDOException $e) {
	$error_message = $e->getMessage();
	die("An error occured. ERR: ".$error_message);
}

$db_settings = $db->prepare("SELECT * FROM `settings`");
$db_settings->execute();
$db_settings = $db_settings->fetchAll(PDO::FETCH_ASSOC);

foreach ($db_settings as $setting => $value) {
	$db_settings[$value['setting_name']] = $value['setting_value'];
	unset($db_settings[$value['id']-1]);
}

//define('BASE_PATH', $db_settings['base_path']);
//define('BASE_URL', $db_settings['base_url']);
define('BASE_PATH', getcwd().'/');
define('BASE_URL', fullpageurl());

define('TEMPLATES_URL', BASE_URL.'templates/');
define('STATIC_URL', BASE_URL.'static/');

function fullpageurl() {
    $pageURL = 'http://';
    if ($_SERVER["SERVER_PORT"] != "80") {
        $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
    } else {
        $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
    }
    return $pageURL;
}

// $STH = $DB->prepare("SELECT * FROM users WHERE user = :s");
// $STH->execute(array(25));
// $User = $STH->fetch();

// $sth = $dbh->prepare('SELECT name, colour, calories FROM fruit WHERE calories < :calories AND colour = :colour');
// $sth->bindParam(':calories', $calories);
// $sth->bindParam(':colour', $colour);
// $sth->execute();

//$results = $sth->fetchAll(PDO::FETCH_ASSOC);
