<?php

require '../app/db.php';
require '../app/init.php';


if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
	header('Location: '.BASE_URL);
	exit;
}

if (isset($_POST['pull_orders']) && check_user_abilities_min_admin()) {
	$min_date = explode('-', $_POST['min_date']);
	$min_time = explode(':', $_POST['min_time']);

	$max_date = explode('-', $_POST['max_date']);
	$max_time = explode(':', $_POST['max_time']);

	$orders = WCApiAddOrdersAndInvoices(get_sites(), get_orders(), ['y'=>$min_date[0],'m'=>$min_date[1],'d'=>$min_date[2],'h'=>$min_time[0],'i'=>$min_time[1],'s'=>0], ['y'=>$max_date[0],'m'=>$max_date[1],'d'=>$max_date[2],'h'=>$max_time[0],'i'=>$max_time[1],'s'=>0], $_POST['limit']);

	if (!is_string($orders)) {
		if ( explode('|', json_encode($orders))[0] == 'false' ) {
			$message = explode('|', $orders)[1];
			$form_error = true;
		} else {
			unset($_POST);
			$message = message('Alle ordre importeret succesfuldt. I alt importeret: '.count($orders));
		}
	} else if ( explode('|', $orders)[0] == 'false' ) {
		$message = explode('|', $orders)[1];
		$form_error = true;
	} else {
		unset($_POST);
		$message = message('Alle ordre importeret succesfuldt. I alt importeret: '.count($orders));
	}
} else if (isset($_POST['add_order']) && check_user_abilities_min_admin()) {
	$billing_details = [
		'first_name' => $_POST['billing_firstname'],
		'last_name' => $_POST['billing_lastname'],
		'address_1' => $_POST['billing_address_1'],
		'address_2' => $_POST['billing_address_2'],
		'city' => $_POST['billing_city'],
		'postcode' => $_POST['billing_postcode'],
		'country' => $_POST['billing_country'],
		'company' => $_POST['billing_company'],
		'phone' => $_POST['billing_phone'],
		'email' => $_POST['billing_email'],
		'state' => NULL
	];
	$shipping_details = [
		'first_name' => (empty($_POST['shipping_firstname'])) ? $_POST['billing_firstname'] : $_POST['shipping_firstname'],
		'last_name' => (empty($_POST['shipping_lastname'])) ? $_POST['billing_lastname'] : $_POST['shipping_lastname'],
		'address_1' => (empty($_POST['shipping_address_1'])) ? $_POST['billing_address_1'] : $_POST['shipping_address_1'],
		'address_2' => (empty($_POST['shipping_address_2'])) ? $_POST['billing_address_2'] : $_POST['shipping_address_2'],
		'city' => (empty($_POST['shipping_city'])) ? $_POST['billing_city'] : $_POST['shipping_city'],
		'postcode' => (empty($_POST['shipping_postcode'])) ? $_POST['billing_postcode'] : $_POST['shipping_postcode'],
		'country' => (empty($_POST['shipping_country'])) ? $_POST['billing_country'] : $_POST['shipping_country'],
		'company' => (empty($_POST['shipping_company'])) ? $_POST['billing_company'] : $_POST['shipping_company'],
		'state' => NULL
	];

	$product = (isset($_POST['product'])) ? $_POST['product'] : [] ;
	$shipping = (isset($_POST['shipping'])) ? $_POST['shipping'] : [] ;
	$fee = (isset($_POST['fee'])) ? $_POST['fee'] : [] ;
	$discount = (isset($_POST['discount'])) ? $_POST['discount'] : [] ;
	$total_discount = (isset($_POST['total_discount'])) ? $_POST['total_discount'] : 0 ;

	if (!empty($product)) {
		$result = add_order($product, $shipping, $fee, $discount, $billing_details, $shipping_details, $_POST['currency'], $_POST['status'], $_POST['order_created_at'], $_POST['order_updated_at'], $_POST['order_completed_at'], $_POST['payment'], $_POST['is_paid'], $_POST['note']);

		if ($result === true) {
			header('Location: '.BASE_URL.'/admin/orders.php?invoice_id='.$_POST['invoice_id'].'&action=view');
			exit;
		} else {
			$message = message('Noget gik galt, prøv igen.', 'danger');
		}
	}

	$message = message('Du skal tilføje min. 1 produkt.', 'danger');
}

$is_action = false;

