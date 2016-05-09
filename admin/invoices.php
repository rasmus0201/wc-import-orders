<?php

require '../app/db.php';
require '../app/init.php';


if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
	header('Location: '.ADMIN_URL);
	exit;
}
if ((isset($_POST['export_csv']) || isset($_POST['export_pdf']) || isset($_POST['export_csv_bulk']) || isset($_POST['export_pdf_bulk'])) && check_user_abilities_min_accountant() ) {
	if ( (!empty($_POST['invoices']) && !is_null($_POST['invoices']) && $_POST['invoices'] != '') || ($_POST['export_bulk_from'] != '' && $_POST['export_bulk_to'] != '') ) {
		if (isset($_POST['export_csv'])) {
			download_csv_orders_by_ids($_POST['invoices']);
		} else if (isset($_POST['export_csv_bulk'])) {
			download_csv_orders_by_ids($_POST['export_bulk_from'].'-'.$_POST['export_bulk_to'], true);
		} else if (isset($_POST['export_pdf'])) {
			download_pdf_orders_by_ids($_POST['invoices']);
		}  else if (isset($_POST['export_pdf_bulk'])) {
			download_pdf_orders_by_ids($_POST['export_bulk_from'].'-'.$_POST['export_bulk_to'], true);
		} else {
			$message = message('Noget gik galt.', 'danger');
		}
	} else {
		$message = message('Du skal vælge mindst 1 faktura.', 'danger');
	}
}

$invoices = get_invoices();


require '../templates/admin/header.php';

?>

<div class="row">
	<?php require '../templates/admin/sidebar.php'; ?>
	<div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
		<?php echo $message; ?>
		<h1 class="page-header"><?php echo $titles['admin/index.php'].' / '.$global['site_title']; ?><?php if(check_user_abilities_min_admin()): ?><span class="pull-right"><a class="btn btn-success" href="orders.php?action=add" role="button">Tilføj ordre</a><a class="btn btn-success" href="orders.php?action=pull" role="button">Importér ordrer</a></span><?php endif; ?></h1>
		<?php if(!empty($invoices)): ?>
			<?php if(check_user_abilities_min_accountant()): ?>
			<form class="form-horizontal" method="post">
				<div class="form-group">
					<div class="btn-group" role="group" id="export_btns">
						<input class="btn btn-default" type="text" name="export_bulk_from" placeholder="Fra faktura nr. (Eks. 100)">
						<input class="btn btn-default" type="text" name="export_bulk_to" placeholder="Til faktura nr. (Eks. 200)">

						<input class="btn btn-default" type="submit" name="export_csv_bulk" value="CSV">
						<input class="btn btn-default" type="submit" name="export_pdf_bulk" value="PDF">
					</div>
				</div>
				<div class="form-group">
					<div class="btn-group" role="group" id="export_btns">
						<input class="btn btn-default" type="submit" name="export_csv" value="Eksportér CSV">
						<input class="btn btn-default" type="submit" name="export_pdf" value="Eksportér PDF">
					</div>
				</div>	
			<?php endif; ?>
				<div class="table-responsive">
					<table id="invoice_table" class="table table-striped">
						<thead>
							<tr>
								<?php if(check_user_abilities_min_accountant()): ?><th><div class="checkbox"><label><input type="checkbox" id="select_all"></label></div></th><?php endif; ?>
								<th>Faktura nr.</th>
								<th>Importeret / Oprettet</th>
								<th>Website</th>
								<?php /*<th></th>*/ ?>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($invoices as $invoice) : ?>
								<tr>
									<?php if(check_user_abilities_min_accountant()): ?>
										<td>
											<div class="checkbox"><label><input type="checkbox" name="invoices[invoice_<?php echo $invoice['invoice_id']; ?>]"></label></div>
										</td>
									<?php endif; ?>
									<td><?php echo $invoice['invoice_id']; ?></td>
									<td><?php echo $invoice['created_at']; ?></td>
									<td><a href="<?php echo $invoice['owner_site_url']; ?>" target="_blank"><?php echo $invoice['owner_site_name']; ?></a></td>
									<?php /*<td><a href="orders.php?id=<?php echo $invoice['id']; ?>&action=view" class="btn btn-default">Vis</a></td> */ ?>
								</tr>	
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			<?php if(check_user_abilities_min_accountant()): ?></form><?php endif; ?>
		<?php else: ?>
			<?php echo message('Du har ingen fakturaer endnu.', 'warning', false); ?>
		<?php endif; ?>
	</div>
</div>



<?php

require '../templates/admin/footer.php';

?>