<?php

if (!defined('BASE_URL')) {
	header('HTTP/1.0 404 not found');
	echo '<h1>404 - Page not found.</h1>';
	exit;
}

function fullpageurl() {
	$pageURL = 'http://';
	if ($_SERVER["SERVER_PORT"] != "80") {
		$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
	} else {
		$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
	}
	return $pageURL;
}

function get_current_user_ip(){
	if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} else {
		$ip = $_SERVER['REMOTE_ADDR'];
	}

	return $ip;
}

function message($str, $type = 'success', $dismissable = true){
	$return = '';

	if ($dismissable) {
		switch ($type) {
			case 'success':
				$return = '<div class="alert alert-success alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Luk"><span aria-hidden="true">&times;</span></button>'.$str.'</div>';
				break;

			case 'info':
				$return = '<div class="alert alert-info alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Luk"><span aria-hidden="true">&times;</span></button>'.$str.'</div>';
				break;

			case 'warning':
				$return = '<div class="alert alert-warning alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Luk"><span aria-hidden="true">&times;</span></button>'.$str.'</div>';
				break;

			case 'danger':
				$return = '<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Luk"><span aria-hidden="true">&times;</span></button>'.$str.'</div>';
				break;
		}
		return $return;
	}

	switch ($type) {
		case 'success':
			$return = '<div class="alert alert-success" role="alert">'.$str.'</div>';
			break;

		case 'info':
			$return = '<div class="alert alert-info" role="alert">'.$str.'</div>';
			break;

		case 'warning':
			$return = '<div class="alert alert-warning" role="alert">'.$str.'</div>';
			break;

		case 'danger':
			$return = '<div class="alert alert-danger" role="alert">'.$str.'</div>';
			break;
	}

	return $return;
}

function niceurl($str, $delimiter = '-') {
	$clean = iconv('UTF-8', 'ASCII//TRANSLIT', $str);
	$clean = explode('.', $clean)[0];
	$clean = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $clean);
	$clean = strtolower(trim($clean, '-'));
	$clean = preg_replace("/[\/_|+ -]+/", $delimiter, $clean);

	return $clean;
}

function check_user_abilities_min_accountant($return_error = false){
	if ($_SESSION['user_role'] == 'viewer') {
		if ($return_error) {
			display_404();
		}

		return false;
	}

	return true;
}

function check_user_abilities_min_admin($return_error = false){
	if ($_SESSION['user_role'] != 'superadmin' && $_SESSION['user_role'] != 'admin') {
		if ($return_error) {
			display_404();
		}

		return false;
	}

	return true;
}

function check_user_abilities_superadmin($return_error = false){
	if ($_SESSION['user_role'] != 'superadmin') {
		if ($return_error) {
			display_404();
		}

		return false;
	}

	return true;
}

function display_404(){
	global $global, $titles;
	header("HTTP/1.0 404 Not Found");
	require TEMPLATES_PATH.'/admin/header.php';

	?>
	<div class="row">
		<?php require TEMPLATES_PATH.'/admin/sidebar.php'; ?>
		<div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
			<div class="jumbotron">
				<h1>404 - Side ikke fundet</h1>
				<p><a href="<?php echo BASE_URL; ?>/admin"><?php echo $titles['admin/index.php']; ?></a></p>
			</div>
		</div>
	</div>
	<?php

	require TEMPLATES_PATH.'/admin/footer.php';
	exit;
}

function change_settings($next_invoice, $last_pull_date, $base_url, $base_path){
	global $db, $db_settings;
	if (empty($next_invoice) || empty($last_pull_date) || empty($base_url) || empty($base_path)) {
		$_SESSION['form_error'] = true;
		
		return message('Alle felter skal udfyldes.', 'danger');
	} else if (!is_numeric($next_invoice)) {
		$_SESSION['form_error'] = true;

		return message('"next_invoice" skal være et tal.', 'danger');
	}

	$next_invoice = intval($next_invoice);

	//Not nessecary to update and make a db call.
	if ($next_invoice == $db_settings['next_invoice'] && $last_pull_date == $db_settings['last_pull_date'] && $base_url == $db_settings['base_url'] && $base_path == $db_settings['base_path']) {
		return true;
	}

	if ($next_invoice != $db_settings['next_invoice']) {
		$sth = $db->prepare("UPDATE settings SET setting_value = :value WHERE setting_name = 'next_invoice' ");
		$sth->bindParam(':value', $next_invoice);
		$result = $sth->execute();
		if (!$result){
			return message('Noget gik galt.', 'danger');
		}
		$db_settings['next_invoice'] = $next_invoice;
	}

	if ($last_pull_date != $db_settings['last_pull_date']) {
		$sth = $db->prepare("UPDATE settings SET setting_value = :value WHERE setting_name = 'last_pull_date' ");
		$sth->bindParam(':value', $last_pull_date);
		$result = $sth->execute();
		if (!$result){
			return message('Noget gik galt.', 'danger');
		}
		$db_settings['last_pull_date'] = $last_pull_date;
	}

	if ($base_url != $db_settings['base_url']) {
		$sth = $db->prepare("UPDATE settings SET setting_value = :value WHERE setting_name = 'base_url' ");
		$sth->bindParam(':value', $base_url);
		$result = $sth->execute();
		if (!$result){
			return message('Noget gik galt.', 'danger');
		}
		$db_settings['base_url'] = $base_url;
	}

	if ($base_path != $db_settings['base_path']) {
		$sth = $db->prepare("UPDATE settings SET setting_value = :value WHERE setting_name = 'base_path' ");
		$sth->bindParam(':value', $base_path);
		$result = $sth->execute();
		if (!$result){
			return message('Noget gik galt.', 'danger');
		}
		$db_settings['base_path'] = $base_path;
	}

	return true;
}