if (isset($_GET['action'])){
	if (!empty($_GET['action'])){
		if ($_GET['action'] != 'add' && $_GET['action'] != 'edit' && $_GET['action'] != 'view' && $_GET['action'] != 'pull' && $_GET['action'] != 'export_csv' && $_GET['action'] != 'export_pdf') {
			header('Location: '.BASE_URL.'/'.$global['current_url']);
			exit;
		}

		if ($_GET['action'] == 'add') {
			if(check_user_abilities_min_admin()){
				$action = 'add';
				$is_action = true;
			} else {
				header('Location: '.BASE_URL.'/'.$global['current_url']);
				exit;
			}
			
		} else if ($_GET['action'] == 'pull') {
			if(check_user_abilities_min_admin()){
				$action = 'pull';
				$is_action = true;
			} else {
				header('Location: '.BASE_URL.'/'.$global['current_url']);
				exit;
			}
		} else if (isset($_GET['invoice_id'])) {
			if (!empty($_GET['invoice_id'])) {
				$invoice_id = $_GET['invoice_id'];

				$sth = $db->prepare("SELECT * FROM orders WHERE invoice_id = :invoice_id LIMIT 1");
				$sth->bindParam(':invoice_id', $invoice_id);
				$sth->execute();

				$order = $sth->fetch(PDO::FETCH_ASSOC);

				if (!$order) {
					header('Location: '.BASE_URL.'/'.$global['current_url']);
					exit;
				}

				$order['shipping_methods'] = json_decode($order['shipping_methods'], true);
				$order['payment_details'] = json_decode($order['payment_details'], true);
				$order['billing_address'] = json_decode($order['billing_address'], true);
				$order['shipping_address'] = json_decode($order['shipping_address'], true);

				$order['line_items'] = json_decode($order['line_items'], true);
				$order['shipping_lines'] = json_decode($order['shipping_lines'], true);
				$order['tax_lines'] = json_decode($order['tax_lines'], true);
				$order['fee_lines'] = json_decode($order['fee_lines'], true);
				$order['coupon_lines'] = json_decode($order['coupon_lines'], true);

				$order['export_csv'] = json_decode($order['export_csv'], true);

				if ($_GET['action'] == 'edit') {
					$action = 'edit';
					$is_action = true;
				} else if ($_GET['action'] == 'view') {
					$action = 'view';
					$is_action = true;
				} else if ($_GET['action'] == 'export_csv') {
					$action = 'view';
					$is_action = true;

					//Generate csv + download and then header to current order
					$invoice = array('invoice_'.$invoice_id => 'on');
					download_csv_orders_by_ids($invoice);
				} else if ($_GET['action'] == 'export_pdf') {
					$action = 'view';
					$is_action = true;
					$invoice = array('invoice_'.$invoice_id => 'on');
					download_pdf_orders_by_ids($invoice);
				}
			}
		} else {
			$is_action = message('En fejl opstod, gå tilbage til Dashboard.', 'danger', false);
		}
	} else {
		$is_action = message('En fejl opstod, gå tilbage til Dashboard.', 'danger', false);
	}
} else {
	$orders = get_order_for_table();
}

require '../templates/admin/header.php';

?>

