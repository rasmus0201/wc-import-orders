<?php

require '../app/db.php';
require '../app/init.php';


if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
	header('Location: '.BASE_URL);
	exit;
}

require '../templates/admin-header.php';

?>

<div class="row">
	<?php require '../templates/admin-sidebar.php'; ?>
	<div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
		<h1 class="page-header"><?php echo $titles['admin/index.php'].' / '.$global['site_title']; ?><span class="pull-right"><a class="btn btn-success" href="orders.php?order_id=new&action=add" role="button">Tilføj</a></span></h1>
		<div class="table-responsive">
			<table class="table table-striped">
				<thead>
					<tr>
						<th>Ordre nr.</th>
						<th>Website</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>2344</td>
						<td><a href="jellybeans.dk" target="_blank">Jellybeans.dk</a></td>
						<td><a href="orders.php?order_id=xx&action=view" class="btn btn-default">Vis</a></td>
					</tr>
					<tr>
						<td>2456</td>
						<td><a href="jellybeans.dk" target="_blank">Jellybeans.dk</a></td>
						<td><a href="orders.php?order_id=xx&action=view" class="btn btn-default">Vis</a></td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</div>



<?php

require '../templates/admin-footer.php';

?>