function change_site_details($site_id, $name, $old_url, $new_url, $ck, $cs, $address, $postcode, $city, $company_name, $company_vat, $company_logo_url){
	global $db;

	if (empty($site_id)) {
		return message('Noget gik galt.', 'danger');
	}

	/*if(!strstr($new_url, 'http://') && !strstr($new_url, 'https://')){
		$url = 'http://'.$new_url;
	}
	if(!strstr($company_logo_url, 'http://') && !strstr($company_logo_url, 'https://')){
		$company_logo_url = 'http://'.$company_logo_url;
	}*/

	$new_url = rtrim($new_url, '/');
	$company_logo_url = rtrim($company_logo_url, '/');

	if ($old_url != $new_url) {
		$site = get_site_by_url($new_url);

		if (!empty($site) && $site !== false) {
			return message('En side med denne url eksisterer allerede. Du kan redigere den her: <a href="?site_id='.$site['id'].'&action=edit">'.$site['name'].'</a>.', 'danger');
		}
	}

	if ( empty($name) && empty($new_url) && empty($ck) && empty($address) && empty($postcode) && empty($city) && empty($company_name) && empty($company_vat) && empty($company_logo_url) ) {
		return true;
	}
	
	$sql = "UPDATE IGNORE sites SET ";
	if (!empty($name)) {
		$sql .= 'name = :name, ';
	}
	if (!empty($new_url)) {
		$sql .= 'url = :url, ';
	}
	if (!empty($ck)) {
		$sql .= 'consumer_key = :ck, ';
	}
	if (!empty($cs)) {
		$sql .= 'consumer_secret = :cs, ';
	}
	if (!empty($address)) {
		$sql .= 'address = :address, ';
	}
	if (!empty($postcode)) {
		$sql .= 'postcode = :postcode, ';
	}
	if (!empty($city)) {
		$sql .= 'city = :city, ';
	}
	if (!empty($company_name)) {
		$sql .= 'company_name = :company_name, ';
	}
	if (!empty($company_vat)) {
		$sql .= 'company_vat = :company_vat, ';
	}
	if (!empty($company_logo_url)) {
		$sql .= 'company_logo_url = :company_logo_url, ';
	}

	$sql = rtrim($sql, ' ,');

	$sql .= " WHERE id = :site_id";

	$sth = $db->prepare($sql);
	$sth->bindParam(':site_id', $site_id);

	if (!empty($name)) {
		$sth->bindParam(':name', $name);
	}
	if (!empty($new_url)) {
		$sth->bindParam(':url', $new_url);
	}
	if (!empty($ck)) {
		$sth->bindParam(':ck', $ck);
	}
	if (!empty($cs)) {
		$sth->bindParam(':cs', $cs);
	}
	if (!empty($address)) {
		$sth->bindParam(':address', $address);
	}
	if (!empty($postcode)) {
		$sth->bindParam(':postcode', $postcode);
	}
	if (!empty($city)) {
		$sth->bindParam(':city', $city);
	}
	if (!empty($company_name)) {
		$sth->bindParam(':company_name', $company_name);
	}
	if (!empty($company_vat)) {
		$sth->bindParam(':company_vat', $company_vat);
	}
	if (!empty($company_logo_url)) {
		$sth->bindParam(':company_logo_url', $company_logo_url);
	}

	$result = $sth->execute();

	if ($result) {
		return true;
	}

	return message('Noget gik galt.', 'danger');
}

function add_site($name, $url, $ck, $cs, $address, $postcode, $city, $company_name, $company_vat, $company_logo_url){
	global $db;
	if ( empty($name) || empty($url) || empty($ck) || empty($cs) || empty($address) || empty($postcode) || empty($city) || empty($company_name) ) {
		$_SESSION['form_error'] = true;
		
		return message('Alle felter, undtagen logo url & cvr. nr., skal udfyldes.', 'danger');
	}

	/*if(!strstr($url, 'http://') && !strstr($url, 'https://')){
		$url = 'http://'.$url;
	}*/

	$url = rtrim($url, '/');

	$site = get_site_by_url($url);

	if (!empty($site) && $site !== false) {
		return message('En side med denne url eksisterer allerede. Du kan redigere den her: <a class="alert-link" href="?site_id='.$site['id'].'&action=edit">'.$site['name'].'</a>', 'danger');
	}

	$sth = $db->prepare("INSERT IGNORE INTO sites SET name = :name, url = :url, consumer_key = :ck, consumer_secret = :cs, address = :address, postcode = :postcode, city = :city, company_name = :company_name, company_vat = :company_vat, company_logo_url = :company_logo_url");
	$sth->bindParam(':name', $name);
	$sth->bindParam(':url', $url);
	$sth->bindParam(':ck', $ck);
	$sth->bindParam(':cs', $cs);

	$sth->bindParam(':address', $address);
	$sth->bindParam(':postcode', $postcode);
	$sth->bindParam(':city', $city);
	$sth->bindParam(':company_name', $company_name);
	$sth->bindParam(':company_vat', $company_vat);
	$sth->bindParam(':company_logo_url', $company_logo_url);

	$result = $sth->execute();

	if ($result) {
		$_SESSION['sites_count'] = $_SESSION['sites_count'] + 1;
		return true;
	}

	return message('Noget gik galt.', 'danger');
}

function get_sites(){
	global $db;

	$sth = $db->prepare("SELECT * FROM sites");
	$sth->execute();
	$results = $sth->fetchAll(PDO::FETCH_ASSOC);

	$sites = [];

	foreach ($results as $result => $value) {
		$sites[$value['id']] = $value;
	}

	return $sites;
}

function get_site_by_id($id){
	global $db;

	$sth = $db->prepare("SELECT * FROM sites WHERE id = :site_id LIMIT 1");
	$sth->bindParam(':site_id', $id);
	$sth->execute();

	$result = $sth->execute();

	if ($result) {
		$result = $sth->fetch(PDO::FETCH_ASSOC);

		if ($result) {
			return $result;
		}

		return false;
	}

	return null;
}

function get_site_by_url($url){
	global $db;

	$sth = $db->prepare("SELECT * FROM sites WHERE url = :site_url LIMIT 1");
	$sth->bindParam(':site_url', $url);
	$sth->execute();

	$result = $sth->execute();

	if ($result) {
		$result = $sth->fetch(PDO::FETCH_ASSOC);

		if ($result) {
			return $result;
		}

		return false;
	}

	return null;
}

function delete_site_by_id($id){
	global $db;

	$sth = $db->prepare("DELETE FROM sites WHERE id = :site_id");
	$sth->bindParam(':site_id', $id);

	$result = $sth->execute();

	if ($result) {
		return true;
	}

	return false;
}

function update_setting($key, $value){
	global $db;

	$sth = $db->prepare("UPDATE `settings` SET `setting_value` = :value WHERE `setting_name` = :key");
	$sth->bindParam(':value', $value);
	$sth->bindParam(':key', $key);
	
	if ($sth->execute()) {
		return true;
	}

	return false;
}

