<?php 
if (!defined('BASE_URL')) {
	header('HTTP/1.0 404 not found');
	exit;
}
?>

	</div>
	<footer class="footer">
		<div class="container-fluid">
			<div class="row">
				<div class="col-sm-9 col-md-9 col-lg-10 col-sm-offset-3 col-md-offset-2 col-lg-offset-2">
					<p class="text-muted">&copy; 2015 - <?php echo date('Y');?> | Ulvemosens Handelsselskab ApS </p>
				</div>
			</div>
		</div>
	</footer>

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.0/jquery.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>
	<script src="https://cdn.datatables.net/1.10.11/js/jquery.dataTables.min.js"></script>
	<script src="https://cdn.datatables.net/1.10.11/js/dataTables.bootstrap.min.js"></script>

	<?php if ($global['current_url'] == 'admin/reports.php') : ?>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/1.0.2/Chart.min.js"></script>
	<?php endif; ?>
	
	<script src="<?php echo STATIC_URL;?>/js/main.js"></script>
	<?php if ($global['current_url'] == 'admin/reports.php') : ?>
		<script src="<?php echo STATIC_URL;?>/js/reports.js"></script>
	<?php endif; ?>
	
	</body>
</html>