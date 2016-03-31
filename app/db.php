<?php

ini_set('display_errors', 'On');
error_reporting(E_ALL);
mb_internal_encoding("UTF-8");

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

#Make some reports (daily, weekly, monthly, yearly, custom)
	#See vat, subtotal, total, shipping, shipping vat, fees, sold products (+amounts etc.)
	#Also see dates and other info.

#Sort orders/invoices/users table by date/order_id/site/total-order-price/name/invoice_id and id/name/email/role
#Make pdf template
#Export (both .csv and .pdf)
	#from_date - to_date
	#invoice_xx to invoice_yy
#Edit orders
#Add orders directly

# Lines of code wc api = 2539
# Lines of code by Rasmus = 1962
# Lines of code total = 4501

# Lines now = 4963
# Lines now = 5427

# Terminal = find . -name '*.php' | xargs wc -l





