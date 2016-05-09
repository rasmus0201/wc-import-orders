<?php


require '../app/db.php';
require '../app/init.php';


if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {

	if (isset($_POST['login-submit'])) {
		$login = login($_POST['email'], $_POST['password']);
		if (!$login) {
			$message = message('E-mail eller password er forkert!', 'danger', true);
		}

		header('Location: '.ADMIN_URL);
		exit;
	}

	require '../templates/header.php';

	?>
	<?php echo $message; ?>
	<div class="jumbotron">
		<h1>Ulvemosens Handelsselskab ApS</h1>
		<h3>- Administrationsside</h3>
		<p>Omsætning, fakturaer og andre diverse regnskabsrelaterede opgaver.</p>
	</div>
	<?php

	require '../templates/footer.php';

	exit;
} else { 

require '../templates/admin/header.php';

?>

<div class="row">
	<?php require '../templates/admin/sidebar.php'; ?>
	<div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
		<h1 class="page-header"><?php echo $global['site_title']; ?></h1>

		<div class="panel panel-default">
			<div class="panel-body">
				<p>Her kan du administrere de fleste ting som foregår i virksomheden Ulvemosens Handelsselskab ApS.</p>
				<p>Du har mulighed for export af ordre både til regnsskabsprogram "C5" (csv) eller som pdf-faktura.</p>
				<p>Der er desuden flere muligheder og værktøjer som kan tages i brug.</p>
				<p>Brug navigationen til at finde rundt.</p>
			</div>
		</div>
	</div>
</div>

<?php
require '../templates/admin/footer.php';
}
?>