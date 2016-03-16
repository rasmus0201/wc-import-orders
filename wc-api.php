<?php

require 'app/init.php';

$min_date = ['y'=>2016,'m'=>1,'d'=>1,'h'=>0,'i'=>0,'s'=>0]; // User input
$max_date = 0;#['y'=>2016,'m'=>2,'d'=>1,'h'=>0,'i'=>0,'s'=>0]; // User input

$limit = -1; // User input / from settings array (-1 for all)

$sites = get_sites();
$orders = get_orders();


$orders = add_orders($sites, $orders, $min_date, $max_date, $limit);

//pred($orders);

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


function add_orders($sites, $orders, $min_date, $max_date, $limit, $return_only_new_orders = true){
	foreach ($sites as $site => $val) {
		$new_orders = json_decode(get_new_orders($val['url'], $val['consumer_key'], $val['consumer_secret'], $min_date, $max_date, $limit), true)['orders'];

		if (!isset($orders[$site])) {
			$orders[$site] = [];
		}

		$new_orders = array_assoc_reverse($new_orders);

		foreach ($new_orders as $new_order => $new_order_val) {
			//$orders[$site]['order_'.$new_order_val['id']] = $new_order_val; // Merge instead
			$new_orders['order_'.$new_order_val['id']] = $new_order_val;
			$new_orders[$new_order] = [];
			unset($new_orders[$new_order]);


			if (array_key_exists('order_'.$new_order_val['id'], $orders[$site])) {
				unset($new_orders['order_'.$new_order_val['id']]);
			}

			$new_orders['order_'.$new_order_val['id']]['owner_site'] = $site;
		}

		update_orders($new_orders);

		$orders_for_site = $orders[$site];

		unset($orders[$site]);

		$orders[$site] = array_merge($orders_for_site, $new_orders);
	}

	krsort($orders);

	if ($return_only_new_orders) {
		return $new_orders;
	}

	return $orders;
}

function get_new_orders($site, $ck, $cs, $min_date, $max_date, $limit){
	require_once 'lib/woocommerce-api.php';
	$options = array(
		'debug'           => true,
		'return_as_array' => false,
		'validate_url'    => false,
		'timeout'         => 45,
		'ssl_verify'      => false,
	);

	$fields = 'id,order_number,created_at,updated_at,completed_at,status,currency,total,subtotal,total_line_items_quantity,total_tax,total_shipping,cart_tax,shipping_tax,total_discount,shipping_methods,payment_details,billing_address,shipping_address,note,customer_ip,customer_id,view_order_url,line_items,shipping_lines,tax_lines,fee_lines,coupon_lines';

	if ($min_date['m'] < 10) {
		$min_date['m'] = '0'.$min_date['m'];
	}
	if ($min_date['d'] < 10) {
		$min_date['d'] = '0'.$min_date['d'];
	}
	if ($min_date['h'] < 10) {
		$min_date['h'] = '0'.$min_date['h'];
	}
	if ($min_date['i'] < 10) {
		$min_date['i'] = '0'.$min_date['i'];
	}
	if ($min_date['s'] < 10) {
		$min_date['s'] = '0'.$min_date['s'];
	}

	if (empty($max_date) || $max_date == '' || $max_date == 0) {
		$max_date = false;
	} else {
		if ($max_date['m'] < 10) {
			$max_date['m'] = '0'.$max_date['m'];
		}
		if ($max_date['d'] < 10) {
			$max_date['d'] = '0'.$max_date['d'];
		}
		if ($max_date['h'] < 10) {
			$max_date['h'] = '0'.$max_date['h'];
		}
		if ($max_date['i'] < 10) {
			$max_date['i'] = '0'.$max_date['i'];
		}
		if ($max_date['s'] < 10) {
			$max_date['s'] = '0'.$max_date['s'];
		}
	}

	try {
		$client = new WC_API_Client( $site, $ck, $cs, $options );

		// orders
		if (!$max_date) {
			$res = $client->orders->get(null, array(
				'fields' => $fields,
				'status' => 'completed,refunded',
				'order' => 'ASC',
				'filter[created_at_min]' => $min_date['y'].'-'.$min_date['m'].'-'.$min_date['d'].'T'.$min_date['h'].':'.$min_date['i'].':'.$min_date['s'].'Z',
				'filter[limit]' => $limit
			));
		} else {
			$res = $client->orders->get(null, array(
				'fields' => $fields,
				'status' => 'completed,refunded',
				'order' => 'ASC',
				'filter[created_at_min]' => $min_date['y'].'-'.$min_date['m'].'-'.$min_date['d'].'T'.$min_date['h'].':'.$min_date['i'].':'.$min_date['s'].'Z',
				'filter[created_at_max]' => $max_date['y'].'-'.$max_date['m'].'-'.$max_date['d'].'T'.$max_date['h'].':'.$max_date['i'].':'.$max_date['s'].'Z',
				'filter[limit]' => $limit
			));
		}

		return json_encode(json_decode($res->http->response->body));

		//print_r( $client->orders->get( $order_id ) );
		//print_r( $client->orders->update_status( $order_id, 'pending' ) );
		// order refunds
			//print_r( $client->order_refunds->get( $order_id ) );
			//print_r( $client->order_refunds->get( $order_id, $refund_id ) );
			//print_r( $client->order_refunds->create( $order_id, array( 'amount' => 1.00, 'reason' => 'cancellation' ) ) );
			//print_r( $client->order_refunds->update( $order_id, $refund_id, array( 'reason' => 'who knows' ) ) );
			//print_r( $client->order_refunds->delete( $order_id, $refund_id ) );

	} catch ( WC_API_Client_Exception $e ) {
		//echo json_encode($e->getMessage() . PHP_EOL);
		echo json_encode($e->getCode() . PHP_EOL);
		if ( $e instanceof WC_API_Client_HTTP_Exception ) {
			//echo json_encode($e->get_request());
			echo json_encode($e->get_response());
		}
	}
}

