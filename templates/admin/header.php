<?php

if (!defined('BASE_URL')) {
	header('HTTP/1.0 404 not found');
	exit;
}

require_once 'head.php';

if ( ($global['current_url'] == 'admin/index.php') || 
	($global['current_url'] == 'admin/orders.php') || 
	($global['current_url'] == 'admin/reports.php') || 
	($global['current_url'] == 'admin/invoices.php')) {
	$dashboard_active = 'active';
} else {
	$dashboard_active = '';
}

if ( ($global['current_url'] == 'admin/settings.php') || $global['current_url'] == 'admin/sites.php') {
	$settings_active = 'active';
} else {
	$settings_active = '';
}

if ($global['current_url'] == 'admin/profile.php' || $global['current_url'] == 'admin/users.php') {
	$profile_active = 'active';
} else {
	$profile_active = '';
}

?>


<nav class="navbar navbar-inverse navbar-fixed-top">
	<div class="container-fluid">
		<div class="navbar-header">
			<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
				<span class="sr-only">Navigation</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
			<a class="navbar-brand" href="<?php echo BASE_URL; ?>/"><?php echo $global['project_name']; ?></a>
		</div>
		<div id="navbar" class="navbar-collapse collapse">
			<ul class="nav navbar-nav navbar-right">
				<li class="<?php echo $dashboard_active; ?>"><a href="<?php echo BASE_URL; ?>/admin/"><?php echo $titles['admin/index.php']; ?></a></li>
				<?php if(check_user_abilities_min_admin()): ?><li class="<?php echo $settings_active; ?>"><a href="<?php echo BASE_URL; ?>/admin/settings.php"><?php echo $titles['admin/settings.php']; ?></a></li><?php endif; ?>
				<li class="<?php echo $profile_active; ?>"><a href="<?php echo BASE_URL; ?>/admin/profile.php"><?php echo $titles['admin/profile.php']; ?></a></li>
				<li class="<?php echo ($global['current_url'] == 'admin/logout.php') ? 'active' : ''; ?>"><a href="<?php echo BASE_URL; ?>/admin/logout.php"><?php echo $titles['admin/logout.php']; ?></a></li>
			</ul>
		</div>
	</div>
</nav>
<div class="admin-content container-fluid">