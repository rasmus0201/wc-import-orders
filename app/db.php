<?php

ini_set('display_errors', 'On');
error_reporting(E_ALL);
mb_internal_encoding("UTF-8");

date_default_timezone_set("Europe/Copenhagen");

session_start();

try {
	//$db = new PDO('mysql:host=mysql44.unoeuro.com;dbname=ulvemosenshandelsselskab_dk_db;charset=utf8', 'ulvemosensh_dk', 'nvf55pmz');
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
define('ADMIN_PATH', BASE_PATH.'/admin');
define('TEMPLATES_PATH', BASE_PATH.'/templates');
define('STATIC_PATH', BASE_PATH.'/static');

define('BASE_URL', $db_settings['base_url']);
define('ADMIN_URL', BASE_URL.'/admin');
define('TEMPLATES_URL', BASE_URL.'/templates');
define('STATIC_URL', BASE_URL.'/static');

if (isset($_SESSION['loggedin']))  {
	if ($_SESSION['loggedin'] == true) {
		$sth_1 = $db->prepare("SELECT COUNT(*) FROM orders");
		$sth_2 = $db->prepare("SELECT COUNT(*) FROM invoices");
		$sth_3 = $db->prepare("SELECT COUNT(*) FROM sites");
		$sth_4 = $db->prepare("SELECT COUNT(*) FROM users");

		$result_1 = $sth_1->execute();
		$result_2 = $sth_2->execute();
		$result_3 = $sth_3->execute();
		$result_4 = $sth_4->execute();

		$_SESSION['orders_count'] = $sth_1->fetchColumn();
		$_SESSION['invoices_count'] = $sth_2->fetchColumn();
		$_SESSION['sites_count'] = $sth_3->fetchColumn();
		$_SESSION['users_count'] = $sth_4->fetchColumn();
	}
}

$message = '';

#On next push
	#Make ready for wc subscriptions
	#(Edit orders directly)

# Lines of code wc api = 2539
# Lines of code by Rasmus = 1962 + 654
# Lines of code total = 4501 + 654

# Lines now = 4963 + 654
# Lines now = 5427 + 654
# Lines now = 6181 + 654
# Lines now = 6579 + 654
# Lines now = 7259 + 654
# Lines now = 7769 + 839

# --- Excl. /lib --- #
# Lines now = 5475 + 839 = 6314


# Terminal = find . -name '*.php' | xargs wc -l