function add_invoices(array $orders){
	global $db;

	$sth = $db->prepare("SELECT * FROM `settings` WHERE `setting_name` = :name");
	$key = 'next_invoice';
	$sth->bindParam(':name', $key);
	$sth->execute();
	$res = $sth->fetch(PDO::FETCH_ASSOC);
	$next_invoice = $res['setting_value'];

	$invoices = get_invoices();

	if (!is_null($invoices) && !empty($invoices)) {
		//At least 1 invoice has been made
		foreach ($orders as $site => $order) {
			//Add site name + csv to the array.
			$iteration = 0;
			foreach ($order as $key => $value) {
				$shipping_total = (float)$value['total_shipping']+$value['shipping_tax'];
				$fee = (float)$value['fee_lines'][0]['total']+$value['fee_lines'][0]['total_tax'];
				$cart_discount = (float)$value['total_discount'];
				$date = date("d-m-Y", strtotime(explode(' ', $value['created_at'])[0]));
				$id = $value['id'];
				$total_no_format = (float)$value['total']-$cart_discount;
				$subtotal = number_format(((float)$value['total'] - ($fee+$shipping_total+$cart_discount)), 2, ',', '');
				$total = number_format($total_no_format, 2, ',', '');
				$fee = number_format($fee, 2, ',', '');
				$shipping_total = number_format($shipping_total, 2, ',', '');

				$subtotal_csv = $date.';-'.$next_invoice.';0;"1010";"";"'.$site.' (ID: '.$id.')";'.$subtotal.';"DKK";100,00;"Salg";"";0;'.$date.';0,00;;"";"";0,00;0;"";0;"";"";"";"";"";0;0,00;"";"";"";"";"";0'."\n";
				$shipping_csv = $date.';-'.$next_invoice.';0;"1040";"";"'.$site.' (ID: '.$id.')";'.$shipping_total.';"DKK";100,00;"";"";0;'.$date.';0,00;;"";"";0,00;0;"";0;"";"";"";"";"";0;0,00;"";"";"";"";"";0'."\n";
				$fee_csv = $date.';-'.$next_invoice.';0;"1610";"";"'.$site.' (ID: '.$id.')";'.$fee.';"DKK";100,00;"";"";0;'.$date.';0,00;;"";"";0,00;0;"";0;"";"";"";"";"";0;0,00;"";"";"";"";"";0'."\n";
				$total_csv = $date.';-'.$next_invoice.';0;"16200";"";"'.$site.' (ID: '.$id.')";-'.$total.';"DKK";100,00;"";"";0;'.$date.';0,00;;"";"";0,00;0;"";0;"";"";"";"";"";0;0,00;"";"";"";"";"";0'."\n";

				$invoices['invoice_'.$next_invoice] = $value;				
				$invoices['invoice_'.$next_invoice]['owner_site'] = $site;
				$invoices['invoice_'.$next_invoice]['csv'] = [	
					'separated' => [
						'subtotal'	=> $subtotal_csv,
						'shipping'	=> $shipping_total,
						'fee'		=> $fee_csv,
						'total'		=> $total_csv,
					],
					'joined' => $subtotal_csv.$shipping_csv.$fee_csv.$total_csv
				];
				$next_invoice = $next_invoice + 1;
				++$iteration;
			}
		}

		update_invoices($invoices);
		update_next_invoice_number($next_invoice);
	} else {
		$invoices = [];
		// No invoices yet
		foreach ($orders as $site => $order) {
			//Add site name + csv to the array.
			foreach ($order as $key => $value) {
				$shipping_total = (float)$value['total_shipping']+$value['shipping_tax'];
				$fee = (float)$value['fee_lines'][0]['total']+$value['fee_lines'][0]['total_tax'];
				$cart_discount = (float)$value['total_discount'];
				$date = date("d-m-Y", strtotime(explode(' ', $value['created_at'])[0]));
				$id = $value['id'];
				$total_no_format = (float)$value['total']-$cart_discount;
				$subtotal = number_format(((float)$value['total'] - ($fee+$shipping_total+$cart_discount)), 2, ',', '');
				$total = number_format($total_no_format, 2, ',', '');
				$fee = number_format($fee, 2, ',', '');
				$shipping_total = number_format($shipping_total, 2, ',', '');

				$subtotal_csv = $date.';-'.$next_invoice.';0;"1010";"";"'.$site.' (ID: '.$id.')";'.$subtotal.';"DKK";100,00;"Salg";"";0;'.$date.';0,00;;"";"";0,00;0;"";0;"";"";"";"";"";0;0,00;"";"";"";"";"";0'."\n";
				$shipping_csv = $date.';-'.$next_invoice.';0;"1040";"";"'.$site.' (ID: '.$id.')";'.$shipping_total.';"DKK";100,00;"";"";0;'.$date.';0,00;;"";"";0,00;0;"";0;"";"";"";"";"";0;0,00;"";"";"";"";"";0'."\n";
				$fee_csv = $date.';-'.$next_invoice.';0;"1610";"";"'.$site.' (ID: '.$id.')";'.$fee.';"DKK";100,00;"";"";0;'.$date.';0,00;;"";"";0,00;0;"";0;"";"";"";"";"";0;0,00;"";"";"";"";"";0'."\n";
				$total_csv = $date.';-'.$next_invoice.';0;"16200";"";"'.$site.' (ID: '.$id.')";-'.$total.';"DKK";100,00;"";"";0;'.$date.';0,00;;"";"";0,00;0;"";0;"";"";"";"";"";0;0,00;"";"";"";"";"";0'."\n";

				$invoices['invoice_'.$next_invoice] = $value;				
				$invoices['invoice_'.$next_invoice]['owner_site'] = $site;
				$invoices['invoice_'.$next_invoice]['csv'] = [	
					'separated' => [
						'subtotal'	=> $subtotal_csv,
						'shipping'	=> $shipping_total,
						'fee'		=> $fee_csv,
						'total'		=> $total_csv,
					],
					'joined' => $subtotal_csv.$shipping_csv.$fee_csv.$total_csv
				];
				$next_invoice = $next_invoice + 1;
			}
		}

		update_invoices($invoices);
		update_next_invoice_number($next_invoice);
	}
}

