<?php 

if (!defined('BASE_URL')) {
	header('HTTP/1.0 404 not found');
	exit;
}
/* <span class="sr-only">(aktuelle)</span>*/
?>

<div class="col-sm-3 col-md-2 sidebar">
	<ul class="nav nav-sidebar">
		<li class="<?php echo ($global['current_url'] == 'admin/index.php') ? 'active' : ''; ?>"><a href="<?php echo BASE_URL; ?>admin/"><?php echo $titles['admin/index.php']; ?></a></li>
		<li class="<?php echo ($global['current_url'] == 'admin/reports.php') ? 'active' : ''; ?>"><a href="<?php echo BASE_URL; ?>admin/reports.php"><?php echo $titles['admin/reports.php']; ?></a></li>
		<li class="<?php echo ($global['current_url'] == 'admin/orders.php') ? 'active' : ''; ?>"><a href="<?php echo BASE_URL; ?>admin/orders.php"><?php echo $titles['admin/orders.php']; ?></a></li>
		<li class="<?php echo ($global['current_url'] == 'admin/invoices.php') ? 'active' : ''; ?>"><a href="<?php echo BASE_URL; ?>admin/invoices.php"><?php echo $titles['admin/invoices.php']; ?></a></li>
		
	</ul>
	<ul class="nav nav-sidebar">
		<li class="<?php echo ($global['current_url'] == 'admin/sites.php') ? 'active' : ''; ?>"><a href="<?php echo BASE_URL; ?>admin/sites.php"><?php echo $titles['admin/sites.php']; ?></a></li>
	</ul>
</div>