<?php

require 'app/init.php';

$date = ['y'=>2016,'m'=>0,'d'=>30,'h'=>0,'i'=>0,'s'=>0]; // User input

$limit = 1; // User input / from settings array (-1 for all)

$sites = get_sites();
$orders = get_orders();


$orders = loop_orders($sites, $orders, $date, $limit, true);

add_invoices($orders); // IT WILL INSERT EVERYTHING, BE CAREFULL WITH DUPLICATES

pred(get_invoices());

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


function loop_orders($sites, $orders, $date, $limit, $return_only_new_orders = false){
	foreach ($sites as $site => $val) {
		$loaded_orders = json_decode(get_new_orders($val['url'], $val['consumer_key'], $val['consumer_secret'], $date, $limit), true)['orders'];

		if (!isset($orders[$site])) {
			$orders[$site] = [];
		}

		foreach ($loaded_orders as $new_order => $new_order_val) {
			//$orders[$site]['order_'.$new_order_val['id']] = $new_order_val; // Merge instead
			$loaded_orders['order_'.$new_order_val['id']] = $new_order_val;
			$loaded_orders[$new_order] = [];
			unset($loaded_orders[$new_order]);


			if (array_key_exists('order_'.$new_order_val['id'], $orders[$site])) {
				unset($loaded_orders['order_'.$new_order_val['id']]);
			}
		}

		$loaded_orders = array_assoc_reverse($loaded_orders);

		$orders_for_site = $orders[$site];

		unset($orders[$site]);

		$orders[$site] = array_merge($orders_for_site, $loaded_orders);
	}

	krsort($orders);

	update_orders($orders);

	if ($return_only_new_orders) {
		return $loaded_orders;
	}

	return $orders;
}

function get_new_orders($site, $ck, $cs, $min_date, $limit){
	require_once( 'lib/woocommerce-api.php' );
	$options = array(
		'debug'           => true,
		'return_as_array' => false,
		'validate_url'    => false,
		'timeout'         => 30,
		'ssl_verify'      => false,
	);

	if ($min_date['m'] > 10) {
		$min_date['m'] = '0'.$min_date['m'];
	}
	if ($min_date['d'] > 10) {
		$min_date['d'] = '0'.$min_date['d'];
	}
	if ($min_date['h'] > 10) {
		$min_date['h'] = '0'.$min_date['h'];
	}
	if ($min_date['i'] > 10) {
		$min_date['i'] = '0'.$min_date['i'];
	}
	if ($min_date['s'] > 10) {
		$min_date['s'] = '0'.$min_date['s'];
	}


	try {
		$client = new WC_API_Client( $site, $ck, $cs, $options );

		// orders
		$res = $client->orders->get(null, array(
			'fields' => 'id,status,date,order_number,total,subtotal,total_tax,total_shipping,cart_tax,shipping_tax,total_discount,fee_lines.total,fee_lines.total_tax,created_at,completed_at',
			'status' => 'completed,refunded',
			'filter[created_at_min]' => $min_date['y'].'-'.$min_date['m'].'-'.$min_date['d'].'T'.$min_date['h'].':'.$min_date['i'].':'.$min_date['s'].'Z',
			'filter[limit]' => $limit
		));

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
				$date = date("d-m-Y", strtotime(explode(' ', $value['completed_at'])[0]));
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
				$date = date("d-m-Y", strtotime(explode(' ', $value['completed_at'])[0]));
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
	$sth = $db->prepare("SELECT * FROM invoices LIMIT 1");
	$sth->execute();
	$result = $sth->fetch(PDO::FETCH_ASSOC);

	return json_decode($result['invoices'], 1);
}

function update_invoices(array $invoices){
	global $db;
	$id = 1;
	$invoices = json_encode($invoices);
	//$invoices = addslashes('{number:1}');

	$sth = $db->prepare("UPDATE `invoices` SET `invoices` = :invoices WHERE `id` = :id ");
	$sth->bindParam(':invoices', $invoices, PDO::PARAM_STR);
	$sth->bindParam(':id', $id, PDO::PARAM_INT);
	
	if ($sth->execute()) {
		return true;
	}

	return false;
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

function get_orders(){
	global $db;
	$sth = $db->prepare("SELECT * FROM orders LIMIT 1");
	$sth->execute();
	$result = $sth->fetch(PDO::FETCH_ASSOC);

	return json_decode($result['orders'], 1);
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

function update_orders(array $orders){
	global $db;
	$orders = json_encode($orders);

	$sth = $db->prepare("UPDATE `orders` SET `orders` = :orders WHERE `id` = 1");
	$sth->bindParam(':orders', $orders);

	if ( $sth->execute() ){
		return true;
	}

	return false;
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