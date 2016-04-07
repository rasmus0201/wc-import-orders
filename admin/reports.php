<?php

require '../app/db.php';
require '../app/init.php';


if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
	header('Location: '.BASE_URL);
	exit;
}

if (isset($_POST['this_year'])) {
	$min_date = date('Y-m-d', strtotime('-1 year'));
	$max_date = date('Y-m-d');
	$sorting_method = 'month';
} else if (isset($_POST['last_month'])) {
	$min_date = date('Y-m-d', strtotime('first day of last month'));
	$max_date = date('Y-m-d', strtotime('last day of last month'));
	$sorting_method = 'month';
} else if (isset($_POST['last_week'])) {
	$min_date = date('Y-m-d', strtotime('last week') );
	$max_date = date('Y-m-d', strtotime(closestDate('monday'))-1 );
	$sorting_method = 'day';
} else if (isset($_POST['this_month'])) {
	$min_date = date('Y-m-d', strtotime('-31 days'));
	$max_date = date('Y-m-d');
	$sorting_method = 'day';
} else if (isset($_POST['this_week'])) {
	$min_date = date('Y-m-d', strtotime(closestDate('monday')) );
	$max_date = date('Y-m-d', strtotime(closestDate('sunday'))+86400 );
	$sorting_method = 'day';
} else if (isset($_POST['from_date']) && isset($_POST['to_date'])) {
	#POST custom date report
	if (!empty($_POST['from_date']) && !empty($_POST['to_date'])) {
		$min_date = date('Y-m-d', strtotime($_POST['from_date']));
		$max_date = date('Y-m-d', strtotime($_POST['to_date']));

		$days_between = (int)ceil(abs(strtotime($max_date) - strtotime($min_date)) / 86400);

		if ($days_between > 89) {
			$sorting_method = 'month';
			if ($days_between > 365*3) {
				$sorting_method = 'year';
			}
		} else {
			$sorting_method = 'day';
		}
	} else {
		$min_date = date('Y-m-d', strtotime('-6 days'));
		$max_date = date('Y-m-d');
		$sorting_method = 'day';
	}
} else {
	$min_date = date('Y-m-d', strtotime(closestDate('monday')) );
	$max_date = date('Y-m-d', strtotime(closestDate('sunday'))+86400 );
	$sorting_method = 'day';
}

$reports = make_reports($min_date, $max_date, $sorting_method);
$total_orders = make_reports($min_date, $max_date, $sorting_method, true);

$total = 0;
$total_shipping = 0;
$fee = 0;
$total_discount = 0;
$total_tax = 0;

foreach($reports as $key => $data) {
	$total += $data['total'];
	$total_shipping += $data['total_shipping'];
	$fee += $data['fee'];
	$total_discount += $data['total_discount'];
	$total_tax += $data['total_tax'];
}

require '../templates/admin/header.php';

?>

<div class="row">
	<?php require '../templates/admin/sidebar.php'; ?>
	<div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
		<h1 class="page-header"><?php echo $titles['admin/index.php'].' / '.$global['site_title']; ?></h1>
		<div class="col-sm-12">
			<form class="form-horizontal" method="post">
				<div class="form-group">
					<div class="btn-group" role="group" id="reports_btn_group_1">
						<input class="btn btn-default" type="submit" name="this_year" value="Seneste år">
						<input class="btn btn-default" type="submit" name="last_month" value="Sidste måned">
						<input class="btn btn-default" type="submit" name="this_month" value="Sidste 30 dage">
					</div>
					<div class="btn-group" role="group" id="reports_btn_group_2">
						<input class="btn btn-default" type="submit" name="last_week" value="Sidste uge">
						<input class="btn btn-default" type="submit" name="this_week" value="Denne uge">
					</div>
				</div>
				<div class="form-group">
					<div class="btn-group" role="group" id="reports_btn_group_3">
						<input class="btn btn-default reports_custom_date" type="date" name="from_date">
						<input class="btn btn-default reports_custom_date" type="date" name="to_date">
						<input class="btn btn-default" type="submit" name="custom_date_submit" value="Søg">
					</div>
				</div>
			</form>
		</div>
		<div class="col-sm-12">
			<div class="reports-legend">
				<div id="line-legend"></div>
			</div>
		</div>
		<div class="col-sm-12">
			<div class="table-responsive">
				<canvas id="reports-chart"></canvas>
				<div id="chartjs-tooltip"></div>
			</div>
		</div>
		<div class="col-sm-12 total-report-charts">
			<hr class="seperator">
			<div class="col-sm-12 pie-legend-wrap">
				<div class="reports-legend">
					<div id="pie-legend">
						<ul class="pie-legend">
							<li>
								<span style="background-color:rgba(52,152,219,1)"></span>Omsætning
							</li>
							<li>
								<span style="background-color:rgba(46,204,113,1)"></span>Fragt
							</li>
							<li>
								<span style="background-color:rgba(52,73,94,1)"></span>Gebyr
							</li>
							<li>
								<span style="background-color:rgba(231,76,60,1)"></span>Rabat
							</li>
							<li>
								<span style="background-color:rgba(241,196,15,1)"></span>Moms
							</li>
							<li>
								<span style="background-color:rgba(149,165,166,1)"></span>Antal ordre
							</li>
						</ul>
					</div>
				</div>
			</div>
			<div class="col-sm-12 col-lg-4 col-md-6">
				<canvas id="revenue-chart"></canvas>
			</div>
			<div class="col-sm-12 col-lg-4 col-md-6">
				<canvas id="shipping-chart"></canvas>
			</div>
			<div class="col-sm-12 col-lg-4 col-md-6">
				<canvas id="fee-chart"></canvas>
			</div>
			<div class="col-sm-12 add-margin-bottom"></div>
			<div class="col-sm-12 col-lg-4 col-md-6">
				<canvas id="discount-chart"></canvas>
			</div>
			<div class="col-sm-12 col-lg-4 col-md-6">
				<canvas id="tax-chart"></canvas>
			</div>
			<div class="col-sm-12 col-lg-4 col-md-6">
				<canvas id="orders-chart"></canvas>
			</div>
		</div>
	</div>
