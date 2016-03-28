<?php

if (!defined('BASE_URL')) {
	header('HTTP/1.0 404 not found');
	echo '<h1>404 - Page not found.</h1>';
	exit;
}

function get_new_orders($site, $ck, $cs, $min_date, $max_date, $limit){
	require_once BASE_PATH.'/lib/woocommerce-api.php';
	$options = array(
		'debug'           => true,
		'return_as_array' => false,
		'validate_url'    => false,
		'timeout'         => 45,
		'ssl_verify'      => false,
	);

	$fields = 'id,order_number,created_at,updated_at,completed_at,status,currency,total,subtotal,total_line_items_quantity,total_tax,total_shipping,cart_tax,shipping_tax,total_discount,shipping_methods,payment_details,billing_address,shipping_address,note,customer_ip,customer_id,view_order_url,line_items,shipping_lines,tax_lines,fee_lines,coupon_lines';

	/*if ($min_date['m'] < 10) {
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
	}*/

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
		if ( $e instanceof WC_API_Client_HTTP_Exception ) {
			//echo json_encode($e->get_request());
			echo json_encode($e->get_response());
		}

		//echo $e->getCode();
		return false;
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

		$results[$value['order_id'].'_'.$value['owner_site_id']] = $value;
		$results[$order] = [];
		unset($results[$order]);
	}

	return $results;
}

function add_orders(array $orders){
	if (empty($orders)) {
		return false;
	}

	global $db;

	$sql = "INSERT INTO orders (`invoice_id`, `owner_site_id`, `owner_site_url`, `owner_site_name`, `order_id`, `order_created_at`, `order_updated_at`, `order_completed_at`, `status`, `currency`, `total`, `subtotal`, `total_tax`, `total_shipping`, `shipping_tax`, `cart_tax`, `total_discount`, `shipping_methods`, `payment_details`, `billing_address`, `shipping_address`, `total_line_items_quantity`, `note`, `customer_ip`, `customer_id`, `view_order_url`, `line_items`, `shipping_lines`, `tax_lines`, `fee_lines`, `coupon_lines`, `export_csv`, `updated_at`, `created_at`) VALUES (:invoice_id, :owner_site_id, :owner_site_url, :owner_site_name,  :order_id, :order_created_at, :order_updated_at, :order_completed_at, :status, :currency, :total, :subtotal, :total_tax, :total_shipping, :shipping_tax, :cart_tax, :total_discount, :shipping_methods, :payment_details, :billing_address, :shipping_address, :total_line_items_quantity, :note, :customer_ip, :customer_id, :view_order_url, :line_items, :shipping_lines, :tax_lines, :fee_lines, :coupon_lines, :export_csv, NOW(), NOW()) ON DUPLICATE KEY UPDATE `order_id` = `order_id`";

	foreach ($orders as $order => $value) {
		$sth = $db->prepare($sql);

		$invoice_id = $value['invoice_id'];

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

		$subtotal_csv = $date.';-'.$invoice_id.';0;"1010";"";"'.$value['owner_site_name'].' (ID: '.$id.')";'.$subtotal.';"DKK";100,00;"Salg";"";0;'.$date.';0,00;;"";"";0,00;0;"";0;"";"";"";"";"";0;0,00;"";"";"";"";"";0'."\n";
		$shipping_csv = $date.';-'.$invoice_id.';0;"1040";"";"'.$value['owner_site_name'].' (ID: '.$id.')";'.$shipping_total.';"DKK";100,00;"";"";0;'.$date.';0,00;;"";"";0,00;0;"";0;"";"";"";"";"";0;0,00;"";"";"";"";"";0'."\n";
		$fee_csv = $date.';-'.$invoice_id.';0;"1610";"";"'.$value['owner_site_name'].' (ID: '.$id.')";'.$fee.';"DKK";100,00;"";"";0;'.$date.';0,00;;"";"";0,00;0;"";0;"";"";"";"";"";0;0,00;"";"";"";"";"";0'."\n";
		$total_csv = $date.';-'.$invoice_id.';0;"16200";"";"'.$value['owner_site_name'].' (ID: '.$id.')";-'.$total.';"DKK";100,00;"";"";0;'.$date.';0,00;;"";"";0,00;0;"";0;"";"";"";"";"";0;0,00;"";"";"";"";"";0'."\n";

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

		$export_csv = [
			'separated' => [
				'subtotal'	=> $subtotal_csv,
				'shipping'	=> $shipping_csv,
				'fee'		=> $fee_csv,
				'total'		=> $total_csv,
			],
			'joined' => $subtotal_csv.$shipping_csv.$fee_csv.$total_csv
		];

		$export_csv = json_encode($export_csv);

		$sth->bindParam(':invoice_id', $invoice_id);
		$sth->bindParam(':owner_site_id', $value['owner_site_id']);
		$sth->bindParam(':owner_site_url', $value['owner_site_url']);
		$sth->bindParam(':owner_site_name', $value['owner_site_name']);
		$sth->bindParam(':order_id', $value['id']);
		$sth->bindParam(':order_created_at', $value['created_at']);
		$sth->bindParam(':order_updated_at', $value['updated_at']);
		$sth->bindParam(':order_completed_at', $value['completed_at']);
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
		$sth->bindParam(':export_csv', $export_csv);

		$sth->execute();

		$_SESSION['orders_count'] = $_SESSION['orders_count'] + 1;
	}

	return true;
}