function get_invoices(){
	global $db;
	$sth = $db->prepare("SELECT * FROM invoices");
	$sth->execute();
	$results = $sth->fetchAll(PDO::FETCH_ASSOC);
	return $results;
}

function get_orders(){
	global $db;
	$sth = $db->prepare("SELECT * FROM orders");
	$sth->execute();
	$results = $sth->fetchAll(PDO::FETCH_ASSOC);

	$results = json_decode(json_encode($results), 1);

	foreach ($results as $order => $value) {
		$value['shipping_methods'] = json_decode($value['shipping_methods'], 1);
		$value['payment_details'] = json_decode($value['payment_details'], 1);
		$value['billing_address'] = json_decode($value['billing_address'], 1);
		$value['shipping_address'] = json_decode($value['shipping_address'], 1);
		$value['line_items'] = json_decode($value['line_items'], 1);
		$value['shipping_lines'] = json_decode($value['shipping_lines'], 1);
		$value['tax_lines'] = json_decode($value['tax_lines'], 1);
		$value['fee_lines'] = json_decode($value['fee_lines'], 1);
		$value['coupon_lines'] = json_decode($value['coupon_lines'], 1);
	}

	return $results;
}

function update_invoices(array $invoices){
	if (empty($orders)) {
		return false;
	}

	global $db;

	$sql = "INSERT INTO orders (`invoice_id`, `owner_site`, `order_id`, `created_at`) VALUES (:invoice_id, :owner_site, :order_id, NOW()) ON DUPLICATE KEY UPDATE `order_id` = `order_id`";

	foreach ($orders as $order => $value) {
		$sth = $db->prepare($sql);

		$sth->bindParam(':invoice_id', $value['invoice_id']);
		$sth->bindParam(':owner_site', $value['owner_site']);
		$sth->bindParam(':order_id', $value['id']);
		$sth->bindParam(':created_at', $value['created_at']);

		$sth->execute();
	}

	return true;
}

