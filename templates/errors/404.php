<?php

require '../../app/db.php';
require '../../app/init.php';

$loggedin = false;

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin']) {
	require '../admin/header.php';

	?>
	<div class="row">
		<?php require '../admin/sidebar.php'; ?>
		<div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
			<div class="jumbotron">
				<h1>404 - Side ikke fundet</h1>
				<p><a href="<?php echo BASE_URL; ?>/admin"><?php echo $titles['admin/index.php']; ?></a></p>
			</div>
		</div>
	</div>
	<?php

	require '../admin/footer.php';
	exit;
}

require '../header.php';

?>
<div class="jumbotron">
	<h1>404 - Side ikke fundet</h1>
	<a href="<?php echo BASE_URL; ?>">Forside</a>
</div>
<?php

require '../footer.php';
exit;

?>