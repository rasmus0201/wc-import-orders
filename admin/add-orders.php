<?php


require '../app/db.php';
require '../app/init.php';


// $_SESSION['invoices_count'] = 0;
// $_SESSION['orders_count'] = 0;
// $_SESSION['sites_count'] = 0;

$orders = WCApiAddOrdersAndInvoices(get_sites(), get_orders(), $min_date, $max_date, $limit);

if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
	header('Location: '.BASE_URL);
	exit;
}

require '../templates/admin-header.php';

if (!is_string($orders)) {
	if ( explode('|', json_encode($orders))[0] == 'false' ) {
		$message = explode('|', $orders)[1];
	}
} else if ( explode('|', $orders)[0] == 'false' ) {
	$message =  explode('|', $orders)[1];
}


?>



<div class="row">
	<?php require '../templates/admin-sidebar.php'; ?>
	<div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
		<?php echo $message; ?>
		<h1 class="page-header"><?php echo $global['site_title']; ?></h1>
		<div class="row placeholders">
			<div class="col-xs-6 col-sm-3 placeholder">
				<img src="data:image/gif;base64,R0lGODlhAQABAIAAAHd3dwAAACH5BAAAAAAALAAAAAABAAEAAAICRAEAOw==" width="200" height="200" class="img-responsive" alt="Generic placeholder thumbnail">
				<h4>Label</h4>
				<span class="text-muted">Something else</span>
				</div>
				<div class="col-xs-6 col-sm-3 placeholder">
				<img src="data:image/gif;base64,R0lGODlhAQABAIAAAHd3dwAAACH5BAAAAAAALAAAAAABAAEAAAICRAEAOw==" width="200" height="200" class="img-responsive" alt="Generic placeholder thumbnail">
				<h4>Label</h4>
				<span class="text-muted">Something else</span>
				</div>
				<div class="col-xs-6 col-sm-3 placeholder">
				<img src="data:image/gif;base64,R0lGODlhAQABAIAAAHd3dwAAACH5BAAAAAAALAAAAAABAAEAAAICRAEAOw==" width="200" height="200" class="img-responsive" alt="Generic placeholder thumbnail">
				<h4>Label</h4>
				<span class="text-muted">Something else</span>
				</div>
				<div class="col-xs-6 col-sm-3 placeholder">
				<img src="data:image/gif;base64,R0lGODlhAQABAIAAAHd3dwAAACH5BAAAAAAALAAAAAABAAEAAAICRAEAOw==" width="200" height="200" class="img-responsive" alt="Generic placeholder thumbnail">
				<h4>Label</h4>
				<span class="text-muted">Something else</span>
			</div>
		</div>
	</div>
</div>



<?php


require '../templates/admin-footer.php';

?>