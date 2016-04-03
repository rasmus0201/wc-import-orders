<?php
require '../app/db.php';
require '../app/init.php';

if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
	header('Location: '.BASE_URL);
	exit;
}

$qry_str = "?base=DKK";
$ch = curl_init();

// Set query data here with the URL
curl_setopt($ch, CURLOPT_URL, 'http://api.fixer.io/latest' . $qry_str); 

curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_TIMEOUT, '3');
$content = trim(curl_exec($ch));
curl_close($ch);

$currencies = json_decode($content, true)['rates'];

require '../templates/admin/header.php';
?>

<div class="row">
	<?php require '../templates/admin/sidebar.php'; ?>
	<div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
		<h1 class="page-header"><?php echo $titles['admin/tools.php'].' / '.$global['site_title']; ?></h1>
		<form class="form-horizontal" action="post">
			<div class="form-group <?php echo (isset($form_error)) ? 'has-error' : '' ;?>">
				<label for="buyprice" class="col-sm-2 control-label">Indkøbspris</label>
				<div class="col-sm-8">
					<input type="number" id="buyprice" placeholder="Pris ekskl. moms" class="form-control">
				</div>
				<div class="col-sm-2">
					<select class="form-control" id="buycurrency">
						<option value="DKK">DKK</option>
						<option value="USD">USD</option>
						<option value="GBP">GBP</option>
						<option value="EUR">EUR</option>
					</select>
				</div>
			</div>
			<div class="form-group">
				<label for="qty" class="col-sm-2 control-label">Stk. pr. kasse</label>
				<div class="col-sm-8">
					<input type="number" id="qty" placeholder="0" class="form-control">
				</div>
			</div>
			<div class="form-group <?php echo (isset($form_error)) ? 'has-error' : '' ;?>">
				<label for="saleprice" class="col-sm-2 control-label">Salgspris pr. stk.</label>
				<div class="col-sm-8">
					<input type="number" id="saleprice" placeholder="Pris inkl. moms" class="form-control">
				</div>
				<div class="col-sm-2">
					<select disabled class="form-control" id="salecurrency">
						<option value="DKK">DKK</option>
						<option value="USD">USD</option>
						<option value="GBP">GBP</option>
						<option value="EUR">EUR</option>
					</select>
				</div>
			</div>
			<hr class="seperator">
			<div class="form-group">
				<label for="avance_percent" class="col-sm-2 control-label"> = Avance i %</label>
				<div class="col-sm-8">
					<input disabled type="text" id="avance_percent" placeholder="0%" class="form-control">
				</div>
			</div>
			<div class="form-group">
				<label for="avance_bucks" class="col-sm-2 control-label"> = Avance</label>
				<div class="col-sm-8">
					<input disabled type="text" id="avance_bucks" placeholder="0" class="form-control">
				</div>
				<div class="col-sm-2">
					<select disabled class="form-control" id="profitcurrency">
						<option value="DKK">DKK</option>
						<option value="USD">USD</option>
						<option value="GBP">GBP</option>
						<option value="EUR">EUR</option>
					</select>
				</div>
			</div>
		</form>
	</div>
</div>

<script>
	<?php if($currencies): ?>
		var currencies = {
			'DKK': 1,
			'GBP': <?php echo $currencies['GBP']; ?>,
			'EUR': <?php echo $currencies['EUR']; ?>,
			'USD': <?php echo $currencies['USD']; ?>,
		};
	<?php else: ?>
		var currencies = {
			'DKK': 1,
			'GBP': 1,
			'EUR': 1,
			'USD': 1,
		};
	<?php endif; ?>
</script>

<?php

require '../templates/admin/footer.php';

?>