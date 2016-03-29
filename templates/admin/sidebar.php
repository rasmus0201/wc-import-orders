<?php 

if (!defined('BASE_URL')) {
	header('HTTP/1.0 404 not found');
	exit;
}
/* <span class="sr-only">(aktuelle)</span>*/
?>

<div class="col-sm-3 col-md-2 sidebar">
	<ul class="nav nav-sidebar">
		<li class="<?php echo ($global['current_url'] == 'admin/index.php') ? 'active' : ''; ?>"><a href="<?php echo BASE_URL; ?>/admin/"><?php echo $titles['admin/index.php']; ?></a></li>
		<li class="<?php echo ($global['current_url'] == 'admin/reports.php') ? 'active' : ''; ?>"><a href="<?php echo BASE_URL; ?>/admin/reports.php">- <?php echo $titles['admin/reports.php']; ?></a></li>
		<li class="<?php echo ($global['current_url'] == 'admin/orders.php') ? 'active' : ''; ?>"><a href="<?php echo BASE_URL; ?>/admin/orders.php">- <?php echo $titles['admin/orders.php']; ?><span class="badge pull-right"><?php echo $_SESSION['orders_count']; ?></span></a></li>
		<li class="<?php echo ($global['current_url'] == 'admin/invoices.php') ? 'active' : ''; ?>"><a href="<?php echo BASE_URL; ?>/admin/invoices.php">- <?php echo $titles['admin/invoices.php']; ?><span class="badge pull-right"><?php echo $_SESSION['invoices_count']; ?></span></a></li>
		
	</ul>
	<?php if(check_user_abilities_min_admin()): ?>
		<ul class="nav nav-sidebar">
			<li class="<?php echo ($global['current_url'] == 'admin/settings.php') ? 'active' : ''; ?>"><a href="<?php echo BASE_URL; ?>/admin/settings.php"><?php echo $titles['admin/settings.php']; ?></a></li>
			<li class="<?php echo ($global['current_url'] == 'admin/sites.php') ? 'active' : ''; ?>"><a href="<?php echo BASE_URL; ?>/admin/sites.php">- <?php echo $titles['admin/sites.php']; ?><span class="badge pull-right"><?php echo $_SESSION['sites_count']; ?></span></a></li>
		</ul>
	<?php endif; ?>
	<ul class="nav nav-sidebar">
		<li class="<?php echo ($global['current_url'] == 'admin/profile.php') ? 'active' : ''; ?>"><a href="<?php echo BASE_URL; ?>/admin/profile.php"><?php echo $titles['admin/profile.php']; ?></a></li>
		<?php if($_SESSION['user_role'] == 'superadmin' || $_SESSION['user_role'] == 'admin'): ?>
			<li class="<?php echo ($global['current_url'] == 'admin/users.php') ? 'active' : ''; ?>"><a href="<?php echo BASE_URL; ?>/admin/users.php">- <?php echo $titles['admin/users.php']; ?></a></li>
		<?php endif; ?>
	</ul>
</div>