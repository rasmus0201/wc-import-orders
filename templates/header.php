<?php

if (!defined('BASE_URL')) {
	header('HTTP/1.0 404 not found');
	exit;
}

require_once 'head.php';

?>

<nav class="navbar navbar-inverse navbar-static-top">
	<div class="container">
		<div class="navbar-header">
			<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
				<span class="sr-only">Toggle navigation</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
			<a class="navbar-brand" href="<?php echo BASE_URL; ?>"><?php echo $global['project_name']; ?></a>
		</div>
		<div id="navbar" class="navbar-collapse collapse">
			<form class="navbar-form navbar-right" method="post" action="">
				<div class="form-group">
					<input required type="email" name="email" placeholder="E-mail" class="form-control">
				</div>
				<div class="form-group">
					<input required type="password" name="password" placeholder="Password" class="form-control">
				</div>
				<button type="submit" name="login-submit" class="btn btn-success">Log ind</button>
			</form>
		</div>
	</div>
</nav>
<div class="container">