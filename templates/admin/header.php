<?php

if (!defined('BASE_URL')) {
	header('HTTP/1.0 404 not found');
	exit;
}

require_once 'head.php';

if ( ($global['current_url'] == 'admin/index.php') || 
	($global['current_url'] == 'admin/orders.php') || 
	($global['current_url'] == 'admin/reports.php') || 
	($global['current_url'] == 'admin/invoices.php') || 
	($global['current_url'] == 'admin/users.php')) {
	$dashboard_active = ' active';
} else {
	$dashboard_active = '';
}

if ( ($global['current_url'] == 'admin/settings.php') || $global['current_url'] == 'admin/sites.php') {
	$settings_active = ' active';
} else {
	$settings_active = '';
}

if ($global['current_url'] == 'admin/profile.php') {
	$profile_active = ' active';
} else {
	$profile_active = '';
}

if ( ($global['current_url'] == 'admin/tools.php') || ($global['current_url'] == 'admin/label-maker.php') || ($global['current_url'] == 'admin/profit-estimate.php')  ) {
	$tools_active = ' active';
} else {
	$tools_active = '';
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
			<ul id="mobile-menu" class="nav navbar-nav navbar-right">
				<li class="dropdown<?php echo $dashboard_active; ?>">
					<a href="<?php echo BASE_URL; ?>/admin/index.php" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><?php echo $titles['admin/index.php']; ?> <span class="caret"></span></a>
					<ul class="dropdown-menu">
						<li class="<?php echo ($global['current_url'] == 'admin/index.php') ? 'active' : ''; ?>"><a href="<?php echo BASE_URL; ?>/admin/"><?php echo $titles['admin/index.php']; ?></a></li>
						<li class="<?php echo ($global['current_url'] == 'admin/reports.php') ? 'active' : ''; ?>"><a href="<?php echo BASE_URL; ?>/admin/reports.php"><?php echo $titles['admin/reports.php']; ?></a></li>
						<li class="<?php echo ($global['current_url'] == 'admin/orders.php') ? 'active' : ''; ?>"><a href="<?php echo BASE_URL; ?>/admin/orders.php"><?php echo $titles['admin/orders.php']; ?><span class="badge pull-right"><?php echo $_SESSION['orders_count']; ?></span></a></li>
						<li class="<?php echo ($global['current_url'] == 'admin/invoices.php') ? 'active' : ''; ?>"><a href="<?php echo BASE_URL; ?>/admin/invoices.php"><?php echo $titles['admin/invoices.php']; ?><span class="badge pull-right"><?php echo $_SESSION['invoices_count']; ?></span></a></li>
						<?php if(check_user_abilities_min_admin()): ?>
							<li class="<?php echo ($global['current_url'] == 'admin/users.php') ? 'active' : ''; ?>"><a href="<?php echo BASE_URL; ?>/admin/users.php"><?php echo $titles['admin/users.php']; ?><span class="badge pull-right"><?php echo $_SESSION['users_count']; ?></span></a></li>
						<?php endif; ?>
					</ul>
				</li>
				<?php if(check_user_abilities_min_admin()): ?>
					<li class="dropdown<?php echo $settings_active; ?>">
						<a href="<?php echo BASE_URL; ?>/admin/settings.php" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><?php echo $titles['admin/settings.php']; ?> <span class="caret"></span></a>
						<ul class="dropdown-menu">
							<li class="<?php echo ($global['current_url'] == 'admin/settings.php') ? 'active' : ''; ?>"><a href="<?php echo BASE_URL; ?>/admin/settings.php"><?php echo $titles['admin/settings.php']; ?></a></li>
							<li class="<?php echo ($global['current_url'] == 'admin/sites.php') ? 'active' : ''; ?>"><a href="<?php echo BASE_URL; ?>/admin/sites.php"><?php echo $titles['admin/sites.php']; ?><span class="badge pull-right"><?php echo $_SESSION['sites_count']; ?></span></a></li>
						</ul>
					</li>
				<?php endif; ?>
				<li class="dropdown<?php echo $tools_active; ?>">
					<a href="<?php echo BASE_URL; ?>/admin/tools.php" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><?php echo $titles['admin/tools.php']; ?> <span class="caret"></span></a>
					<ul class="dropdown-menu">
						<li class="<?php echo ($global['current_url'] == 'admin/tools.php') ? 'active' : ''; ?>"><a href="<?php echo BASE_URL; ?>/admin/tools.php"><?php echo $titles['admin/tools.php']; ?></a></li>
						<li class="<?php echo ($global['current_url'] == 'admin/profit-estimate.php') ? 'active' : ''; ?>"><a href="<?php echo BASE_URL; ?>/admin/profit-estimate.php"><?php echo $titles['admin/profit-estimate.php']; ?></a></li>
						<li class="<?php echo ($global['current_url'] == 'admin/label-maker.php') ? 'active' : ''; ?>"><a href="<?php echo BASE_URL; ?>/admin/label-maker.php"><?php echo $titles['admin/label-maker.php']; ?></a></li>
					</ul>
				</li>
				<li class="<?php echo $profile_active; ?>"><a href="<?php echo BASE_URL; ?>/admin/profile.php"><?php echo $titles['admin/profile.php']; ?></a></li>
				<li><a href="<?php echo BASE_URL; ?>/admin/logout.php"><?php echo $titles['admin/logout.php']; ?></a></li>
			</ul>
			<ul id="pc-menu" class="nav navbar-nav navbar-right">
				<li class="<?php echo $dashboard_active; ?>"><a href="<?php echo BASE_URL; ?>/admin/"><?php echo $titles['admin/index.php']; ?></a></li>
				<?php if(check_user_abilities_min_admin()): ?><li class="<?php echo $settings_active; ?>"><a href="<?php echo BASE_URL; ?>/admin/settings.php"><?php echo $titles['admin/settings.php']; ?></a></li><?php endif; ?>
				<li class="<?php echo $profile_active; ?>"><a href="<?php echo BASE_URL; ?>/admin/profile.php"><?php echo $titles['admin/profile.php']; ?></a></li>
				<li class="<?php echo $tools_active; ?>"><a href="<?php echo BASE_URL; ?>/admin/tools.php"><?php echo $titles['admin/tools.php']; ?></a></li>
				<li class="<?php echo ($global['current_url'] == 'admin/logout.php') ? 'active' : ''; ?>"><a href="<?php echo BASE_URL; ?>/admin/logout.php"><?php echo $titles['admin/logout.php']; ?></a></li>
			</ul>
		</div>
	</div>
</nav>
<div class="admin-content container-fluid">