function update_orders(array $orders){
	if (empty($orders)) {
		return false;
	}

	global $db;

	$sql = "INSERT INTO orders (`owner_site`, `order_id`, `created_at`, `updated_at`, `completed_at`, `status`, `currency`, `total`, `subtotal`, `total_tax`, `total_shipping`, `shipping_tax`, `cart_tax`, `total_discount`, `shipping_methods`, `payment_details`, `billing_address`, `shipping_address`, `total_line_items_quantity`, `note`, `customer_ip`, `customer_id`, `view_order_url`, `line_items`, `shipping_lines`, `tax_lines`, `fee_lines`, `coupon_lines`, `export_csv`) VALUES (:owner_site, :order_id, :created_at, :updated_at, :completed_at, :status, :currency, :total, :subtotal, :total_tax, :total_shipping, :shipping_tax, :cart_tax, :total_discount, :shipping_methods, :payment_details, :billing_address, :shipping_address, :total_line_items_quantity, :note, :customer_ip, :customer_id, :view_order_url, :line_items, :shipping_lines, :tax_lines, :fee_lines, :coupon_lines, :export_csv) ON DUPLICATE KEY UPDATE `order_id` = `order_id`";

	foreach ($orders as $order => $value) {
		$sth = $db->prepare($sql);

		$shipping_methods = json_encode($value['shipping_methods']);
		$payment_details = json_encode($value['payment_details']);
		$billing_address = json_encode($value['billing_address']);
		$shipping_address = json_encode($value['shipping_address']);
		$line_items = json_encode($value['line_items']);
		$shipping_lines = json_encode($value['shipping_lines']);
		$tax_lines = json_encode($value['tax_lines']);
		$fee_lines = json_encode($value['fee_lines']);
		$coupon_lines = json_encode($value['coupon_lines']);

		$fee = 0;

		if (!empty($value['fee_lines'])) {
			$fee = (float)$value['fee_lines'][0]['total']+$value['fee_lines'][0]['total_tax'];
		}

		$shipping_total = (float)$value['total_shipping']+$value['shipping_tax'];
		$cart_discount = (float)$value['total_discount'];
		$date = date("d-m-Y", strtotime(explode(' ', $value['created_at'])[0]));
		$id = $value['id'];
		$total_no_format = (float)$value['total']-$cart_discount;
		$subtotal = number_format(((float)$value['total'] - ($fee+$shipping_total+$cart_discount)), 2, ',', '');
		$total = number_format($total_no_format, 2, ',', '');
		$fee = number_format($fee, 2, ',', '');
		$shipping_total = number_format($shipping_total, 2, ',', '');

		$subtotal_csv = $date.';-{invoice_id};0;"1010";"";"'.$value['owner_site'].' (ID: '.$id.')";'.$subtotal.';"DKK";100,00;"Salg";"";0;'.$date.';0,00;;"";"";0,00;0;"";0;"";"";"";"";"";0;0,00;"";"";"";"";"";0'."\n";
		$shipping_csv = $date.';-{invoice_id};0;"1040";"";"'.$value['owner_site'].' (ID: '.$id.')";'.$shipping_total.';"DKK";100,00;"";"";0;'.$date.';0,00;;"";"";0,00;0;"";0;"";"";"";"";"";0;0,00;"";"";"";"";"";0'."\n";
		$fee_csv = $date.';-{invoice_id};0;"1610";"";"'.$value['owner_site'].' (ID: '.$id.')";'.$fee.';"DKK";100,00;"";"";0;'.$date.';0,00;;"";"";0,00;0;"";0;"";"";"";"";"";0;0,00;"";"";"";"";"";0'."\n";
		$total_csv = $date.';-{invoice_id};0;"16200";"";"'.$value['owner_site'].' (ID: '.$id.')";-'.$total.';"DKK";100,00;"";"";0;'.$date.';0,00;;"";"";0,00;0;"";0;"";"";"";"";"";0;0,00;"";"";"";"";"";0'."\n";

		if (empty($subtotal)) {
			$subtotal_csv = '';
		}
		if (empty($shipping_total)) {
			$shipping_csv = '';
		}
		if (empty($fee) || $fee == 0) {
			$fee_csv = '';
		}
		if (empty($total)) {
			$total_csv = '';
		}

		$export_csv['invoice_'.$id] = [
			'separated' => [
				'subtotal'	=> $subtotal_csv,
				'shipping'	=> $shipping_csv,
				'fee'		=> $fee_csv,
				'total'		=> $total_csv,
			],
			'joined' => $subtotal_csv.$shipping_csv.$fee_csv.$total_csv
		];

		$export_csv['invoice_'.$id] = json_encode($export_csv['invoice_'.$id]);

		$sth->bindParam(':owner_site', $value['owner_site']);
		$sth->bindParam(':order_id', $value['id']);
		$sth->bindParam(':created_at', $value['created_at']);
		$sth->bindParam(':updated_at', $value['updated_at']);
		$sth->bindParam(':completed_at', $value['completed_at']);
		$sth->bindParam(':status', $value['status']);
		$sth->bindParam(':currency', $value['currency']);
		$sth->bindParam(':total', $value['total']);
		$sth->bindParam(':subtotal', $value['subtotal']);
		$sth->bindParam(':total_tax', $value['total_tax']);
		$sth->bindParam(':total_shipping', $value['total_shipping']);
		$sth->bindParam(':shipping_tax', $value['shipping_tax']);
		$sth->bindParam(':cart_tax', $value['cart_tax']);
		$sth->bindParam(':total_discount', $value['total_discount']);
		$sth->bindParam(':shipping_methods', $shipping_methods);
		$sth->bindParam(':payment_details', $payment_details);
		$sth->bindParam(':billing_address', $billing_address);
		$sth->bindParam(':shipping_address', $shipping_address);
		$sth->bindParam(':total_line_items_quantity', $value['total_line_items_quantity']);
		$sth->bindParam(':note', $value['note']);
		$sth->bindParam(':customer_ip', $value['customer_ip']);
		$sth->bindParam(':customer_id', $value['customer_id']);
		$sth->bindParam(':view_order_url', $value['view_order_url']);
		$sth->bindParam(':line_items', $line_items);
		$sth->bindParam(':shipping_lines', $shipping_lines);
		$sth->bindParam(':tax_lines', $tax_lines);
		$sth->bindParam(':fee_lines', $fee_lines);
		$sth->bindParam(':coupon_lines', $coupon_lines);
		$sth->bindParam(':export_csv', $export_csv['invoice_'.$id]);

		$sth->execute();
	}

	return true;
}

