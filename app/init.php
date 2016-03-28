<?php

require_once 'db.php';

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
$global['project_name'] = 'ULVEMOSENS HANDELSSELSKAB / Administrationsside';

$titles = [
	'index.php' => $global['project_name'],
	'admin/index.php' => 'Dashboard',
	'admin/profile.php' => 'Profil',
	'admin/settings.php' => 'Indstillinger',
	'admin/orders.php' => 'Ordre',
	'admin/reports.php' => 'Rapporter',
	'admin/invoices.php' => 'Fakturaer',
	'admin/sites.php' => 'WC shops',
	'admin/logout.php' => 'Log ud',
];

if (array_key_exists($global['current_url'], $titles)) {
	$global['site_title'] = $titles[$global['current_url']];
	$global['site_niceurl'] = niceurl($global['current_url']);
} else {
	$global['site_title'] = $global['project_name'];
	$global['site_niceurl'] = niceurl($global['site_title']);
}

//$_SESSION['invoices_count'] = 0;
//$_SESSION['orders_count'] = 0;

#Login, Logout, Register function
	#Make "Administration panel admin-panel"
#User Interface
	#invoice/order Tables: Order by date, id, or total-order-price, or name
	#Make some reports (daily, weekly, monthly, yearly, custom)
		#See vat, subtotal, total, shipping, shipping vat, fees
		#Also see dates and other info.
	#Export
		#Choose invoices
			#Either from invoice_xx to invoice_yy or by date
		#Choose the export form 
			#PDF


# Lines of code wc api = 2539
# Lines of code by Rasmus = 1962
# Lines of code total = 4501

# Terminal = find . -name '*.php' | xargs wc -l




