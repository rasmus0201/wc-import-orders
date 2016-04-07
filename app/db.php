<?php

ini_set('display_errors', 'On');
error_reporting(E_ALL);
mb_internal_encoding("UTF-8");

date_default_timezone_set("Europe/Copenhagen");

session_start();

try {
	$db = new PDO('mysql:host=localhost;dbname=wc_invoices;charset=utf8', 'root', 'root');
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$db->exec("SET NAMES utf8mb4");
} catch (PDOException $e) {
	$error_message = $e->getMessage();
	die("An error occured. ERR: ".$error_message);
}

$db_settings = $db->prepare("SELECT * FROM settings");
$db_settings->execute();
$db_settings = $db_settings->fetchAll(PDO::FETCH_ASSOC);

foreach ($db_settings as $setting => $value) {
	$db_settings[$value['setting_name']] = $value['setting_value'];
	unset($db_settings[$value['id']-1]);
}

define('BASE_PATH', $db_settings['base_path']);
define('TEMPLATES_PATH', BASE_PATH.'/templates');
define('STATIC_PATH', BASE_PATH.'/static');

define('BASE_URL', $db_settings['base_url']);
define('TEMPLATES_URL', BASE_URL.'/templates');
define('STATIC_URL', BASE_URL.'/static');

$message = '';

//$_SESSION['invoices_count'] = 0;
//$_SESSION['orders_count'] = 0;

#Make pdf template
#Export .pdf in both bulk and by checkbox

#Edit orders directly
#Add orders directly

# Lines of code wc api = 2539
# Lines of code by Rasmus = 1962 + 654
# Lines of code total = 4501 + 654

# Lines now = 4963 + 654
# Lines now = 5427 + 654
# Lines now = 6181 + 654
# Lines now = 6579 + 654
# Lines now = 7259 + 654

# Terminal = find . -name '*.php' | xargs wc -l





