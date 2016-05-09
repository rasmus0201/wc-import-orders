<?php

require '../app/db.php';
require '../app/init.php';


if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
	header('Location: '.ADMIN_URL);
	exit;
}
check_user_abilities_min_admin(true);

if (isset($_POST['add_site'])) {
	$result = add_site(
		$_POST['name'],
		$_POST['url'], 
		$_POST['consumer_key'], 
		$_POST['consumer_secret'], 
		$_POST['address'], 
		$_POST['postcode'], 
		$_POST['city'], 
		$_POST['company_name'], 
		$_POST['company_vat'], 
		$_POST['company_logo_url']);
	
	if ($result === true) {
		unset($_POST);
		$message = message('Siden blev tilføjet succesfuldt.');
	} else {
		$message = $result;
	}

	if (isset($_SESSION['form_error'])) {
		$user_email_error = true;
		unset($_SESSION['form_error']);
	}
} else if (isset($_POST['change_site_details'])) {
	$result = change_site_details(
		$_GET['site_id'], 
		$_POST['name'], 
		$_POST['old_url'], 
		$_POST['new_url'], 
		$_POST['consumer_key'], 
		$_POST['consumer_secret'], 
		$_POST['address'], 
		$_POST['postcode'], 
		$_POST['city'], 
		$_POST['company_name'], 
		$_POST['company_vat'], 
		$_POST['company_logo_url']
		);

	if ($result === true) {
		unset($_POST);
		$message = message('Siden blev ændret succesfuldt.');
	} else {
		$message = $result;
	}

	if (isset($_SESSION['form_error'])) {
		$user_email_error = true;
		unset($_SESSION['form_error']);
	}
}

$is_action = false;

if (isset($_GET['action'])){
	if (!empty($_GET['action'])){
		if ($_GET['action'] != 'edit' && $_GET['action'] != 'delete' && $_GET['action'] != 'add') {
			header('Location: '.BASE_URL.'/'.$global['current_url']);
			exit;
		}

		if ($_GET['action'] == 'add') {
			$action = 'add';
			$is_action = true;
		} else if (isset($_GET['site_id'])) {
			if (!empty($_GET['site_id'])) {
				$site_id = $_GET['site_id'];

				$site = get_site_by_id($site_id);

				if (!$site) {
					header('Location: '.BASE_URL.'/'.$global['current_url']);
					exit;
				}

				if ($_GET['action'] == 'edit') {
					$action = 'edit';
					$is_action = true;
				} else {

					$result = delete_site_by_id($site_id);

					if (!$result) {
						echo message('Noget gik galt.', 'danger');
					} else {
						if ($_SESSION['sites_count'] != 0) {
							$_SESSION['sites_count'] = $_SESSION['sites_count'] - 1;
						}
						
						header('Location: '.BASE_URL.'/'.$global['current_url']);
						exit;
					}
				}
			}
		} else {
			$is_action = message('En fejl opstod, gå tilbage til Dashboard.', 'danger', false);
		}
	} else {
		$is_action = message('En fejl opstod, gå tilbage til Dashboard.', 'danger', false);
	}
} else {
	$sites = get_sites();
}

require '../templates/admin/header.php';

?>