function add_invoices(array $orders){
	global $db;

	$next_invoice = get_setting('next_invoice');

	$invoices = get_invoices();

	if (is_null($invoices) || empty($invoices)) {
		$invoices = [];
	}

	if (is_null($next_invoice) || empty($next_invoice) || !is_numeric($next_invoice) ) {
		return false;
	}

	$sql = "INSERT INTO invoices (`invoice_id`, `owner_site_id`, `owner_site_url`, `owner_site_name`, `order_id`, `created_at`) VALUES (:invoice_id, :owner_site_id, :owner_site_url, :owner_site_name, :order_id, NOW()) ON DUPLICATE KEY UPDATE `invoice_id` = `invoice_id`";

	foreach ($orders as $order => $value) {
		$value['invoice_id'] = (int)$next_invoice;
		$orders['order_'.$value['id']] = $value;

		$sth = $db->prepare($sql);

		$sth->bindParam(':invoice_id', $next_invoice);
		$sth->bindParam(':owner_site_id', $value['owner_site_id']);
		$sth->bindParam(':owner_site_url', $value['owner_site_url']);
		$sth->bindParam(':owner_site_name', $value['owner_site_name']);
		$sth->bindParam(':order_id', $value['id']);

		$sth->execute();

		$next_invoice = $next_invoice + 1;
		$_SESSION['invoices_count'] = $_SESSION['invoices_count'] + 1;
	}

	update_setting('next_invoice', $next_invoice);

	return $orders;
}

function WCApiAddOrdersAndInvoices($sites, $orders, $min_date, $max_date, $limit, $return_only_new_orders = true){
	global $db;
	$error = '';
	foreach ($sites as $site => $val) {
		$new_orders = get_new_orders($val['url'], $val['consumer_key'], $val['consumer_secret'], $min_date, $max_date, $limit);

		if ($new_orders === false) {
			$error .= message('Noget gik galt, tjek dine side indstillinger. (URLs, API Keys)', 'danger');
		}

		$new_orders = json_decode($new_orders, true)['orders'];

		if (!empty($new_orders) && !is_null($new_orders)) {
			$new_orders = array_assoc_reverse($new_orders);

			foreach ($new_orders as $new_order => $new_order_val) {
				//$orders[$site]['order_'.$new_order_val['id']] = $new_order_val; // Merge instead
				$new_orders['order_'.$new_order_val['id']] = $new_order_val;
				$new_orders[$new_order] = [];
				unset($new_orders[$new_order]);

				$new_orders['order_'.$new_order_val['id']]['owner_site_id'] = $val['id'];
				$new_orders['order_'.$new_order_val['id']]['owner_site_url'] = $val['url'];
				$new_orders['order_'.$new_order_val['id']]['owner_site_name'] = $val['name'];

				$key_id = $new_order_val['id'].'_'.$site;

				if ( array_key_exists($key_id, $orders) ) { //isset($orders[$key_id]) 
					unset($new_orders['order_'.$new_order_val['id']]);
				}
			}

			$res = add_invoices($new_orders);

			if ($res === false) {
				#Error
				$error .= message('Ordrer eksisterer allerede.', 'danger');
			}

			$res_2 = add_orders($res);

			if ($res_2 === false) {
				#Error
				$error .= message('Ordrer eksisterer allerede.', 'danger');
			}

			$orders_for_site = $orders;

			unset($orders);

			$orders = array_merge($orders_for_site, $new_orders);
		}
	}

	if (!empty($new_orders) && !is_null($new_orders)) {
		krsort($orders);
	}

	$sth = $db->prepare("UPDATE `settings` SET `setting_value` = NOW() WHERE `setting_name` = 'last_pull_date' ");
	$sth->execute();


	if ($error !== '') {
		return 'false|'.$error;
	}

	if ($return_only_new_orders) {
		return $new_orders;
	}

	return $orders;
}
