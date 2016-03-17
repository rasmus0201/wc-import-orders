<?php

require 'app/db.php';
require 'app/init.php';


if (isset($_POST['login-submit'])) {
	$login = login($_POST['email'], $_POST['password']);
	if (!$login) {
		$message = message('E-mail eller password er forkert!', 'danger', true);
	}
}

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin']) {
	header('Location: admin/');
	exit;
}

require 'templates/header.php';


?>
<?php echo $message; ?>
<div class="jumbotron">
	<h1>Ulvemosens Handelsselskab ApS</h1>
	<h3>- Administrationsside</h3>
	<p>Omsætning, fakturaer og andre diverse regnskabsrelaterede opgaver.</p>
</div>
<?php


require 'templates/footer.php';

?>