<div class="row">
	<?php require '../templates/admin/sidebar.php'; ?>
	<div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
		<?php if ($is_action) : ?>
			<?php if ($is_action === true) : ?>
				<?php echo $message; ?>
				<?php if($action == 'edit'): ?>
					<h1 class="page-header"><?php echo $titles['admin/settings.php'].' / '.$global['site_title']; ?> / Rediger <span class="pull-right"><a class="btn btn-danger" href="?site_id=<?php echo $site['id']; ?>&action=delete" role="button">Slet</a></span></h1>
					<form class="form-horizontal" method="post">
						<div class="form-group <?php echo (isset($form_error)) ? 'has-error' : '' ;?>">
							<label for="name" class="col-sm-2 control-label">Navn</label>
							<div class="col-sm-10">
								<input type="text" class="form-control" name="name" id="name" placeholder="Website navn" value="<?php echo (isset($_POST['name'])) ? $_POST['name'] : $site['name']; ?>">
							</div>
						</div>
						<div class="form-group <?php echo (isset($form_error)) ? 'has-error' : '' ;?>">
							<label for="url" class="col-sm-2 control-label">URL</label>
							<div class="col-sm-10">
								<input type="hidden" class="form-control" name="old_url" value="<?php echo $site['url']; ?>">
								<input type="url" class="form-control" name="new_url" id="url" placeholder="http://" value="<?php echo (isset($_POST['url'])) ? $_POST['url'] : $site['url']; ?>">
							</div>
						</div>
						<div class="form-group <?php echo (isset($form_error)) ? 'has-error' : '' ;?>">
							<label for="consumer_key" class="col-sm-2 control-label">consumer_key</label>
							<div class="col-sm-10">
								<input type="text" class="form-control" name="consumer_key" id="consumer_key" placeholder="Bliver ikke vist igen, kun gemt!" value="<?php echo (isset($_POST['consumer_key'])) ? $_POST['consumer_key'] : ''; ?>">
							</div>
						</div>
						<div class="form-group <?php echo (isset($form_error)) ? 'has-error' : '' ;?>">
							<label for="consumer_secret" class="col-sm-2 control-label">consumer_secret</label>
							<div class="col-sm-10">
								<input type="text" class="form-control" name="consumer_secret" id="consumer_secret" placeholder="Bliver ikke vist igen, kun gemt!" value="<?php echo (isset($_POST['consumer_secret'])) ? $_POST['consumer_secret'] : ''; ?>">
							</div>
						</div>
						<hr class="separator">
						<div class="form-group <?php echo (isset($form_error)) ? 'has-error' : '' ;?>">
							<label for="address" class="col-sm-2 control-label">Adresse</label>
							<div class="col-sm-10">
								<input type="text" class="form-control" name="address" id="address" placeholder="Testvej 1" value="<?php echo (isset($_POST['address'])) ? $_POST['address'] : $site['address']; ?>">
							</div>
						</div>
						<div class="form-group <?php echo (isset($form_error)) ? 'has-error' : '' ;?>">
							<label for="postcode" class="col-sm-2 control-label">Postnummer</label>
							<div class="col-sm-10">
								<input type="text" class="form-control" name="postcode" id="postcode" placeholder="1234" value="<?php echo (isset($_POST['postcode'])) ? $_POST['postcode'] : $site['postcode']; ?>">
							</div>
						</div>
						<div class="form-group <?php echo (isset($form_error)) ? 'has-error' : '' ;?>">
							<label for="city" class="col-sm-2 control-label">By</label>
							<div class="col-sm-10">
								<input type="text" class="form-control" name="city" id="city" placeholder="Testby" value="<?php echo (isset($_POST['city'])) ? $_POST['city'] : $site['city']; ?>">
							</div>
						</div>
						<div class="form-group <?php echo (isset($form_error)) ? 'has-error' : '' ;?>">
							<label for="company_name" class="col-sm-2 control-label">Virksomhedsnavn</label>
							<div class="col-sm-10">
								<input type="text" class="form-control" name="company_name" id="company_name" placeholder="Test virksomhed A/S" value="<?php echo (isset($_POST['company_name'])) ? $_POST['company_name'] : $site['company_name']; ?>">
							</div>
						</div>
						<div class="form-group <?php echo (isset($form_error)) ? 'has-error' : '' ;?>">
							<label for="company_vat" class="col-sm-2 control-label">Virksomheds cvr.</label>
							<div class="col-sm-10">
								<input type="text" class="form-control" name="company_vat" id="company_vat" placeholder="87654321" value="<?php echo (isset($_POST['company_vat'])) ? $_POST['company_vat'] : $site['company_vat']; ?>">
							</div>
						</div>
						<div class="form-group <?php echo (isset($form_error)) ? 'has-error' : '' ;?>">
							<label for="company_logo_url" class="col-sm-2 control-label">Logo URL</label>
							<div class="col-sm-10">
								<input type="url" class="form-control" name="company_logo_url" id="company_logo_url" placeholder="http://" value="<?php echo (isset($_POST['company_logo_url'])) ? $_POST['company_logo_url'] : $site['company_logo_url']; ?>">
							</div>
						</div>
						<div class="form-group">
							<div class="col-sm-offset-2 col-sm-10">
								<button type="submit" class="btn btn-primary" name="change_site_details">Gem</button>
							</div>
						</div>
						<div class="form-group">
							<div class="col-sm-offset-2 col-sm-10">
								<?php echo message('Hvis du efterlader et felt tomt vil det blive ignoreret, og beholde sin gamle værdi.', 'info', false); ?>
							</div>
						</div>
					</form>
				<?php elseif ($action == 'add') : ?>
					<h1 class="page-header"><?php echo $titles['admin/settings.php'].' / '.$global['site_title']; ?> / Tilføj</h1>
					<form class="form-horizontal" method="post">
						<div class="form-group <?php echo (isset($form_error)) ? 'has-error' : '' ;?>">
							<label for="name" class="col-sm-2 control-label">Navn</label>
							<div class="col-sm-10">
								<input required type="text" class="form-control" name="name" id="name" placeholder="Website navn" value="<?php echo (isset($_POST['name'])) ? $_POST['name'] : ''; ?>">
							</div>
						</div>
						<div class="form-group <?php echo (isset($form_error)) ? 'has-error' : '' ;?>">
							<label for="url" class="col-sm-2 control-label">URL</label>
							<div class="col-sm-10">
								<input required type="url" class="form-control" name="url" id="url" placeholder="http://" value="<?php echo (isset($_POST['url'])) ? $_POST['url'] : ''; ?>">
							</div>
						</div>
						<div class="form-group <?php echo (isset($form_error)) ? 'has-error' : '' ;?>">
							<label for="consumer_key" class="col-sm-2 control-label">consumer_key</label>
							<div class="col-sm-10">
								<input required type="text" class="form-control" name="consumer_key" id="consumer_key" placeholder="Bliver ikke vist igen, kun gemt!" value="<?php echo (isset($_POST['consumer_key'])) ? $_POST['consumer_key'] : ''; ?>">
							</div>
						</div>
						<div class="form-group <?php echo (isset($form_error)) ? 'has-error' : '' ;?>">
							<label for="consumer_secret" class="col-sm-2 control-label">consumer_secret</label>
							<div class="col-sm-10">
								<input required type="text" class="form-control" name="consumer_secret" id="consumer_secret" placeholder="Bliver ikke vist igen, kun gemt!" value="<?php echo (isset($_POST['consumer_secret'])) ? $_POST['consumer_secret'] : ''; ?>">
							</div>
						</div>
						<hr class="separator">
						<div class="form-group <?php echo (isset($form_error)) ? 'has-error' : '' ;?>">
							<label for="address" class="col-sm-2 control-label">Adresse</label>
							<div class="col-sm-10">
								<input required type="text" class="form-control" name="address" id="address" placeholder="Testvej 1" value="<?php echo (isset($_POST['address'])) ? $_POST['address'] : ''; ?>">
							</div>
						</div>
						<div class="form-group <?php echo (isset($form_error)) ? 'has-error' : '' ;?>">
							<label for="postcode" class="col-sm-2 control-label">Postnummer</label>
							<div class="col-sm-10">
								<input required type="text" class="form-control" name="postcode" id="postcode" placeholder="1234" value="<?php echo (isset($_POST['postcode'])) ? $_POST['postcode'] : ''; ?>">
							</div>
						</div>
						<div class="form-group <?php echo (isset($form_error)) ? 'has-error' : '' ;?>">
							<label for="city" class="col-sm-2 control-label">By</label>
							<div class="col-sm-10">
								<input required type="text" class="form-control" name="city" id="city" placeholder="Testby" value="<?php echo (isset($_POST['city'])) ? $_POST['city'] : ''; ?>">
							</div>
						</div>
						<div class="form-group <?php echo (isset($form_error)) ? 'has-error' : '' ;?>">
							<label for="company_name" class="col-sm-2 control-label">Virksomhedsnavn</label>
							<div class="col-sm-10">
								<input required type="text" class="form-control" name="company_name" id="company_name" placeholder="Test virksomhed A/S" value="<?php echo (isset($_POST['company_name'])) ? $_POST['company_name'] : ''; ?>">
							</div>
						</div>
						<div class="form-group <?php echo (isset($form_error)) ? 'has-error' : '' ;?>">
							<label for="company_vat" class="col-sm-2 control-label">Virksomheds cvr.</label>
							<div class="col-sm-10">
								<input type="text" class="form-control" name="company_vat" id="company_vat" placeholder="87654321" value="<?php echo (isset($_POST['company_vat'])) ? $_POST['company_vat'] : ''; ?>">
							</div>
						</div>
						<div class="form-group <?php echo (isset($form_error)) ? 'has-error' : '' ;?>">
							<label for="company_logo_url" class="col-sm-2 control-label">Logo URL</label>
							<div class="col-sm-10">
								<input type="url" class="form-control" name="company_logo_url" id="company_logo_url" placeholder="http://" value="<?php echo (isset($_POST['company_logo_url'])) ? $_POST['company_logo_url'] : ''; ?>">
							</div>
						</div>
						<div class="form-group">
							<div class="col-sm-offset-2 col-sm-10">
								<button type="submit" class="btn btn-primary" name="add_site">Gem</button>
							</div>
						</div>
					</form>
				<?php endif; ?>
			<?php else: ?>
				<?php echo $is_action; ?>
			<?php endif; ?>
		<?php else: ?>
			<h1 class="page-header"><?php echo $titles['admin/settings.php'].' / '.$global['site_title']; ?> <span class="pull-right"><a class="btn btn-success" href="?action=add" role="button">Tilføj side</a></span></h1>
			<?php if(!empty($sites)): ?>
				<div class="table-responsive">
					<table class="table table-striped">
						<thead>
							<tr>
								<th>#</th>
								<th>Navn</th>
								<th>URL</th>
								<th></th>
								<th></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($sites as $site) : ?>
								<tr>
									<td><?php echo $site['id']; ?></td>
									<td><?php echo $site['name']; ?></td>
									<td><a href="<?php echo $site['url']; ?>" target="_blank"><?php echo $site['url']; ?></a></td>
									<td><a class="btn btn-default" href="?site_id=<?php echo $site['id']; ?>&action=edit" role="button">Rediger</a></td>
									<td><a class="btn btn-danger" href="?site_id=<?php echo $site['id']; ?>&action=delete" role="button">Slet</a></td>
								</tr>	
							<?php endforeach; ?>

						</tbody>
					</table>
				</div>
			<?php else: ?>
				<?php echo message('Du har ingen sider endnu.', 'warning', false); ?>
			<?php endif; ?>
		<?php endif; ?>
	</div>
</div>

<?php

require '../templates/admin/footer.php';

?>