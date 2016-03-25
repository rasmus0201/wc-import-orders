<?php

require '../app/db.php';
require '../app/init.php';


if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
	header('Location: '.BASE_URL);
	exit;
}

if (isset($_POST['change_user_password'])) {
	$result = change_user_password($_POST['password'], $_POST['password_again']);

	if ($result === true) {
		$message = message('Ændringer blev gemt succesfuldt.', 'success');
	} else {
		$message = $result;
	}

	if (isset($_SESSION['user_password_error'])) {
		$user_password_error = true;
		unset($_SESSION['user_password_error']);
	}
}

$is_action = false;

if (isset($_GET['action'])){
	if (!empty($_GET['action'])){
		if ($_GET['action'] != 'add' && $_GET['action'] != 'edit' && $_GET['action'] != 'view' && $_GET['action'] != 'pull') {
			header('Location: '.BASE_URL.'/'.$global['current_url']);
			exit;
		}

		if ($_GET['action'] == 'add') {
			$action = 'add';
			$is_action = true;
		} else if ($_GET['action'] == 'pull') {
			$action = 'pull';
			$is_action = true;
		} else if (isset($_GET['invoice_id'])) {
			if (!empty($_GET['invoice_id'])) {
				$site_id = $_GET['invoice_id'];

				$sth = $db->prepare("SELECT * FROM orders WHERE invoice_id = :invoice_id LIMIT 1");
				$sth->bindParam(':invoice_id', $invoice_id);
				$sth->execute();

				$site = $sth->fetch(PDO::FETCH_ASSOC);

				if (!$site) {
					header('Location: '.BASE_URL.'/'.$global['current_url']);
					exit;
				}

				if ($_GET['action'] == 'edit') {
					$action = 'edit';
					$is_action = true;
				} else if ($_GET['action'] == 'view') {
					$action = 'view';
					$is_action = true;
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

require '../templates/admin-header.php';

?>

<div class="row">
	<?php require '../templates/admin-sidebar.php'; ?>
	<div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
		<?php if($is_action): ?>
			<?php if($is_action === true): ?>
				<?php if($action == 'pull'): ?>
					<h1 class="page-header"><?php echo $titles['admin/index.php'].' / '.$global['site_title']; ?> / Hent</h1>
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
							</div>
						</div>
					</form>
				<?php elseif($action == 'add'): ?>
					<h1 class="page-header"><?php echo $titles['admin/index.php'].' / '.$global['site_title']; ?> / Tilføj</h1>
					<?php /*<form class="form-horizontal" method="post">
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
							</div>
						</div>
					</form> */ ?>
					<?php echo message('Denne funktion kommer snart! <a class="alert-link" href="'.BASE_URL.'/'.$global['current_url'].'">Tilbage?</a>', 'info', false); ?>
				<?php elseif($action == 'view'): ?>
					<?php echo message('Denne funktion kommer snart! <a class="alert-link" href="'.BASE_URL.'/'.$global['current_url'].'">Tilbage?</a>', 'info', false); ?>
				<?php elseif($action == 'edit'): ?>
					<?php echo message('Denne funktion kommer snart! <a class="alert-link" href="'.BASE_URL.'/'.$global['current_url'].'">Tilbage?</a>', 'info', false); ?>
				<?php else: ?>
					<?php echo message('En fejl opstod, gå tilbage til Dashboard.', 'danger', false); ?>
				<?php endif; ?>
			<?php else: ?>
				<?php echo $is_action; ?>
			<?php endif; ?>
		<?php else: ?>
			<h1 class="page-header"><?php echo $titles['admin/index.php'].' / '.$global['site_title']; ?><span class="pull-right"><a class="btn btn-success" href="orders.php?action=add" role="button">Tilføj ordre</a><a class="btn btn-success" href="orders.php?action=pull" role="button">Hent ordrer</a></span></h1>
			<?php if(!empty($orders)): ?>
				<form class="form-horizontal" method="post">
					<div class="table-responsive">
						<table class="table table-striped">
							<thead>
								<tr>
									<th>Faktura nr.</th>
									<th>Ordre nr.</th>
									<th>Website</th>
									<th></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($orders as $order) : ?>
									<tr>
										<td><?php echo $order['invoice_id']; ?></td>
										<td><?php echo $order['order_id']; ?></td>
										<td><a href="<?php echo $order['owner_site_url']; ?>" target="_blank"><?php echo $order['owner_site_name']; ?></a></td>
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

require '../templates/admin-footer.php';

?>