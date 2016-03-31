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
		
	</div>
</div>

<?php

require '../templates/admin/footer.php';

?>