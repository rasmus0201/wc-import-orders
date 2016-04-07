<?php

//require_once 'db.php';

require_once 'functions.php';
require_once 'user.php';
require_once 'wc-api.php';

if (!defined('BASE_URL')) {
	header('HTTP/1.0 404 not found');
	echo '<h1>404 - Page not found.</h1>';
	exit;
}

$current_url = str_replace(BASE_PATH.'/','',$_SERVER["SCRIPT_FILENAME"]);
$global['current_url'] = $current_url;
$global['project_name'] = 'Administration';

$titles = [
	'index.php' => $global['project_name'],
	'admin/index.php' => 'Dashboard',
	'admin/profile.php' => 'Profil',
	'admin/settings.php' => 'Indstillinger',
	'admin/orders.php' => 'Ordre',
	'admin/reports.php' => 'Rapporter',
	'admin/invoices.php' => 'Fakturaer',
	'admin/sites.php' => 'WC shops',
	'admin/users.php' => 'Brugere',
	'admin/tools.php' => 'Værktøjer',
	'admin/profit-estimate.php' => 'Avanceberegner',
	'admin/label-maker.php' => 'Lav labels',
	'admin/logout.php' => 'Log ud',
];

if (array_key_exists($global['current_url'], $titles)) {
	$global['site_title'] = $titles[$global['current_url']];
	$global['site_niceurl'] = niceurl($global['current_url']);
} else {
	$global['site_title'] = $global['project_name'];
	$global['site_niceurl'] = niceurl($global['site_title']);
}