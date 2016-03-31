<?php

require '../app/db.php';
require '../app/init.php';


if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
	header('Location: '.BASE_URL);
	exit;
}
if ((isset($_POST['export_csv']) || isset($_POST['export_pdf'])) && check_user_abilities_min_accountant() ) {
	if (!empty($_POST['invoices']) && !is_null($_POST['invoices']) && $_POST['invoices'] != '') {
		if (isset($_POST['export_csv'])) {
			download_csv_orders_by_ids($_POST['invoices']);
			echo 'heee';
		} else if (isset($_POST['export_pdf'])) {
			//download_pdf_orders_by_ids($_POST['invoices']);
			$message = message('.pdf eksport-funktionen kommer snart!', 'danger');
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
				<input class="btn btn-default" type="submit" name="export_csv" value="Eksportér CSV">
				<input class="btn btn-default" type="submit" name="export_pdf" value="Eksportér PDF">
			<?php endif; ?>
				<div class="table-responsive">
					<table class="table table-striped">
						<thead>
							<tr>
								<?php if(check_user_abilities_min_accountant()): ?><th><div class="checkbox"><label><input type="checkbox" id="select_all"></label></div></th><?php endif; ?>
								<th>Faktura nr.</th>
								<th>Website</th>
								<th></th>
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
									<td><a href="<?php echo $invoice['owner_site_url']; ?>" target="_blank"><?php echo $invoice['owner_site_name']; ?></a></td>
									<td><a href="orders.php?invoice_id=<?php echo $invoice['invoice_id']; ?>&action=view" class="btn btn-default">Vis</a></td>
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