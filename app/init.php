<?php

require_once 'db.php';

require_once 'wc-api.php';
require_once 'user.php';


$global['project_name'] = 'ULVEMOSENSHANDELSSELSKAB / Administrationsside';

$min_date = ['y'=>2016,'m'=>1,'d'=>1,'h'=>0,'i'=>0,'s'=>0]; // User input
$max_date = 0;#['y'=>2016,'m'=>2,'d'=>1,'h'=>0,'i'=>0,'s'=>0]; // User input

$limit = -1; // User input / from settings array (-1 for all)

//$sites = get_sites();
//$orders = get_orders();

//$orders = add_orders($sites, $orders, $min_date, $max_date, $limit);

//add_invoices($orders); // IT WILL INSERT EVERYTHING, BE CAREFULL WITH DUPLICATES

#Login, Logout, Register function
	#Make "Administration panel admin-panel"
#User Interface
	#Choose from date - min. last_pull_date - a day (an hour what ever just be sure to not miss any orders!!)
	#See pulled orders from each site AND See all invoices - see owner site (all data, if click)
		#Order by date, id, or total order price
	#Make some reports (daily, weekly, monthly, yearly, custom)
		#See vat, subtotal, total, shipping, shipping vat, fees
		#Also see dates and other info.
	#Export
		#Choose invoices
			#Either by bulk (from invoice_xx to invoice_yy or by date) OR By checkbox
		#Choose the export form 
			#Either .csv or as the pdf - (invoice template from jellybeans.dk )