<?php


require '../app/db.php';
require '../app/init.php';


if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
	header('Location: '.BASE_URL);
	exit;
}

if (isset($_POST['change_settings'])) {
	$result = change_settings($_POST['next_invoice'], $_POST['last_pull_date'], $_POST['base_url'], $_POST['base_path']);

	if ($result === true) {
		$message = message('Ændringer blev gemt succesfuldt.', 'success');
	} else {
		$message = $result;
	}

	if (isset($_SESSION['form_error'])) {
		$form_error = true;
		unset($_SESSION['form_error']);
	}
}


require '../templates/admin-header.php';

?>


<div class="row">
	<?php require '../templates/admin-sidebar.php'; ?>
	<div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
		<?php echo $message; ?>
		<h1 class="page-header"><?php echo $global['site_title']; ?></h1>
		<div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2"><h2 class="sub-header">Skift indstillinger</h2></div>
		<form class="form-horizontal" method="post">
			<div class="form-group <?php echo (isset($form_error)) ? 'has-error' : '' ;?>">
				<label for="next_invoice" class="col-sm-2 control-label">next_invoice</label>
				<div class="col-sm-10">
					<input required type="number" class="form-control" name="next_invoice" id="next_invoice" placeholder="0" value="<?php echo (isset($_POST['next_invoice'])) ? $_POST['next_invoice'] : $db_settings['next_invoice']; ?>">
				</div>
			</div>
			<div class="form-group <?php echo (isset($form_error)) ? 'has-error' : '' ;?>">
				<label for="last_pull_date" class="col-sm-2 control-label">last_pull_date</label>
				<div class="col-sm-10">
					<input required type="text" class="form-control" name="last_pull_date" id="last_pull_date" placeholder="åååå-mm-dd tt:mm:ss" value="<?php echo (isset($_POST['last_pull_date'])) ? $_POST['last_pull_date'] : $db_settings['last_pull_date']; ?>">
				</div>
			</div>
			<div class="form-group <?php echo (isset($form_error)) ? 'has-error' : '' ;?>">
				<label for="base_url" class="col-sm-2 control-label">base_url</label>
				<div class="col-sm-10">
					<input required type="text" class="form-control" name="base_url" id="base_url" placeholder="http://" value="<?php echo (isset($_POST['base_url'])) ? $_POST['base_url'] : $db_settings['base_url']; ?>">
				</div>
			</div>
			<div class="form-group <?php echo (isset($form_error)) ? 'has-error' : '' ;?>">
				<label for="base_path" class="col-sm-2 control-label">base_path</label>
				<div class="col-sm-10">
					<input required type="text" class="form-control" name="base_path" id="base_path" placeholder="/path/to/base/" value="<?php echo (isset($_POST['base_path'])) ? $_POST['base_path'] : $db_settings['base_path']; ?>">
				</div>
			</div>
			<div class="form-group">
				<div class="col-sm-offset-2 col-sm-10">
					<button type="submit" class="btn btn-primary" name="change_settings">Gem</button>
				</div>
			</div>
		</form>
	</div>
</div>


<?php


require '../templates/admin-footer.php';

?>