<div class="row">
	<?php require '../templates/admin/sidebar.php'; ?>
	<div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
		<?php echo $message; ?>
		<?php if($is_action): ?>
			<?php if($is_action === true): ?>
				<?php if($action == 'pull'): ?>
					<h1 class="page-header"><?php echo $titles['admin/index.php'].' / '.$global['site_title']; ?> / Importér</h1>
					<form class="form-horizontal" method="post" id="pull_orders_form">
						<div class="form-group <?php echo (isset($form_error)) ? 'has-error' : '' ;?>">
							<label for="limit" class="col-sm-2 control-label">Max. ordre antal pr. side.</label>
							<div class="col-sm-10">
								<input required type="number" class="form-control" name="limit" id="limit" placeholder="-1 for alle" value="<?php echo (isset($_POST['limit'])) ? $_POST['limit'] : ''; ?>">
							</div>
						</div>
						<div class="form-group <?php echo (isset($form_error)) ? 'has-error' : '' ;?>">
							<label for="min_date" class="col-sm-2 control-label">Fra dato</label>
							<div class="col-sm-6">
								<input required type="date" class="form-control" name="min_date" id="min_date" placeholder="YYYY-MM-DD" value="<?php echo (isset($_POST['min_date'])) ? $_POST['min_date'] : ''; ?>">
							</div>
							<div class="col-sm-4">
								<input required type="time" class="form-control" name="min_time" id="min_time" placeholder="TT:MM:SS" value="<?php echo (isset($_POST['min_time'])) ? $_POST['min_time'] : ''; ?>">
							</div>
						</div>
						<div class="form-group <?php echo (isset($form_error)) ? 'has-error' : '' ;?>">
							<label for="max_date" class="col-sm-2 control-label">Til dato</label>
							<div class="col-sm-6">
								<input required type="date" class="form-control" name="max_date" id="max_date" placeholder="YYYY-MM-DD" value="<?php echo (isset($_POST['max_date'])) ? $_POST['max_date'] : ''; ?>">
							</div>
							<div class="col-sm-4">
								<input required type="time" class="form-control" name="max_time" id="max_time" placeholder="TT:MM:SS" value="<?php echo (isset($_POST['max_time'])) ? $_POST['max_time'] : ''; ?>">
							</div>
						</div>
						<div class="form-group">
							<div class="col-sm-offset-2 col-sm-10">
								<button required type="submit" class="btn btn-danger" name="pull_orders">Hent</button>
							</div>
						</div>
						<div class="form-group">
							<div class="col-sm-offset-2 col-sm-10">
								<?php echo message('Ordrerne du importerer bliver hentet fra alle dine WC Shops og derefter lavet som ordre & faktura.', 'info', false); ?>
								<?php echo message('Importeringen kan ikke annulleres og kan tage op til flere minutter!', 'danger', false); ?>
							</div>
						</div>
					</form>
				<?php elseif($action == 'add'): ?>
					<?php
						$next_invoice = get_setting('next_invoice');
						$internal_order_id = get_setting('next_internal_order_id');
						$url = $db_settings['base_url'];
					?>
					<form method="post" class="form-horizontal">
						<h1 class="page-header"><?php echo $titles['admin/index.php'].' / '.$global['site_title']; ?> / Tilføj</h1>
						<div class="row">
							<div class="col-sm-12"><h3>Ordredetaljer</h3></div>
							<div class="col-sm-6">
								<div class="table-responsive">
									<table class="table table-condensed">
										<tr>
											<td><label for="invoice_id">Faktura nr.</label></td>
											<td><input readonly type="number" id="invoice_id" name="invoice_id" class="form-control" value="<?php echo $next_invoice; ?>"></td>
										</tr>
										<tr>
											<td><label for="owner_site">Shop</label></td>
											<td><input readonly type="text" id="owner_site" class="form-control" value="<?php echo $url; ?>"></td>
										</tr>
										<tr>
											<td><label for="order_id">Ordre ID</label></td>
											<td><input readonly type="text" id="order_id" class="form-control" value="<?php echo $internal_order_id; ?>"></td>
										</tr>
										<tr>
											<td><label for="currency">Valuta</label></td>
											<td><input type="text" id="currency" class="form-control" name="currency" placeholder="Eks. DKK" value="DKK"></td>
										</tr>
										<tr>
											<td><label for="status">Status</label></td>
											<td>
												<select class="form-control" name="status" id="status">
													<option selected value="completed">Færdig</option>
													<option value="refunded">Refunderet</option>
												</select>
											</td>
										</tr>
										<tr>
											<td><label for="order_created_at">Lavet</label></td>
											<td><input type="text" id="order_created_at" class="form-control" name="order_created_at" placeholder="Eks. 2016-02-01 09:02:05"></td>
										</tr>
										<tr>
											<td><label for="order_updated_at">Opdateret</label></td>
											<td><input type="text" id="order_updated_at" class="form-control" name="order_updated_at" placeholder="Eks. åååå-mm-dd tt:mm:ss"></td>
										</tr>
										<tr>
											<td><label for="order_completed_at">Færdig</label></td>
											<td><input type="text" id="order_completed_at" class="form-control" name="order_completed_at" placeholder="Eks. åååå-mm-dd tt:mm:ss"></td>
										</tr>
										<tr><td></td><td></td></tr>
									</table>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="table-responsive">
									<table class="table table-condensed">
										<tr>
											<td><label for="customer_ip">Kunde IP</label></td>
											<td><input disabled type="text" id="customer_ip" class="form-control" value="<?php echo get_current_user_ip(); ?> (Din IP)"></td>
										</tr>
										<tr>
											<td><label for="customer_id">Kunde ID</label></td>
											<td><input disabled type="text" id="customer_id" class="form-control" value="<?php echo $_SESSION['user_id']; ?> (Dit bruger ID)"></td>
										</tr>
										<tr>
											<td><label for="payment">Betaling</label></td>
											<td>
												<select name="payment" id="payment" class="form-control">
													<option value="wiretransfer_Bankoverførsel">Bankoverførsel</option>
													<option selected value="cash_Kontant">Kontant</option>
													<option value="creditcart_Kreditkort">Kreditkort</option>
													<option value="other_Andet">Andet</option>
												</select>
											</td>
										</tr>
										<tr>
											<td><label for="is_paid">Er betalt?</label></td>
											<td>
												<select name="is_paid" id="is_paid" class="form-control">
													<option value="yes">Ja</option>
													<option value="no">Nej</option>
												</select>
											</td>
										</tr>
										<tr>
											<td><label for="note">Note</label></td>
											<td><textarea name="note" id="note" rows="8" class="form-control"></textarea></td><?php /*<input type="text" class="form-control" id="note" name="note">*/ ?>
										</tr>
										<tr><td></td><td></td></tr>
									</table>
								</div>
							</div>
							<div class="col-sm-12"><h3>Ordrelinjer</h3></div>
							<div class="col-sm-12">
								<div class="table-responsive">
									<table class="table table-striped" id="orderlines_table">
										<tr id="orderlines_header">
											<th></th>
											<th>Navn</th>
											<th>Stk. pris</th>
											<th>Mængde</th>
											<th>Subtotal</th>
											<th>Total moms</th>
											<th>Total</th>
										</tr>
										<tr class="product_row">
											<td>Produkt:</td>
											<td><input class="form-control" data-row="product" data-id="0" data-name="name" name="product[0][name]" type="text"></td>
											<td><input min="0" pattern="[0-9]+([\.,][0-9]+)?" step="0.01" class="form-control" oninput="calculate_line(this)" data-row="product" data-id="0" data-name="qty_price" name="product[0][qty_price]" type="number"></td>
											<td><input min="1" class="form-control" oninput="calculate_line(this)" data-row="product" data-id="0" data-name="qty" name="product[0][qty]" type="number" value="1"></td>
											<td><input min="0" readonly class="form-control" oninput="calculate_line(this)" data-row="product" data-id="0" data-name="subtotal" name="product[0][subtotal]" type="number" value="0"></td>
											<td><input min="0" readonly class="form-control" data-row="product" data-id="0" data-name="total_tax" name="product[0][total_tax]" type="number" value="0"></td>
											<td><input min="0" readonly class="form-control" data-row="product" data-id="0" data-name="total" name="product[0][total]" type="number" value="0"></td>
										</tr>
									</table>
								</div>
							</div>
							<div class="col-sm-12">
								<div class="table-responsive">
									<table class="table table-striped">
										<tr id="total_row">
											<td>I alt</td>
											<td></td>
											<td></td>
											<td><input readonly class="form-control" type="number" name="total_line_items" id="total_line_items" value="1"></td>
											<td><input readonly class="form-control" type="number" name="subtotal" id="subtotal" value="0"></td>
											<td><input readonly class="form-control" type="number" name="total_tax" id="total_tax" value="0"></td>
											<td><input readonly class="form-control" type="number" name="total_price" id="total_price" value="0"></td>
										</tr>
										<tr id="total_discount_row">
											<td>Rabat i alt</td>
											<td>(er fratrukket)</td>
											<td></td>
											<td></td>
											<td><input readonly class="form-control" type="number" id="discount_subtotal" value="0"></td>
											<td><input readonly class="form-control" type="number" id="discount_total_tax" value="0"></td>
											<td><input readonly class="form-control" type="number" name="total_discount" id="total_discount" value="0"></td>
										</tr>
										<tr id="add_lines">
											<td>Tilføj nye linjer</td>
											<td></td>
											<td></td>
											<td>
												<button id="add_product" onclick="add_line(this); return false;" data-name="product" class="btn btn-default">+ Produktlinje</button>
											</td>
											<td>
												<button id="add_shipping" onclick="add_line(this); return false;" data-name="shipping" class="btn btn-default">+ Leveringslinje</button>
											</td>
											<td>
												<button id="add_fee" onclick="add_line(this); return false;" data-name="fee" class="btn btn-default">+ Gebyrlinje</button>
											</td>
											<td>
												<button id="add_discount" onclick="add_line(this); return false;" data-name="discount"  class="btn btn-default">+ Rabatlinje</button>
											</td>
										</tr>
										<tr id="subtract_lines">
											<td>Fjern linjer</td>
											<td></td>
											<td></td>
											<td>
												<button id="subtract_product" onclick="remove_line(this); return false;" data-name="product" class="btn btn-default">- Produktlinje</button>
											</td>
											<td>
												<button id="subtract_shipping" onclick="remove_line(this); return false;" data-name="shipping" class="btn btn-default">- Leveringslinje</button>
											</td>
											<td>
												<button id="subtract_fee" onclick="remove_line(this); return false;" data-name="fee" class="btn btn-default">- Gebyrlinje</button>
											</td>
											<td>
												<button id="subtract_discount" onclick="remove_line(this); return false;" data-name="discount" class="btn btn-default">- Rabatlinje</button>
											</td>
										</tr>
									</table>
								</div>
							</div>
							<div class="col-sm-12"><h3>Kundedetaljer</h3></div>
							<div class="col-sm-6">
								<h5>Faktureringsoplysninger</h5>
								<div class="table-responsive">
									<table class="table table-condensed">
										<tr>
											<td><label for="billing_firstname">Fornavn</label></td>
											<td><input required type="text" id="billing_firstname" name="billing_firstname" class="form-control"></td>
										</tr>
										<tr>
											<td><label for="billing_lastname">Efternavn</label></td>
											<td><input type="text" id="billing_lastname" name="billing_lastname" class="form-control"></td>
										</tr>
										<tr>
											<td><label for="billing_address_1">Adresse 1</label></td>
											<td><input required type="text" id="billing_address_1" name="billing_address_1" class="form-control"></td>
										</tr>
										<tr>
											<td><label for="billing_address_2">Adresse 2</label></td>
											<td><input type="text" id="billing_address_2" name="billing_address_2" class="form-control"></td>
										</tr>
										<tr>
											<td><label for="billing_city">By</label></td>
											<td><input required type="text" id="billing_city" name="billing_city" class="form-control"></td>
										</tr>
										<tr>
											<td><label for="billing_postcode">Postnummer</label></td>
											<td><input required type="number" id="billing_postcode" name="billing_postcode" class="form-control"></td>
										</tr>
										<tr>
											<td><label for="billing_country">Land</label></td>
											<td><input required type="text" id="billing_country" name="billing_country" class="form-control" placeholder="Eks: DK" value="DK"></td>
										</tr>
										<tr>
											<td><label for="billing_company">Virksomhed</label></td>
											<td><input type="text" id="billing_company" name="billing_company" class="form-control"></td>
										</tr>
										<tr>
											<td><label for="billing_email">E-mail</label></td>
											<td><input type="email" id="billing_email" name="billing_email" class="form-control"></td>
										</tr>
										<tr>
											<td><label for="billing_phone">Telefon</label></td>
											<td><input type="number" id="billing_phone" name="billing_phone" class="form-control"></td>
										</tr>
										<tr><td></td><td></td></tr>
									</table>
								</div>
							</div>
							<div class="col-sm-6">
								<h5>Forsendelsesoplysninger</h5>
								<div class="table-responsive">
									<table class="table table-condensed">
										<tr>
											<td><label for="shipping_firstname">Fornavn</label></td>
											<td><input required type="text" id="shipping_firstname" name="shipping_firstname" class="form-control"></td>
										</tr>
										<tr>
											<td><label for="shipping_lastname">Efternavn</label></td>
											<td><input type="text" id="shipping_lastname" name="shipping_lastname" class="form-control"></td>
										</tr>
										<tr>
											<td><label for="shipping_address_1">Adresse 1</label></td>
											<td><input required type="text" id="shipping_address_1" name="shipping_address_1" class="form-control"></td>
										</tr>
										<tr>
											<td><label for="shipping_address_2">Adresse 2</label></td>
											<td><input type="text" id="shipping_address_2" name="shipping_address_2" class="form-control"></td>
										</tr>
										<tr>
											<td><label for="shipping_city">By</label></td>
											<td><input required type="text" id="shipping_city" name="shipping_city" class="form-control"></td>
										</tr>
										<tr>
											<td><label for="shipping_postcode">Postnummer</label></td>
											<td><input required type="number" id="shipping_postcode" name="shipping_postcode" class="form-control"></td>
										</tr>
										<tr>
											<td><label for="shipping_country">Land</label></td>
											<td><input required type="text" id="shipping_country" name="shipping_country" class="form-control" placeholder="Eks: DK" value="DK"></td>
										</tr>
										<tr>
											<td><label for="shipping_company">Virksomhed</label></td>
											<td><input type="text" id="shipping_company" name="shipping_company" class="form-control"></td>
										</tr>
										<tr><td></td><td></td></tr>
									</table>
								</div>
							</div>	
							<div class="col-sm-12">
								<input class="btn btn-primary btn-lg btn-block" type="submit" name="add_order" value="Tilføj ordre">
							</div>
						</div>
					</form>
				<?php elseif($action == 'view'): ?>
					<h1 class="page-header"><?php echo $titles['admin/index.php'].' / '.$global['site_title']; ?> / Vis</h1>
					<div class="row">
						<div class="col-sm-12"><h3>Ordredetaljer</h3></div>
						<div class="col-sm-6">
							<div class="table-responsive">
								<table class="table table-condensed">
									<tr>
										<td>Faktura nr.</td>
										<td><?php echo $order['invoice_id']; ?></td>
									</tr>
									<tr>
										<td>Shop</td>
										<td><a href="<?php echo $order['owner_site_url']; ?>" title="<?php echo $order['owner_site_name']; ?>" target="_blank"><?php echo $order['owner_site_name']; ?></a></td>
									</tr>
									<tr>
										<td>Ordre ID</td>
										<td>#<?php echo $order['order_id']; ?> (<a href="<?php echo ($order['owner_site_id'] != 0) ? $order['owner_site_url'].'/wp-admin/post.php?post='.$order['order_id'].'&action=edit' : BASE_URL.'/admin/orders.php?invoice_id='.$order['invoice_id'].'&action=view' ; ?>" title="Vis ordre på <?php echo $order['owner_site_name']; ?>" target="_blank">Vis ordre</a>)</td>
									</tr>
									<tr>
										<td>Valuta</td>
										<td><?php echo $order['currency']; ?></td>
									</tr>
									<tr>
										<td>Status</td>
										<td><?php echo $order['status']; ?></td>
									</tr>
									<tr>
										<td>Lavet</td>
										<td>d. <?php echo $order['order_created_at']; ?></td>
									</tr>
									<tr>
										<td>Opdateret</td>
										<td>d. <?php echo $order['order_updated_at']; ?></td>
									</tr>
									<tr>
										<td>Færdig</td>
										<td>d. <?php echo $order['order_completed_at']; ?></td>
									</tr>
									<tr><td></td><td></td></tr>
								</table>
							</div>
						</div>
						<div class="col-sm-6">
							<div class="table-responsive">
								<table class="table table-condensed">
									<tr>
										<td>Kunde IP</td>
										<td><?php echo $order['customer_ip']; ?></td>
									</tr>
									<tr>
										<td>Kunde ID</td>
										<td><?php echo ($order['customer_id'] == 0) ? 'Gæst' : $order['customer_id']; ?></td>
									</tr>
									<tr>
										<td>Betaling</td>
										<td><?php echo $order['payment_details']['method_title']; ?> - via <?php echo $order['payment_details']['method_id']; ?> (Betalt: <?php echo ($order['payment_details']['paid']) ? 'Ja' : 'Nej '; ?>)</td>
									</tr>
									<tr>
										<td>Note</td>
										<td><?php echo ($order['note'] != '') ? $order['note'] : '(Ingen)'; ?></td>
									</tr>
									<tr>
										<td>Total antal produkter</td>
										<td><?php echo $order['total_line_items_quantity']; ?></td>
									</tr>
									<tr>
										<td>Importeret</td>
										<td>d. <?php echo $order['created_at']; ?></td>
									</tr>
									<tr>
										<td>Opdateret</td>
										<td>d. <?php echo $order['updated_at']; ?></td>
									</tr>
									<tr>
										<td>Eksportér</td>
										<td><a href="?invoice_id=<?php echo $order['invoice_id']; ?>&action=export_pdf" title="Eksportér som .pdf fil">PDF</a>, <a href="?invoice_id=<?php echo $order['invoice_id']; ?>&action=export_csv" title="Eksportér som .csv fil">CSV</a></td>
									</tr>
									<tr><td></td><td></td></tr>
								</table>
							</div>
						</div>
						<div class="col-sm-12"><h3>Ordrelinjer</h3></div>
						<div class="col-sm-12">
							<div class="table-responsive">
								<table class="table table-striped">
									<tr>
										<th>Navn</th>
										<th>Stk. pris</th>
										<th>Mængde</th>
										<th>Subtotal</th>
										<th>Total moms</th>
										<th>Total</th>
									</tr>
									<?php if(!empty($order['line_items']) && $order['line_items'] != ''): ?>
										<?php foreach($order['line_items'] as $item): ?>
											<tr>
												<td><?php echo $item['name']; ?>
													<?php if(!empty($item['meta']) && $item['meta'] != ''): ?>
														<span class="text-muted">
															<?php foreach($item['meta'] as $variation): ?>
																<p class="variation-label">- <?php echo $variation['label'].': '.$variation['value']; ?></p>
															<?php endforeach; ?>
														</span>
													<?php endif; ?>
												</td>
												<td><?php echo number_format($item['price'], 2, ',', '.'); ?></td>
												<td><?php echo $item['quantity']; ?></td>
												<td><?php echo number_format($item['price']*$item['quantity'], 2, ',', '.'); ?></td>
												<td><?php echo number_format($item['total_tax'], 2, ',', '.'); ?></td>
												<td><?php echo number_format($item['total']+$item['total_tax'], 2, ',', '.'); ?></td>
											</tr>
										<?php endforeach; ?>
									<?php endif; ?>
									<?php if(!empty($order['shipping_lines']) && $order['shipping_lines'] != ''): ?>
										<?php foreach($order['shipping_lines'] as $line): ?>
											<tr>
												<td>Fragt: <?php echo $line['method_title'] ?></td>
												<td></td>
												<td></td>
												<td><?php echo number_format($line['total']*1.25*0.8, 2, ',', '.'); ?></td>
												<td><?php echo number_format($line['total']*1.25*0.2, 2, ',', '.'); ?></td>
												<td><?php echo number_format($line['total']*1.25, 2, ',', '.'); ?></td>
											</tr>
										<?php endforeach; ?>
									<?php endif; ?>
									<?php $fee = 0; $fee_tax = 0; ?>
									<?php if(!empty($order['fee_lines']) && $order['fee_lines'] != ''): ?>
										<?php foreach($order['fee_lines'] as $line): ?>
											<tr>
												<td>Gebyr: <?php echo $line['title']; ?></td>
												<td></td>
												<td></td>
												<td><?php echo number_format($line['total'], 2, ',', '.'); ?></td>
												<td><?php echo number_format($line['total_tax'], 2, ',', '.'); ?></td>
												<td><?php echo number_format($line['total']+$line['total_tax'], 2, ',', '.'); ?></td>
												<?php $fee += $line['total']; ?>
												<?php $fee_tax += $line['total_tax']; ?>
											</tr>
										<?php endforeach; ?>
									<?php endif; ?>
									<tr>
										<td>I alt</td>
										<td></td>
										<td></td>
										<?php

											$subtotal = $order['subtotal']-$order['total_discount'];

											$difference = round($order['total'] - ($subtotal + $order['total_shipping'] + $fee + $order['total_tax']), 2, PHP_ROUND_HALF_UP);

											if ($subtotal != 0 && $difference != 0 && $difference != -0 && $difference != '0' && $difference != '-0') {
												$subtotal = $subtotal + $difference;
											}
										?>
										<td><?php echo number_format($subtotal+$order['total_shipping']+$fee, 2, ',', '.'); ?></td>
										<td><?php echo number_format($order['total_tax'], 2, ',', '.'); ?></td>
										<td><?php echo number_format($order['total'], 2, ',', '.'); ?></td>
									</tr>
									<?php if(!empty($order['coupon_lines']) && $order['coupon_lines'] != ''): ?>
										<?php foreach($order['coupon_lines'] as $line): ?>
											<tr>
												<td>Rabat: "<?php echo $line['code'] ?>"</td>
												<td>-<?php echo number_format($line['amount'], 2, ',', '.'); ?></td>
												<td></td>
												<td></td>
												<td></td>
												<td>(Er fratrukket)</td>
											</tr>
										<?php endforeach; ?>
									<?php endif; ?>
									<?php if(!empty($order['total_discount']) && $order['total_discount'] != ''): ?>
										<tr>
											<td>Rabat i alt (er fratrukket)</td>
											<td></td>
											<td></td>
											<td>-<?php echo number_format($order['total_discount'], 2, ',', '.'); ?></td>
											<td>-<?php echo number_format(($order['total_discount']*1.25*0.2), 2, ',', '.'); ?></td>
											<td>-<?php echo number_format(($order['total_discount']*1.25), 2, ',', '.'); ?></td>
										</tr>
									<?php endif; ?>
								</table>
							</div>
						</div>
						<div class="col-sm-12"><h3>Kundedetaljer</h3></div>
						<div class="col-sm-6">
							<h5>Faktureringsoplysninger</h5>
							<div class="table-responsive">
								<table class="table table-condensed">
									<tr>
										<td>Navn</td>
										<td class="text-capitalize"><?php echo $order['billing_address']['first_name'].' '.$order['billing_address']['last_name']; ?></td>
									</tr>
									<tr>
										<td>Adresse 1</td>
										<td class="text-capitalize"><?php echo $order['billing_address']['address_1']; ?></td>
									</tr>
									<tr>
										<td>Adresse 2</td>
										<td class="text-capitalize"><?php echo $order['billing_address']['address_2']; ?></td>
									</tr>
									<tr>
										<td>By</td>
										<td class="text-capitalize"><?php echo $order['billing_address']['city']; ?></td>
									</tr>
									<tr>
										<td>Postnummer</td>
										<td><?php echo $order['billing_address']['postcode']; ?></td>
									</tr>
									<tr>
										<td>Land</td>
										<td class="text-capitalize"><?php echo $order['billing_address']['country']; ?></td>
									</tr>
									<tr>
										<td>Virksomhed</td>
										<td><?php echo $order['billing_address']['company']; ?></td>
									</tr>
									<tr>
										<td>E-mail</td>
										<td><?php echo $order['billing_address']['email']; ?></td>
									</tr>
									<tr>
										<td>Telefon</td>
										<td><?php echo $order['billing_address']['phone']; ?></td>
									</tr>
									<tr><td></td><td></td></tr>
								</table>
							</div>
						</div>
						<div class="col-sm-6">
							<h5>Forsendelsesoplysninger</h5>
							<div class="table-responsive">
								<table class="table table-condensed">
									<tr>
										<td>Navn</td>
										<td class="text-capitalize"><?php echo $order['shipping_address']['first_name'].' '.$order['shipping_address']['last_name']; ?></td>
									</tr>
									<tr>
										<td>Adresse 1</td>
										<td class="text-capitalize"><?php echo $order['shipping_address']['address_1']; ?></td>
									</tr>
									<tr>
										<td>Adresse 2</td>
										<td class="text-capitalize"><?php echo $order['shipping_address']['address_2']; ?></td>
									</tr>
									<tr>
										<td>By</td>
										<td class="text-capitalize"><?php echo $order['shipping_address']['city']; ?></td>
									</tr>
									<tr>
										<td>Postnummer</td>
										<td><?php echo $order['shipping_address']['postcode']; ?></td>
									</tr>
									<tr>
										<td>Land</td>
										<td class="text-capitalize"><?php echo $order['shipping_address']['country']; ?></td>
									</tr>
									<tr>
										<td>Virksomhed</td>
										<td><?php echo $order['shipping_address']['company']; ?></td>
									</tr>
									<tr><td></td><td></td></tr>
								</table>
							</div>
						</div>
					</div>
				<?php elseif($action == 'edit'): ?>
					<h1 class="page-header"><?php echo $titles['admin/index.php'].' / '.$global['site_title']; ?> / Rediger</h1>
					<?php echo message('Denne funktion kommer snart! <a class="alert-link" href="'.BASE_URL.'/'.$global['current_url'].'">Tilbage?</a>', 'info', false); ?>
				<?php else: ?>
					<?php echo message('En fejl opstod, gå tilbage til Dashboard.', 'danger', false); ?>
				<?php endif; ?>
			<?php else: ?>
				<?php echo $is_action; ?>
			<?php endif; ?>
		<?php else: ?>
			<h1 class="page-header"><?php echo $titles['admin/index.php'].' / '.$global['site_title']; ?><?php if(check_user_abilities_min_admin()): ?><span class="pull-right"><a class="btn btn-success" href="orders.php?action=add" role="button">Tilføj ordre</a><a class="btn btn-success" href="orders.php?action=pull" role="button">Importér ordrer</a></span><?php endif; ?></h1>
			<?php if(!empty($orders)): ?>
				<form class="form-horizontal" method="post">
					<div class="table-responsive">
						<table id="orders_table" class="table table-striped">
							<thead>
								<tr>
									<th>Faktura nr.</th>
									<th>Ordre id</th>
									<th>Website</th>
									<th>Navn</th>
									<th></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($orders as $order) : ?>
									<tr class="order_line">
										<td><?php echo $order['invoice_id']; ?></td>
										<td><?php echo $order['order_id']; ?></td>
										<td><a href="<?php echo $order['owner_site_url']; ?>" target="_blank"><?php echo $order['owner_site_name']; ?></a></td>
										<td class="name"><?php echo $order['billing_address']['first_name'].' '.$order['billing_address']['last_name']; ?></td>
										<td><a href="orders.php?invoice_id=<?php echo $order['invoice_id']; ?>&action=view" class="btn btn-default">Vis</a></td>
									</tr>	
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				</form>
			<?php else: ?>
				<?php echo message('Du har ingen ordrer endnu.', 'warning', false); ?>
			<?php endif; ?>
		<?php endif; ?>
	</div>
</div>

<?php

require '../templates/admin/footer.php';

?>