</div>

<script>
	var orderData = {
		'revenue': 		[<?php foreach($reports as $key => $data): ?><?php echo $data['total']; ?>,<?php endforeach; ?>],
		'shipping': 	[<?php foreach($reports as $key => $data): ?><?php echo $data['total_shipping']+$data['shipping_tax']; ?>,<?php endforeach; ?>],
		'fee': 			[<?php foreach($reports as $key => $data): ?><?php echo $data['fee']; ?>,<?php endforeach; ?>],
		'discount':		[<?php foreach($reports as $key => $data): ?><?php echo $data['total_discount']; ?>,<?php endforeach; ?>],
		'tax':			[<?php foreach($reports as $key => $data): ?><?php echo $data['total_tax']; ?>,<?php endforeach; ?>],
	};

	var totalRevenue = <?php echo $total; ?>;
	var totalShipping = <?php echo $total_shipping; ?>;
	var totalFee = <?php echo $fee; ?>;
	var totalDiscount = <?php echo $total_discount; ?>;
	var totalTax = <?php echo $total_tax; ?>;
	var totalOrders = <?php echo $total_orders; ?>;

	var lineChartData = {
		labels : [<?php echo find_label($reports, $sorting_method); ?>],
		datasets : [
			{
				label: "Omsætning",
				fillColor : "rgba(52,152,219,0.2)",
				strokeColor : "rgba(52,152,219,1)",
				pointColor : "rgba(52,152,219,1)",
				pointStrokeColor : "#fff",
				pointHighlightFill : "#fff",
				pointHighlightStroke : "rgba(52,152,219,1)",
				data : orderData.revenue
			},
			{
				label: "Fragt",
				fillColor : "rgba(46,204,113,0.2)",
				strokeColor : "rgba(46,204,113,1)",
				pointColor : "rgba(46,204,113,1)",
				pointStrokeColor : "#fff",
				pointHighlightFill : "#fff",
				pointHighlightStroke : "rgba(46,204,113,1)",
				data : orderData.shipping
			},
			{
				label: "Gebyr",
				fillColor : "rgba(52,73,94,0.2)",
				strokeColor : "rgba(52,73,94,1)",
				pointColor : "rgba(52,73,94,1)",
				pointStrokeColor : "#fff",
				pointHighlightFill : "#fff",
				pointHighlightStroke : "rgba(52,73,94,1)",
				data : orderData.fee
			},
			{
				label: "Rabat",
				fillColor : "rgba(231,76,60,0.2)",
				strokeColor : "rgba(231,76,60,1)",
				pointColor : "rgba(231,76,60,1)",
				pointStrokeColor : "#fff",
				pointHighlightFill : "#fff",
				pointHighlightStroke : "rgba(231,76,60,1)",
				data : orderData.discount
			},
			{
				label: "Moms",
				fillColor : "rgba(241,196,15,0.2)",
				strokeColor : "rgba(241,196,15,1)",
				pointColor : "rgba(241,196,15,1)",
				pointStrokeColor : "#fff",
				pointHighlightFill : "#fff",
				pointHighlightStroke : "rgba(241,196,15,1)",
				data : orderData.tax
			},
		]
	};

	var revenuePieData = [{
		value: totalRevenue,
		color: 'rgba(52,152,219,1)',
		highlight: 'rgba(52,152,219,0.8)',
		label: 'Omsætning'
	}];

	var shippingPieData = [{
		value: totalShipping,
		color: 'rgba(46,204,113,1)',
		highlight: 'rgba(46,204,113,0.8)',
		label: 'Fragt'
	}];

	var feePieData = [{
		value: totalFee,
		color: 'rgba(52,73,94,1)',
		highlight: 'rgba(52,73,94,0.8)',
		label: 'Gebyr'
	}];

	var discountPieData = [{
		value: totalDiscount,
		color: 'rgba(231,76,60,1)',
		highlight: 'rgba(231,76,60,0.8)',
		label: 'Rabat'
	}];

	var taxPieData = [{
		value: totalTax,
		color: 'rgba(241,196,15,1)',
		highlight: 'rgba(241,196,15,0.8)',
		label: 'Moms'
	}];

	var ordersPieData = [{
		value: totalOrders,
		color: 'rgba(149,165,166,1)',
		highlight: 'rgba(149,165,166,0.8)',
		label: 'Antal ordre'
	}];
</script>

<?php

require '../templates/admin/footer.php';

?>