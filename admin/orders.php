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
		}
	} else if ( explode('|', $orders)[0] == 'false' ) {
		$message = explode('|', $orders)[1];
		$form_error = true;
	} else {
		unset($_POST);
		$message = message('Alle ordre importeret succesfuldt. I alt importeret: '.count($orders));
	}
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
					$message = message('.csv eksport-funktionen kommer snart!', 'danger');	
					//Generate csv + download and then header to current order
				} else if ($_GET['action'] == 'export_pdf') {
					$action = 'view';
					$is_action = true;
					$message = message('.pdf eksport-funktionen kommer snart!', 'danger');
					//Generate pdf + download and then header to current order
				}
			}
		} else {
			$is_action = message('En fejl opstod, gå tilbage til Dashboard.', 'danger', false);
		}
	} else {
		$is_action = message('En fejl opstod, gå tilbage til Dashboard.', 'danger', false);
	}
} else {
	$orders = get_orders();
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
					<form class="form-horizontal" method="post">
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
					<h1 class="page-header"><?php echo $titles['admin/index.php'].' / '.$global['site_title']; ?> / Tilføj</h1>
					<?php echo message('Denne funktion kommer snart! <a class="alert-link" href="'.BASE_URL.'/'.$global['current_url'].'">Tilbage?</a>', 'info', false); ?>
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
										<td>#<?php echo $order['order_id']; ?> (<a href="<?php echo $order['owner_site_url']; ?>/wp-admin/post.php?post=<?php echo $order['order_id']; ?>&action=edit" title="Vis ordre på <?php echo $order['owner_site_name']; ?>" target="_blank">Vis ordre</a>)</td>
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
												<td></td>
												<td><?php echo number_format($line['total']*1.25*0.2, 2, ',', '.'); ?></td>
												<td><?php echo number_format($line['total']*1.25, 2, ',', '.'); ?></td>
											</tr>
										<?php endforeach; ?>
									<?php endif; ?>
									<?php if(!empty($order['fee_lines']) && $order['fee_lines'] != ''): ?>
										<?php foreach($order['fee_lines'] as $line): ?>
											<tr>
												<td>Gebyr: <?php echo $line['title']; ?></td>
												<td></td>
												<td></td>
												<td></td>
												<td><?php echo number_format($line['total_tax'], 2, ',', '.'); ?></td>
												<td><?php echo number_format($line['total'], 2, ',', '.'); ?></td>
											</tr>
										<?php endforeach; ?>
									<?php endif; ?>
									<tr>
										<td>I alt</td>
										<td></td>
										<td></td>
										<td><?php echo number_format($order['subtotal']-$order['total_discount'], 2, ',', '.'); ?></td>
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
					<?php #echo message('Denne funktion kommer snart! <a class="alert-link" href="'.BASE_URL.'/'.$global['current_url'].'">Tilbage?</a>', 'info', false); ?>
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
						<table class="table table-striped">
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