function get_setting($setting){
	global $db;

	$sth = $db->prepare("SELECT * FROM `settings` WHERE `setting_name` = :name LIMIT 1");
	$sth->bindParam(':name', $setting);
	$sth->execute();
	$res = $sth->fetch(PDO::FETCH_ASSOC);
	return $res['setting_value'];
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

function get_orders_for_table(){
	global $db;
	$sth = $db->prepare("SELECT id, invoice_id, order_id, owner_site_id, owner_site_url, owner_site_name, billing_address FROM orders");
	$sth->execute();
	$results = $sth->fetchAll(PDO::FETCH_ASSOC);

	$results = json_decode(json_encode($results), 1);

	foreach ($results as $order => $value) {
		$value['billing_address'] = json_decode($value['billing_address'], 1);

		$results[$value['order_id'].'_'.$value['owner_site_id']] = $value;
		$results[$order] = [];
		unset($results[$order]);
	}

	return $results;
}

function add_order($products, $shippings, $fees, $discounts, $billing_details, $shipping_details, $currency, $status, $order_created_at, $order_updated_at, $order_completed_at, $payment_method, $is_paid, $note, $proforma_text){
	global $db, $db_settings;
	$next_invoice = get_setting('next_invoice');
	$internal_order_id = get_setting('next_internal_order_id');
	$url = $db_settings['base_url'];
	$site_name = 'Ulvemosens Handelsselskab ApS';
	$site_id = 0;

	$total = 0;
	$subtotal = 0;
	$total_tax = 0;
	$total_shipping = 0;
	$shipping_tax = 0;
	$cart_tax = 0;
	$total_discount = 0;
	$fee = 0;
	$fee_tax = 0;

	$shipping_methods = '';
	$line_items_subtotal = 0;
	$total_line_items = 0;

	foreach ($products as $key => $value) {
		$value['id'] = 0;
		$value['tax_class'] = 'DK';
		$value['total'] = $value['total']*0.8;
		$value['subtotal_tax'] = $value['subtotal']*1.25*0.2;
		$value['price'] = $value['qty_price'];
		$value['quantity'] = $value['qty'];
		$value['product_id'] = 0;
		$value['sku'] = '';
		$value['meta'] = [];

		$subtotal += $value['total'];
		$cart_tax += $value['total_tax'];
		$total_line_items += $value['quantity'];

		unset($value['qty']);
		unset($value['qty_price']);

		$products[$key] = $value;
	}

	foreach ($shippings as $key => $value) {
		$value['id'] = 0;
		$value['method_id'] = 'delivery';
		$value['method_title'] = $value['name'];
		$value['total'] = $value['subtotal'];

		$total_shipping += $value['subtotal'];
		$shipping_tax += $value['total_tax'];
		$shipping_methods .= $value['method_title'].', ';

		unset($value['total_tax']);
		unset($value['subtotal']);

		$shippings[$key] = $value;
	}

	foreach ($fees as $key => $value) {
		$value['id'] = 0;
		$value['title'] = $value['name'];
		$value['tax_class'] = 'DK';
		$value['total'] = $value['subtotal'];
		$value['total_tax'] = $value['total_tax'];

		$fee += $value['total'];
		$fee_tax += $value['total_tax'];

		unset($value['subtotal']);

		$fees[$key] = $value;
	}

	foreach ($discounts as $key => $value) {
		$value['id'] = 0;
		$value['code'] = $value['name'];

		$total_discount += $value['amount'];

		unset($value['name']);

		$discounts[$key] = $value;
	}

	$payment_details = json_encode([
		'method_id' => explode('_', $payment_method)[0],
		'method_title' => explode('_', $payment_method)[1],
		'paid' => ($is_paid == 'yes') ? true : false
	]);

	$cart_tax += $fee_tax;
	$total_tax = $cart_tax + $shipping_tax - ($total_discount*1.25*0.2);
	$total = $subtotal + $total_shipping + $fee + $total_tax - $total_discount;
	$line_items_subtotal = $subtotal + $cart_tax - ($total_discount*1.25) - $fee_tax;

	$tax_lines = json_encode([
		0 => [
			'id' => 0,
			'rate_id' => 1,
			'code' => 'DK-MOMS-1',
			'title' => 'Moms',
			'total' => $total_tax,
			'compound' => false
		]
	]);

	$shipping_total = (float)0;

	if ($total_shipping != 0) {
		$shipping_total = $total_shipping+$shipping_tax;
		$shipping_total = number_format($shipping_total, 2, ',', '');
	}

	$date = date("d-m-Y", strtotime(explode(' ', $order_completed_at)[0]));

	$_subtotal = number_format($line_items_subtotal, 2, ',', '');
	$_total = number_format($total, 2, ',', '');
	$_fee = number_format($fee+$fee_tax, 2, ',', '');

	$subtotal_csv = $date.';-'.$next_invoice.';0;"1010";"";"'.$site_name.' (ID: '.$internal_order_id.')";'.$_subtotal.';"DKK";100,00;"Salg";"";0;'.$date.';0,00;;"";"";0,00;0;"";0;"";"";"";"";"";0;0,00;"";"";"";"";"";0'."\n";
	$shipping_csv = $date.';-'.$next_invoice.';0;"1040";"";"'.$site_name.' (ID: '.$internal_order_id.')";'.$shipping_total.';"DKK";100,00;"";"";0;'.$date.';0,00;;"";"";0,00;0;"";0;"";"";"";"";"";0;0,00;"";"";"";"";"";0'."\n";
	$fee_csv = $date.';-'.$next_invoice.';0;"1610";"";"'.$site_name.' (ID: '.$internal_order_id.')";'.$_fee.';"DKK";100,00;"";"";0;'.$date.';0,00;;"";"";0,00;0;"";0;"";"";"";"";"";0;0,00;"";"";"";"";"";0'."\n";
	$total_csv = $date.';-'.$next_invoice.';0;"16200";"";"'.$site_name.' (ID: '.$internal_order_id.')";-'.$_total.';"DKK";100,00;"";"";0;'.$date.';0,00;;"";"";0,00;0;"";0;"";"";"";"";"";0;0,00;"";"";"";"";"";0'."\n";

	if (empty($subtotal) || $subtotal === 0) {
		$subtotal_csv = '';
	}
	if (empty($total_shipping) || $total_shipping === 0) {
		$shipping_csv = '';
	}
	if (empty($fee) || $fee === 0) {
		$fee_csv = '';
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
	$line_items = json_encode($products);
	$shipping_lines = json_encode($shippings);
	$shipping_methods = json_encode(rtrim($shipping_methods, ', '));
	$fee_lines = json_encode($fees);
	$coupon_lines = json_encode($discounts);
	$billing_address = json_encode($billing_details);
	$shipping_address = json_encode($shipping_details);

	$customer_ip = get_current_user_ip();
	$customer_id = $_SESSION['user_id'];
	$view_order_url = BASE_URL.'/admin/orders.php?id='.$next_invoice.'&action=view';

	$proforma_text = (empty($proforma_text)) ? '' : $proforma_text;

	$sth = $db->prepare("INSERT INTO orders (`invoice_id`, `owner_site_id`, `owner_site_url`, `owner_site_name`, `order_id`, `order_created_at`, `order_updated_at`, `order_completed_at`, `status`, `currency`, `total`, `subtotal`, `total_tax`, `total_shipping`, `shipping_tax`, `cart_tax`, `total_discount`, `shipping_methods`, `payment_details`, `billing_address`, `shipping_address`, `total_line_items_quantity`, `note`, `customer_ip`, `customer_id`, `view_order_url`, `line_items`, `shipping_lines`, `tax_lines`, `fee_lines`, `coupon_lines`, `export_csv`, `proforma_text`, `updated_at`, `created_at`) VALUES (:invoice_id, :owner_site_id, :owner_site_url, :owner_site_name,  :order_id, :order_created_at, :order_updated_at, :order_completed_at, :status, :currency, :total, :subtotal, :total_tax, :total_shipping, :shipping_tax, :cart_tax, :total_discount, :shipping_methods, :payment_details, :billing_address, :shipping_address, :total_line_items_quantity, :note, :customer_ip, :customer_id, :view_order_url, :line_items, :shipping_lines, :tax_lines, :fee_lines, :coupon_lines, :export_csv, :proforma_text, NOW(), NOW()) ON DUPLICATE KEY UPDATE `order_id` = `order_id`");

	$sth->bindParam(':invoice_id', $next_invoice);
	$sth->bindParam(':owner_site_id', $site_id);
	$sth->bindParam(':owner_site_url', $url);
	$sth->bindParam(':owner_site_name', $site_name);
	$sth->bindParam(':order_id', $internal_order_id);
	$sth->bindParam(':order_created_at', $order_created_at);
	$sth->bindParam(':order_updated_at', $order_updated_at);
	$sth->bindParam(':order_completed_at', $order_completed_at);
	$sth->bindParam(':status', $status);
	$sth->bindParam(':currency', $currency);
	$sth->bindParam(':total', $total);
	$sth->bindParam(':subtotal', $subtotal);
	$sth->bindParam(':total_tax', $total_tax);
	$sth->bindParam(':total_shipping', $total_shipping);
	$sth->bindParam(':shipping_tax', $shipping_tax);
	$sth->bindParam(':cart_tax', $cart_tax);
	$sth->bindParam(':total_discount', $total_discount);
	$sth->bindParam(':shipping_methods', $shipping_methods);
	$sth->bindParam(':payment_details', $payment_details);
	$sth->bindParam(':billing_address', $billing_address);
	$sth->bindParam(':shipping_address', $shipping_address);
	$sth->bindParam(':total_line_items_quantity', $total_line_items);
	$sth->bindParam(':note', $note);
	$sth->bindParam(':customer_ip', $customer_ip);
	$sth->bindParam(':customer_id', $customer_id);
	$sth->bindParam(':view_order_url', $view_order_url);
	$sth->bindParam(':line_items', $line_items);
	$sth->bindParam(':shipping_lines', $shipping_lines);
	$sth->bindParam(':tax_lines', $tax_lines);
	$sth->bindParam(':fee_lines', $fee_lines);
	$sth->bindParam(':coupon_lines', $coupon_lines);
	$sth->bindParam(':export_csv', $export_csv);
	$sth->bindParam(':proforma_text', $proforma_text);

	$res = $sth->execute();

	if ($res) {
		if ($status != 'proforma') {
			
			$sth = $db->prepare("INSERT INTO invoices SET invoice_id = :invoice_id, owner_site_id = :owner_site_id, owner_site_url = :owner_site_url, owner_site_name = :owner_site_name, order_id = :order_id, created_at = NOW()");
			$sth->bindParam(':invoice_id', $next_invoice);
			$sth->bindParam(':owner_site_id', $site_id);
			$sth->bindParam(':owner_site_url', $url);
			$sth->bindParam(':owner_site_name', $site_name);
			$sth->bindParam(':order_id', $internal_order_id);

			$res = $sth->execute();

			update_setting('next_internal_order_id', $internal_order_id+1);
			update_setting('next_invoice', $next_invoice+1);

			if ($res) {
				$_SESSION['invoices_count'] = $_SESSION['invoices_count'] + 1;
				return true;
			}

			return false;
		}
		$_SESSION['orders_count'] = $_SESSION['orders_count'] + 1;
		update_setting('next_internal_order_id', $internal_order_id+1);

		return true;
	}

	return false;
}

function get_order_by_id($id){
	global $db;

	$sth = $db->prepare("SELECT * FROM orders WHERE id = :id LIMIT 1");
	$sth->bindParam(':id', $id);
	$sth->execute();

	$result = $sth->execute();

	if ($result) {
		$result = $sth->fetch(PDO::FETCH_ASSOC);

		if ($result) {
			return $result;
		}

		return false;
	}

	return null;
}

function get_order_by_invoice_id($id){
	global $db;

	$sth = $db->prepare("SELECT * FROM orders WHERE invoice_id = :invoice_id LIMIT 1");
	$sth->bindParam(':invoice_id', $id);
	$sth->execute();

	$result = $sth->execute();

	if ($result) {
		$result = $sth->fetch(PDO::FETCH_ASSOC);

		if ($result) {
			return $result;
		}

		return false;
	}

	return null;
}

function get_invoice_by_id($id){
	global $db;

	$sth = $db->prepare("SELECT * FROM invoices WHERE id = :id LIMIT 1");
	$sth->bindParam(':id', $id);
	$sth->execute();

	$result = $sth->execute();

	if ($result) {
		$result = $sth->fetch(PDO::FETCH_ASSOC);

		if ($result) {
			return $result;
		}

		return false;
	}

	return null;
}

function get_invoice_by_invoice_id($id){
	global $db;

	$sth = $db->prepare("SELECT * FROM invoices WHERE invoice_id = :invoice_id LIMIT 1");
	$sth->bindParam(':invoice_id', $id);
	$sth->execute();

	$result = $sth->execute();

	if ($result) {
		$result = $sth->fetch(PDO::FETCH_ASSOC);

		if ($result) {
			return $result;
		}

		return false;
	}

	return null;
}

function download_csv_orders_by_ids($orders, $export_bulk = false){
	if (is_null($orders) || empty($orders) ) {
		return false;
	}
	global $db, $global;

	$csv = '';
	$status = 'proforma';

	if ($export_bulk) {
		$sql = "SELECT export_csv FROM orders WHERE id >= :min_id AND id <= :max_id AND status != :status";
		$min_id = explode('-', $orders)[0];
		$max_id = explode('-', $orders)[1];

		$sth = $db->prepare($sql);
		$sth->bindParam(':min_id', $min_id);
		$sth->bindParam(':max_id', $max_id);
		$sth->bindParam(':status', $status);
		$sth->execute();

		$result = $sth->fetchAll(PDO::FETCH_ASSOC);

		foreach ($result as $order => $value) {
			if (!is_null($order)) {
				$csv .= json_encode($value)."\s";
			} else {
				header('Location: '.BASE_URL.'/'.$global['current_url']);
				exit;
			}
		}
	} else {
		$sql = "SELECT export_csv FROM orders WHERE id = :id LIMIT 1";

		foreach ($orders as $order => $value) {
			if ($value == 'on') {
				$id = explode('_', $order)[1];
				$sth = $db->prepare($sql);
				$sth->bindParam(':id', $id);

				$sth->execute();

				$result = $sth->fetch(PDO::FETCH_ASSOC);

				if ($result) {
					$csv .= json_encode($result)."\s";
				} else {
					header('Location: '.BASE_URL.'/'.$global['current_url']);
					exit;
				}
			}
		}
	}

	header('Content-type: text/csv');
	header('Content-disposition: attachment; filename=CSV Export '.date('Y-m-d H:i:s').'.csv');
	header('Pragma: no-cache');
	header('Expires: 0');

	$csvs = explode("\s", $csv);

	foreach ($csvs as $csv => $value) {
		$csv = json_decode($value, true);

		$joins = array_filter(explode("\n", json_decode($csv['export_csv'], true)['joined']));

		foreach ($joins as $join) {
			if (!is_null($join) && !empty($join)) {
				echo $join."\n";
			}
		}
	}

	exit;
}

function download_pdf_orders_by_ids($orders, $export_bulk = false){
	if (is_null($orders) || empty($orders) ) {
		return false;
	}
	global $db, $global;

	$sql = "SELECT * FROM orders WHERE id = :id LIMIT 1";
	$sql_2 = "SELECT * FROM sites WHERE id = :owner_site_id LIMIT 1";

	$invoices = '';
	$status = 'proforma';

	if ($export_bulk) {
		$sql = "SELECT * FROM orders WHERE id >= :min_id AND id <= :max_id AND status != :status";
		$min_id = explode('-', $orders)[0];
		$max_id = explode('-', $orders)[1];

		$sth = $db->prepare($sql);
		$sth->bindParam(':min_id', $min_id);
		$sth->bindParam(':max_id', $max_id);
		$sth->bindParam(':status', $status);
		$res = $sth->execute();

		$results = $sth->fetchAll(PDO::FETCH_ASSOC);

		if ($res) {
			foreach ($results as $result => $value) {
				$id = $value['id'];

				$sth = $db->prepare($sql_2);
				$sth->bindParam(':owner_site_id', $value['owner_site_id']);

				$sth->execute();

				$result_2 = $sth->fetch(PDO::FETCH_ASSOC);

				$invoices['invoice_'.$id] = $value;
				$invoices['invoice_'.$id]['site_address'] = $result_2['address'];
				$invoices['invoice_'.$id]['site_postcode'] = $result_2['postcode'];
				$invoices['invoice_'.$id]['site_city'] = $result_2['city'];
				$invoices['invoice_'.$id]['site_company_name'] = $result_2['company_name'];
				$invoices['invoice_'.$id]['site_company_vat'] = $result_2['company_vat'];
				$invoices['invoice_'.$id]['site_company_logo_url'] = $result_2['company_logo_url'];

				$invoices['invoice_'.$id]['shipping_methods'] = json_decode($value['shipping_methods'], 1);
				$invoices['invoice_'.$id]['payment_details'] = json_decode($value['payment_details'], 1);
				$invoices['invoice_'.$id]['billing_address'] = json_decode($value['billing_address'], 1);
				$invoices['invoice_'.$id]['shipping_address'] = json_decode($value['shipping_address'], 1);
				$invoices['invoice_'.$id]['line_items'] = json_decode($value['line_items'], 1);
				$invoices['invoice_'.$id]['shipping_lines'] = json_decode($value['shipping_lines'], 1);
				$invoices['invoice_'.$id]['tax_lines'] = json_decode($value['tax_lines'], 1);
				$invoices['invoice_'.$id]['fee_lines'] = json_decode($value['fee_lines'], 1);
				$invoices['invoice_'.$id]['coupon_lines'] = json_decode($value['coupon_lines'], 1);
				$invoices['invoice_'.$id]['proforma_text'] = $result['proforma_text'];

				unset($result_2);
			}
		} else {
			header('Location: '.BASE_URL.'/'.$global['current_url']);
			exit;
		}
	} else {
		foreach ($orders as $order => $value) {
			if ($value == 'on') {
				$id = explode('_', $order)[1];
				$sth = $db->prepare($sql);
				$sth->bindParam(':id', $id);

				$sth->execute();

				$result = $sth->fetch(PDO::FETCH_ASSOC);

				if ($result) {
					$sth = $db->prepare($sql_2);
					$sth->bindParam(':owner_site_id', $result['owner_site_id']);

					$sth->execute();

					$result_2 = $sth->fetch(PDO::FETCH_ASSOC);

					$invoices['invoice_'.$id] = $result;
					$invoices['invoice_'.$id]['site_address'] = $result_2['address'];
					$invoices['invoice_'.$id]['site_postcode'] = $result_2['postcode'];
					$invoices['invoice_'.$id]['site_city'] = $result_2['city'];
					$invoices['invoice_'.$id]['site_company_name'] = $result_2['company_name'];
					$invoices['invoice_'.$id]['site_company_vat'] = $result_2['company_vat'];
					$invoices['invoice_'.$id]['site_company_logo_url'] = $result_2['company_logo_url'];

					$invoices['invoice_'.$id]['shipping_methods'] = json_decode($result['shipping_methods'], 1);
					$invoices['invoice_'.$id]['payment_details'] = json_decode($result['payment_details'], 1);
					$invoices['invoice_'.$id]['billing_address'] = json_decode($result['billing_address'], 1);
					$invoices['invoice_'.$id]['shipping_address'] = json_decode($result['shipping_address'], 1);
					$invoices['invoice_'.$id]['line_items'] = json_decode($result['line_items'], 1);
					$invoices['invoice_'.$id]['shipping_lines'] = json_decode($result['shipping_lines'], 1);
					$invoices['invoice_'.$id]['tax_lines'] = json_decode($result['tax_lines'], 1);
					$invoices['invoice_'.$id]['fee_lines'] = json_decode($result['fee_lines'], 1);
					$invoices['invoice_'.$id]['coupon_lines'] = json_decode($result['coupon_lines'], 1);
					$invoices['invoice_'.$id]['proforma_text'] = $result['proforma_text'];

					unset($result_2);
				} else {
					header('Location: '.BASE_URL.'/'.$global['current_url']);
					exit;
				}
			}
		}
	}

	require BASE_PATH.'/lib/tcpdf.php';

	$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

	$pdf->SetCreator(PDF_CREATOR);
	$pdf->SetAuthor('Ulvemosens Handelsselskab ApS');
	$pdf->SetTitle('Fakturaer - Ulvemosens Handelsselskab ApS');
	$pdf->SetSubject('Fakturaer - Ulvemosens Handelsselskab ApS');
	$pdf->SetKeywords('Fakturaer, Ulvemosens Handelsselskab ApS');
	$pdf->SetPrintHeader(false);
	$pdf->SetPrintFooter(false);
	$pdf->SetMargins(PDF_MARGIN_LEFT, 10, PDF_MARGIN_RIGHT);
	$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
	$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
	$pdf->setFontSubsetting(true);
	$pdf->SetFont('helvetica', '', 14, '', true);
	$pdf->setCellPaddings(1, 1, 1, 1);
	$pdf->setCellMargins(1, 1, 1, 1);
	$pdf->SetFillColor(255, 255, 255);
	$pdf->SetLineWidth(0.7);

	$pdf->AddPage();

	$html = '';

	foreach ($invoices as $invoice => $value) {
		$value['owner_site_name'] = (empty($value['owner_site_name'])) ? 'Ulvemosens Handelsselskab ApS' : $value['owner_site_name'];
		$value['site_address'] = (empty($value['site_address'])) ? 'Ulvemosevej 12' : $value['site_address'];
		$value['site_postcode'] = (empty($value['site_postcode'])) ? '6800' : $value['site_postcode'];
		$value['site_city'] = (empty($value['site_city'])) ? 'Varde' : $value['site_city'];
		$value['site_company_name'] = (empty($value['site_company_name'])) ? 'Ulvemosens Handelsselskab ApS' : $value['site_company_name'];
		$value['site_company_vat'] = (empty($value['site_company_vat'])) ? '36891475' : $value['site_company_vat'];
		$html .= 'split';
		$html .= 'Sælger:<br>';
		$html .= '<strong>'.$value['owner_site_name'].'</strong><br>';
		$html .= $value['site_address'].'<br>';
		$html .= 'DK-'.$value['site_postcode'].' '.$value['site_city'].'<br>';
		$html .= $value['site_company_name'].'<br>';
		$html .= 'CVR: '.$value['site_company_vat'].'<br><br>';
		$html .= 'Køber:<br>';
		if (!empty($value['billing_address']['company'])) {
			$html .= '<strong>'.$value['billing_address']['company'].'</strong><br>';
		}
		$html .= $value['billing_address']['first_name'].' '.$value['billing_address']['last_name'].'<br>';
		$html .= $value['billing_address']['address_1'].'<br>';
		$html .= $value['billing_address']['postcode'].' '.$value['billing_address']['city'].'<br>';
		if ($value['billing_address']['country'] == 'DK') {
			$html .= 'Danmark';
		} else {
			$html .= $value['billing_address']['country'];
		}
		$html .= 'break';
		$html .= '<br><strong>Faktura</strong><br>';
		$html .= 'Fakturadato<br>';
		$html .= 'Ordrebeløb ('.$value['currency'].')<br>';
		$html .= 'break';
		$html .= '<strong>'.$value['invoice_id'].'</strong><br>';
		$html .= explode(' ', $value['order_created_at'])[0].'<br>';
		$html .= number_format($value['total'], 2, ',', '.');
		$html .= 'break';
		$html .= '
		<table>
			<thead>
				<tr>
					<th width="31%" style="border-bottom: 1px solid grey;"><strong>Navn</strong></th>
					<th width="23%" align="right" style="border-bottom: 1px solid grey;"><strong>Pris</strong></th>
					<th width="23%" align="right" style="border-bottom: 1px solid grey;"><strong>Mængde</strong></th>
					<th width="23%" align="right" style="border-bottom: 1px solid grey;"><strong>Linje total</strong></th>
				</tr>
				<tr>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
				</tr>
			</thead><tbody>';
			if(!empty($value['line_items']) && $value['line_items'] != '') {
				foreach($value['line_items'] as $item) {
					$html .= '<tr><td style="border-bottom: 1px solid rgb(240,240,240);" width="31%">'.$item['name'];
						if(!empty($item['meta']) && $item['meta'] != '') {
							foreach($item['meta'] as $variation){
								$html .= '<br><span style="color: #e2e2e2;"> - '.$variation['label'].': '.$variation['value'].'</span>';
							}
						}
						$html .= '</td>
						<td style="border-bottom: 1px solid rgb(240,240,240);" width="23%" align="right">'.number_format(($item['total']+$item['total_tax'])/$item['quantity'], 2, ',', '.').'</td>
						<td style="border-bottom: 1px solid rgb(240,240,240);" width="23%" align="right">'.$item['quantity'].'</td>
						<td style="border-bottom: 1px solid rgb(240,240,240);" width="23%" align="right">'.number_format($item['total']+$item['total_tax'], 2, ',', '.').'</td>
					</tr>';
				}
			}
		$html .= '<tr>
					<td style="border-bottom: 2px solid grey;"></td>
					<td style="border-bottom: 2px solid grey;"></td>
					<td style="border-bottom: 2px solid grey;"></td>
					<td style="border-bottom: 2px solid grey;"></td>
				</tr>
				</tbody></table>';

		$fee = 0; $fee_tax = 0;

		if (!empty($value['fee_lines']) && $value['fee_lines'] != '') {
			foreach ($value['fee_lines'] as $line) {
				$fee += $line['total'];
				$fee_tax += $line['total_tax'];
			}
		}

		$subtotal = $value['subtotal']-$value['total_discount'];

		$total_fee = $fee+$fee_tax;

		$difference = round($value['total'] - ($subtotal + $value['total_shipping'] + $fee + $value['total_tax']), 2, PHP_ROUND_HALF_UP);

		if ($subtotal != 0 && $difference != 0 && $difference != -0 && $difference != '0' && $difference != '-0') {
			$subtotal = $subtotal + $difference;
		}

		$html .= 'break';
		$html .= '<table>
			<tr>
				<td>Subtotal</td>
				<td align="right">'.number_format($value['subtotal']+$value['total_tax']-($fee_tax+$value['shipping_tax']), 2, ',', '.').'</td>
			</tr>
			<tr>
				<td>Rabat</td>
				<td align="right">'.number_format($value['total_discount'], 2, ',', '.').'</td>
			</tr>
			<tr>
				<td>Fragt</td>
				<td align="right">'.number_format($value['total_shipping']+$value['shipping_tax'], 2, ',', '.').'</td>
			</tr>
			<tr>
				<td>Gebyr</td>
				<td align="right">'.number_format($total_fee, 2, ',', '.').'</td>
			</tr>
			<tr><td></td><td></td></tr>
			<tr>
				<td><strong>Total</strong></td>
				<td align="right"><strong>'.number_format($value['total'], 2, ',', '.').'</strong></td>
			</tr>
			<tr><td></td><td></td></tr>
			<tr>
				<td>Moms (25 %)</td>
				<td align="right">'.number_format($value['total_tax'], 2, ',', '.').'</td>
			</tr>
			<tr>
				<td>Total ekskl. moms</td>
				<td align="right">'.number_format($value['total']-$value['total_tax'], 2, ',', '.').'</td>
			</tr>
		</table>';
		$html .= 'break';
		if (!empty($value['proforma_text'])) {
			$html .= '<p>'.nl2br($value['proforma_text']).'</p>';
		}
		$html .= 'break';
		$html .= $value['site_company_name'].'<br>';
		$html .= 'CVR: '.$value['site_company_vat'].'<br><br>';
		$html .= $value['site_address'].'<br>';
		$html .= 'DK-'.$value['site_postcode'].' '.$value['site_city'].'<br><br>';
		$html .= 'Alle priser i '.$value['currency'];
	}

	$delimiter = 'split';
	$chunks    = explode($delimiter, $html);
	$cnt       = count($chunks);

	for ($i = 1; $i < $cnt; $i++) {
		$y = $pdf->getY();

		$pdf->SetFont('helvetica', '', 10, '', true);
		$pdf->SetTextColor(0, 0, 0, 95); 
		$sections = explode('break', $chunks[$i]);

		$sale_and_buyer = $sections[0];
		$details = $sections[1];
		$details_2 = $sections[2];
		$line_items = $sections[3];
		$totals = $sections[4];
		$proforma_text = $sections[5];
		$footer = $sections[6];

		$pdf->writeHTMLCell('', 100, '', $y, $sale_and_buyer, 0, 0, 0, true, 'L', true);
		$pdf->MultiCell('', '', $details, 0, 'L', false, 1, 110, 50, true, 0, true, true, 0, 'B', false);
		$pdf->MultiCell('', '', $details_2, 0, 'R', false, 1, 110, 50, true, 0, true, true, 0, 'B', false);

		$pdf->Ln(5);

		$pdf->SetFont('helvetica', '', 12, '', true);
		$pdf->SetTextColor(0, 0, 0, 100);

		$y = $pdf->getY();
		$x = $pdf->getX();

		$pdf->MultiCell('', '', $line_items, 0, 'L', false, 1, $x, $y, true, 0, true, true, 0, 'T', false);
		$pdf->Ln(3);

		$y = $pdf->getY();

		$pdf->MultiCell('', '', $totals, 0, 'L', false, 1, 110, $y, true, 0, true, true, 0, 'T', false);
		if(!empty($proforma_text)) {
			$pdf->MultiCell(90, '', $proforma_text, 0, 'L', false, 1, 15, $y, true, 0, true, true, 0, 'T', false);
			$pdf->Ln(20);
		}

		$pdf->Ln(3);

		$y = $pdf->getY();
		$pdf->SetFont('helvetica', '', 10, '', true);

		$linestyle = array('width' => 0.1, 'cap' => 'butt', 'join' => 'miter', 'dash' => '', 'phase' => 0, 'color' => array(0, 0, 0));

		$pdf->Line($x, $y+4, 195, $y+4, $linestyle);

		$pdf->MultiCell('', '', $footer, 0, 'C', false, 1, $x, $y+4, true, 0, true, true, 0, 'T', false);

		if ($i < $cnt - 1) {
			$pdf->AddPage();
		}
	}

	$pdf->lastPage();

	$pdf->Output('fakturaer.pdf', 'I');

	exit;
}

function make_reports($min_date, $max_date, $sorting_method = 'day', $return_orders_count = false){
	$orders = sort_orders($min_date, $max_date, $sorting_method);

	$days_between = (int)ceil(abs(strtotime($max_date) - strtotime($min_date)) / 86400);
	$months_between = (int)ceil(abs(strtotime($max_date) - strtotime($min_date)) / 2629743.83 );
	$years_between = (int)ceil(abs(strtotime($max_date) - strtotime($min_date)) / 31556926 );

	$sort_orders = array();

	$count = 0;

	foreach ($orders as $key => $order_arays) {
		$sort_orders[$key]['total'] = 0;
		$sort_orders[$key]['subtotal'] = 0;
		$sort_orders[$key]['total_tax'] = 0;
		$sort_orders[$key]['total_shipping'] = 0;
		$sort_orders[$key]['shipping_tax'] = 0;
		$sort_orders[$key]['total_discount'] = 0;
		$sort_orders[$key]['fee'] = 0;

		$count += count($order_arays);
	}

	if ($return_orders_count) {
		return $count;
	}

	foreach ($orders as $key => $order_arays) {
		foreach ($order_arays as $order => $value) {
			$sort_orders[$key]['total'] += $value['total'];
			$sort_orders[$key]['subtotal'] += $value['subtotal'];
			$sort_orders[$key]['total_tax'] += $value['total_tax'];
			$sort_orders[$key]['total_shipping'] += $value['total_shipping'];
			$sort_orders[$key]['shipping_tax'] += $value['shipping_tax'];
			$sort_orders[$key]['total_discount'] += $value['total_discount'];
			if (isset($value['fee_lines'][0])) {
				$sort_orders[$key]['fee'] += $value['fee_lines'][0]['total']+$value['fee_lines'][0]['total_tax'];
			}
		}
	}

	if ($sorting_method == 'day') {
		$last = 'year_'.explode('-', $max_date)[0].'-'.'month_'.explode('-', $max_date)[1].'-'.'day_'.explode('-', $max_date)[2];
		$first = 'year_'.explode('-', $min_date)[0].'-'.'month_'.explode('-', $min_date)[1].'-'.'day_'.explode('-', $min_date)[2];

		if (count($sort_orders) < $days_between) {
			$day = number_format(explode('_', explode('-', $first)[2])[1]);
			$month = number_format(explode('_', explode('-', $first)[1])[1]);
			$year = explode('_', explode('-', $first)[0])[1];

			$last_day = number_format(explode('_', explode('-', $last)[2])[1]);
			$last_month = number_format(explode('_', explode('-', $last)[1])[1]);
			$last_year = explode('_', explode('-', $last)[0])[1];

			$_month = $month;

			for ($i=0; $i < $days_between; $i++) { 
				$_day = $day + $i;

				if ($_month == 1) {
					if ($_day > 31) {
						$_month = $month + 1;
						$_day = ($_day % 31);
					}
				} else if ($_month == 2) {
					# Check for leap years
					if ($year % 4) {
						if ($_day > 29) {
							$_month = $month + 1;
							$_day = ($_day % 29);
						}
					} else {
						if ($_day > 28) {
							$_month = $month + 1;
							$_day = ($_day % 28);
						}
					}
				} else if ($_month == 3) {
					if ($_day > 31) {
						$_month = $month + 1;
						$_day = ($_day % 31);
					}
				} else if ($_month == 4) {
					if ($_day > 30) {
						$_month = $month + 1;
						$_day = ($_day % 30);
					}
				} else if ($_month == 5) {
					if ($_day > 31) {
						$_month = $month + 1;
						$_day = ($_day % 31);
					}
				} else if ($_month == 6) {
					if ($_day > 30) {
						$_month = $month + 1;
						$_day = ($_day % 30);
					}
				} else if ($_month == 7) {
					if ($_day > 31) {
						$_month = $month + 1;
						$_day = ($_day % 31);
					}
				} else if ($_month == 8) {
					if ($_day > 31) {
						$_month = $month + 1;
						$_day = ($_day % 31);
					}
				} else if ($_month == 9) {
					if ($_day > 30) {
						$_month = $month + 1;
						$_day = ($_day % 30);
					}
				} else if ($_month == 10) {
					if ($_day > 31) {
						$_month = $month + 1;
						$_day = ($_day % 31);
					}
				} else if ($_month == 11) {
					if ($_day > 30) {
						$_month = $month + 1;
						$_day = ($_day % 30);
					}
				} else if ($_month == 12) {
					if ($_day > 31) {
						$year = $year + 1;
						$_month = 1;
						$_day = ($_day % 31);
					}
				}

				if ($_day < 10) {
					$day_lz = '0'.$_day;
				} else {
					$day_lz = $_day;
				}

				if ($_month < 10) {
					$month_lz = '0'.$_month;
				} else {
					$month_lz = $_month;
				}

				$key = 'year_'.$year.'-month_'.$month_lz.'-day_'.$day_lz;

				if (!array_key_exists($key, $sort_orders)) {
					$sort_orders[$key]['total'] = 0;
					$sort_orders[$key]['subtotal'] = 0;
					$sort_orders[$key]['total_tax'] = 0;
					$sort_orders[$key]['total_shipping'] = 0;
					$sort_orders[$key]['shipping_tax'] = 0;
					$sort_orders[$key]['total_discount'] = 0;
					$sort_orders[$key]['fee'] = 0;
				}
			}
		}
	} else if ($sorting_method == 'month') {
		$last = 'year_'.explode('-', $max_date)[0].'-'.'month_'.explode('-', $max_date)[1];
		$first = 'year_'.explode('-', $min_date)[0].'-'.'month_'.explode('-', $min_date)[1];

		if (count($sort_orders) < $days_between) {
			$month = number_format(explode('_', explode('-', $first)[1])[1]);
			$year = explode('_', explode('-', $first)[0])[1];

			$last_month = number_format(explode('_', explode('-', $last)[1])[1]);
			$last_year = explode('_', explode('-', $last)[0])[1];

			$_year = $year;

			for ($i=0; $i < $months_between; $i++) { 
				$_month = $month + $i;

				if ($_month > 12) {
					$_year = $year + 1;
					$_month = $_month % 12;
				}

				if ($_month < 10) {
					$month_lz = '0'.$_month;
				} else {
					$month_lz = $_month;
				}

				$key = 'year_'.$_year.'-month_'.$month_lz;

				if (!array_key_exists($key, $sort_orders)) {
					$sort_orders[$key]['total'] = 0;
					$sort_orders[$key]['subtotal'] = 0;
					$sort_orders[$key]['total_tax'] = 0;
					$sort_orders[$key]['total_shipping'] = 0;
					$sort_orders[$key]['shipping_tax'] = 0;
					$sort_orders[$key]['total_discount'] = 0;
					$sort_orders[$key]['fee'] = 0;
				}
			}
		}
	} else if ($sorting_method == 'year') {
		$last = 'year_'.explode('-', $max_date)[0];
		$first = 'year_'.explode('-', $min_date)[0];

		if (count($sort_orders) < $days_between) {
			$year = explode('_', explode('-', $first)[0])[1];
			$last_year = explode('_', explode('-', $last)[0])[1];

			for ($i=0; $i < $years_between; $i++) { 
				$_year = $year + $i;

				$key = 'year_'.$_year;

				if (!array_key_exists($key, $sort_orders)) {
					$sort_orders[$key]['total'] = 0;
					$sort_orders[$key]['subtotal'] = 0;
					$sort_orders[$key]['total_tax'] = 0;
					$sort_orders[$key]['total_shipping'] = 0;
					$sort_orders[$key]['shipping_tax'] = 0;
					$sort_orders[$key]['total_discount'] = 0;
					$sort_orders[$key]['fee'] = 0;
				}
			}
		}
	}
 	ksort($sort_orders);

	return $sort_orders;
}

function find_label($reports, $sorting_method = 'day'){
	$return = '';

	if ($sorting_method == 'day') {
		foreach($reports as $key => $data){
			$return .= "'".explode("_", explode("-", $key)[2])[1]."/".explode("_", explode("-", $key)[1])[1]."-".explode("_", explode("-", $key)[0])[1]."'".',';
		}
	} else if ($sorting_method == 'month') {
		foreach($reports as $key => $data){
			$return .= "'".explode("_", explode("-", $key)[1])[1].'-'.explode("_", explode("-", $key)[0])[1]."'".',';
		}
	} else if ($sorting_method == 'year') {
		foreach($reports as $key => $data){
			$return .= "'".explode("_", explode("-", $key)[0])[1]."'".',';
		}
	} else if ($sorting_method == 'site') {
		foreach($reports as $key => $data){
			$site_id = explode("_", $key)[1];
			$site = get_site_by_id($site_id);

			$return .= "'".$site['name']."'".',';
		}
	}

	return $return;
}

function sort_orders($min_date, $max_date, $sorting_method = 'day'){
	global $db;

	$sth = $db->prepare("SELECT order_id, owner_site_id, order_created_at, currency, total, subtotal, total_tax, total_shipping, shipping_tax, total_discount, fee_lines FROM orders WHERE order_created_at >= :min_date AND order_created_at < :max_date");
	$sth->bindParam(':min_date', $min_date);
	$sth->bindParam(':max_date', $max_date);
	$sth->execute();
	$results = $sth->fetchAll(PDO::FETCH_ASSOC);

	$results = json_decode(json_encode($results), 1);

	$orders_by_day = array();
	$orders_by_month = array();
	$orders_by_year = array();

	foreach ($results as $order => $value) {
		$value['fee_lines'] = json_decode($value['fee_lines'], 1);

		$results[$value['order_id'].'_'.$value['owner_site_id']] = $value;
		$results[$order] = [];
		unset($results[$order]);
	}

	if ($sorting_method == 'day') {
		return sort_orders_by_day($results);
	} elseif ($sorting_method == 'month') {
		return sort_orders_by_month($results);
	} elseif ($sorting_method == 'year') {
		return sort_orders_by_year($results);
	} elseif ($sorting_method == 'site') {
		return sort_orders_by_site($results);
	} else {
		return $results;
	}
}

function sort_orders_by_site($orders){
	$orders_by_site = array();

	foreach ($orders as $order => $value) {
		$orders_by_site['site_'.$value['owner_site_id']]['order_'.$value['order_id']] = $value;
	}

	return $orders_by_site;
}

function sort_orders_by_day($orders){
	$orders_by_day = array();

	foreach ($orders as $order => $value) {
		$ymd = explode('-', explode(' ', $value['order_created_at'])[0]);
		$orders_by_day['year_'.$ymd[0].'-month_'.$ymd[1].'-day_'.$ymd[2]]['site_'.$value['owner_site_id'].'-order_'.$value['order_id']] = $value;
	}

	return $orders_by_day;
}

function sort_orders_by_month($orders){
	$orders_by_month = array();

	foreach ($orders as $order => $value) {
		$ymd = explode('-', explode(' ', $value['order_created_at'])[0]);
		$orders_by_month['year_'.$ymd[0].'-month_'.$ymd[1]]['site_'.$value['owner_site_id'].'-order_'.$value['order_id']] = $value;
	}

	return $orders_by_month;
}

function sort_orders_by_year($orders){
	$orders_by_year = array();

	foreach ($orders as $order => $value) {
		$ymd = explode('-', explode(' ', $value['order_created_at'])[0]);
		$orders_by_year['year_'.$ymd[0]]['site_'.$value['owner_site_id'].'-order_'.$value['order_id']] = $value;
	}

	return $orders_by_year;
}

function array_assoc_reverse(array $arr){
	return array_combine( array_reverse(array_keys( $arr )), array_reverse( array_values( $arr ) ) );
} 

function closestDate($day){
	$day = ucfirst($day);
	if(date('l', time()) == $day)
		return date("Y-m-d", time());
	else if(abs(time()-strtotime('next '.$day)) < abs(time()-strtotime('last '.$day)))
		return date("Y-m-d", strtotime('next '.$day));
	else
		return date("Y-m-d", strtotime('last '.$day));
}

function pred($arr){
	echo '<pre>';
	var_dump($arr);
	echo '</pre>';
}