function update_next_invoice_number($next_invoice){
	global $db;
	$name = 'next_invoice';

	$sth = $db->prepare("UPDATE `settings` SET `setting_value` = :next_invoice WHERE `setting_name` = :setting_name");
	$sth->bindParam(':next_invoice', $next_invoice);
	$sth->bindParam(':setting_name', $name);
	
	if ($sth->execute()) {
		return true;
	}

	return false;
}

function get_sites(){
	global $db;

	$sth = $db->prepare("SELECT * FROM sites");
	$sth->execute();
	$results = $sth->fetchAll(PDO::FETCH_ASSOC);

	$sites = [];

	foreach ($results as $result => $value) {
		$sites[$value['name']] = $value;
	}

	return $sites;
}

function get_settings(){
	global $db;

	$sth = $db->prepare("SELECT * FROM settings");
	$sth->execute();
	$results = $sth->fetchAll(PDO::FETCH_ASSOC);

	$settings = [];

	foreach ($results as $result => $value) {
		$settings[$value['name']] = $value;
	}

	return $settings;
}

function array_assoc_reverse(array $arr){
	return array_combine( array_reverse(array_keys( $arr )), array_reverse( array_values( $arr ) ) );
}

function pred($arr){
	echo '<pre>';
	var_dump($arr);
	echo '</pre>';
}
?>