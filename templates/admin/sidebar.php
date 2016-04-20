<?php 

if (!defined('BASE_URL')) {
	header('HTTP/1.0 404 not found');
	exit;
}
?>

<div class="col-sm-3 col-md-2 sidebar">
	<ul class="nav nav-sidebar">
		<li class="<?php echo ($global['current_url'] == 'admin/index.php') ? 'active' : ''; ?>"><a href="<?php echo BASE_URL; ?>/admin/"><?php echo $titles['admin/index.php']; ?></a></li>
		<li class="<?php echo ($global['current_url'] == 'admin/reports.php') ? 'active' : ''; ?>"><a href="<?php echo BASE_URL; ?>/admin/reports.php">- <?php echo $titles['admin/reports.php']; ?></a></li>
		<li class="<?php echo ($global['current_url'] == 'admin/orders.php') ? 'active' : ''; ?>"><a href="<?php echo BASE_URL; ?>/admin/orders.php">- <?php echo $titles['admin/orders.php']; ?><span class="badge pull-right"><?php echo $_SESSION['orders_count']; ?></span></a></li>
		<li class="<?php echo ($global['current_url'] == 'admin/invoices.php') ? 'active' : ''; ?>"><a href="<?php echo BASE_URL; ?>/admin/invoices.php">- <?php echo $titles['admin/invoices.php']; ?><span class="badge pull-right"><?php echo $_SESSION['invoices_count']; ?></span></a></li>
		<?php if($_SESSION['user_role'] == 'superadmin' || $_SESSION['user_role'] == 'admin'): ?>
			<li class="<?php echo ($global['current_url'] == 'admin/users.php') ? 'active' : ''; ?>"><a href="<?php echo BASE_URL; ?>/admin/users.php">- <?php echo $titles['admin/users.php']; ?><span class="badge pull-right"><?php echo $_SESSION['users_count']; ?></span></a></li>
		<?php endif; ?>
	</ul>
	<?php if(check_user_abilities_min_admin()): ?>
		<ul class="nav nav-sidebar">
			<li class="<?php echo ($global['current_url'] == 'admin/settings.php') ? 'active' : ''; ?>"><a href="<?php echo BASE_URL; ?>/admin/settings.php"><?php echo $titles['admin/settings.php']; ?></a></li>
			<li class="<?php echo ($global['current_url'] == 'admin/sites.php') ? 'active' : ''; ?>"><a href="<?php echo BASE_URL; ?>/admin/sites.php">- <?php echo $titles['admin/sites.php']; ?><span class="badge pull-right"><?php echo $_SESSION['sites_count']; ?></span></a></li>
		</ul>
	<?php endif; ?>
	<ul class="nav nav-sidebar">
		<li class="<?php echo ($global['current_url'] == 'admin/tools.php') ? 'active' : ''; ?>"><a href="<?php echo BASE_URL; ?>/admin/tools.php"><?php echo $titles['admin/tools.php']; ?></a></li>
		<li class="<?php echo ($global['current_url'] == 'admin/profit-estimate.php') ? 'active' : ''; ?>"><a href="<?php echo BASE_URL; ?>/admin/profit-estimate.php">- <?php echo $titles['admin/profit-estimate.php']; ?></a></li>
		<li class="<?php echo ($global['current_url'] == 'admin/label-maker.php') ? 'active' : ''; ?>"><a href="<?php echo BASE_URL; ?>/admin/label-maker.php">- <?php echo $titles['admin/label-maker.php']; ?></a></li>
	</ul>
	<ul class="nav nav-sidebar">
		<li class="<?php echo ($global['current_url'] == 'admin/profile.php') ? 'active' : ''; ?>"><a href="<?php echo BASE_URL; ?>/admin/profile.php"><?php echo $titles['admin/profile.php']; ?></a></li>
	</ul>
</div>