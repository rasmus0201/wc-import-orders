<?php
require '../app/db.php';
require '../app/init.php';

if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
	header('Location: '.BASE_URL);
	exit;
}

require '../templates/admin/header.php';
?>

<div class="row">
	<?php require '../templates/admin/sidebar.php'; ?>
	<div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
		<h1 class="page-header"><?php echo $global['site_title']; ?></h1>
		<div class="panel panel-default">
			<div class="panel-body">
				<p>
					Dette er værktøjs-sektionen, her findes der massere er brugbar værktøjer til hjælp og gavn.
				</p>
				<p>Værktøjerne vil fremme alle dine / Ulvemosens Handelsselskabs ApS' dagligdags processor.</p>
				<p>Brug navigationen til at finde det værktøj du har brug for.</p>
			</div>
		</div>
	</div>
</div>

<?php

require '../templates/admin/footer.php';

?>