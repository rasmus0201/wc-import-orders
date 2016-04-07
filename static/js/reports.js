Chart.defaults.global = {
	// Boolean - Whether to animate the chart
	animation: true,

	// Number - Number of animation steps
	animationSteps: 45,

	// String - Animation easing effect
	// Possible effects are:
	// [easeInOutQuart, linear, easeOutBounce, easeInBack, easeInOutQuad,
	//  easeOutQuart, easeOutQuad, easeInOutBounce, easeOutSine, easeInOutCubic,
	//  easeInExpo, easeInOutBack, easeInCirc, easeInOutElastic, easeOutBack,
	//  easeInQuad, easeInOutExpo, easeInQuart, easeOutQuint, easeInOutCirc,
	//  easeInSine, easeOutExpo, easeOutCirc, easeOutCubic, easeInQuint,
	//  easeInElastic, easeInOutSine, easeInOutQuint, easeInBounce,
	//  easeOutElastic, easeInCubic]
	animationEasing: "easeOutQuart",

	// Boolean - If we should show the scale at all
	showScale: true,

	// Boolean - If we want to override with a hard coded scale
	scaleOverride: false,

	// ** Required if scaleOverride is true **
	// Number - The number of steps in a hard coded scale
	scaleSteps: null,
	// Number - The value jump in the hard coded scale
	scaleStepWidth: null,
	// Number - The scale starting value
	scaleStartValue: null,

	// String - Colour of the scale line
	scaleLineColor: "rgba(0,0,0,.1)",

	// Number - Pixel width of the scale line
	scaleLineWidth: 1,

	// Boolean - Whether to show labels on the scale
	scaleShowLabels: true,

	// Interpolated JS string - can access value
	scaleLabel: "<%=value%>",

	// Boolean - Whether the scale should stick to integers, not floats even if drawing space is there
	scaleIntegersOnly: true,

	// Boolean - Whether the scale should start at zero, or an order of magnitude down from the lowest value
	scaleBeginAtZero: false,

	// String - Scale label font declaration for the scale label
	scaleFontFamily: "'Helvetica Neue', 'Helvetica', 'Arial', sans-serif",

	// Number - Scale label font size in pixels
	scaleFontSize: 12,

	// String - Scale label font weight style
	scaleFontStyle: "normal",

	// String - Scale label font colour
	scaleFontColor: "#666",

	// Boolean - whether or not the chart should be responsive and resize when the browser does.
	responsive: true,

	// Boolean - whether to maintain the starting aspect ratio or not when responsive, if set to false, will take up entire container
	maintainAspectRatio: true,

	// Boolean - Determines whether to draw tooltips on the canvas or not
	showTooltips: true,

	// Function - Determines whether to execute the customTooltips function instead of drawing the built in tooltips (See [Advanced - External Tooltips](#advanced-usage-custom-tooltips))
	customTooltips: false,

	// Array - Array of string names to attach tooltip events
	tooltipEvents: ["mousemove", "touchstart", "touchmove"],

	// String - Tooltip background colour
	tooltipFillColor: "rgba(0,0,0,0.8)",

	// String - Tooltip label font declaration for the scale label
	tooltipFontFamily: "'Helvetica Neue', 'Helvetica', 'Arial', sans-serif",

	// Number - Tooltip label font size in pixels
	tooltipFontSize: 14,

	// String - Tooltip font weight style
	tooltipFontStyle: "normal",

	// String - Tooltip label font colour
	tooltipFontColor: "#fff",

	// String - Tooltip title font declaration for the scale label
	tooltipTitleFontFamily: "'Helvetica Neue', 'Helvetica', 'Arial', sans-serif",

	// Number - Tooltip title font size in pixels
	tooltipTitleFontSize: 14,

	// String - Tooltip title font weight style
	tooltipTitleFontStyle: "bold",

	// String - Tooltip title font colour
	tooltipTitleFontColor: "#fff",

	// Number - pixel width of padding around tooltip text
	tooltipYPadding: 6,

	// Number - pixel width of padding around tooltip text
	tooltipXPadding: 6,

	// Number - Size of the caret on the tooltip
	tooltipCaretSize: 8,

	// Number - Pixel radius of the tooltip border
	tooltipCornerRadius: 6,

	// Number - Pixel offset from point x to tooltip edge
	tooltipXOffset: 10,

	// String - Template string for single tooltips
	//tooltipTemplate: "<%if (label){%><%=label%>: <%}%><%= value %>",
	tooltipTemplate: "<%= addCommas(value) %>",

	// String - Template string for multiple tooltips
	multiTooltipTemplate: "<%= addCommas(value) %>", //<%= datasetLabel %>: 

	legendTemplate: "<ul class=\"<%=name.toLowerCase()%>-legend\"><% for (var i=0; i<segments.length; i++){%><li><span style=\"background-color:<%=segments[i].fillColor%>\"></span><%if(segments[i].label){%><%=segments[i].label%><%}%></li><%}%></ul>",

	// Function - Will fire on animation progression.
	onAnimationProgress: function(){},

	// Function - Will fire on animation completion.
	onAnimationComplete: function(){}
};

/*Chart.defaults.global.customTooltips = function(tooltip) {
	var tooltipEl = $('#chartjs-tooltip');

	if (!tooltip) {
		tooltipEl.css({
			opacity: 0
		});
		return;
	}

	tooltipEl.removeClass('above below');
	tooltipEl.addClass(tooltip.yAlign);

	var innerHtml = '';
	for (var i = tooltip.labels.length - 1; i >= 0; i--) {
		innerHtml += [
			'<div class="chartjs-tooltip-section">',
			'	<span class="chartjs-tooltip-key" style="background-color:' + tooltip.legendColors[i].fill + '"></span>',
			'	<span class="chartjs-tooltip-value">' + tooltip.labels[i] + '</span>',
			'</div>'
		].join('');
	}

	tooltipEl.html(innerHtml);
	tooltipEl.css({
		opacity: 1,
		left: tooltip.chart.canvas.offsetLeft + tooltip.x + 'px',
		top: tooltip.chart.canvas.offsetTop + tooltip.y + 'px',
		fontFamily: tooltip.fontFamily,
		fontSize: tooltip.fontSize,
		fontStyle: tooltip.fontStyle,
	});
};*/

$(document).ready(function() {
	var ctx_line = document.getElementById('reports-chart').getContext('2d');

	var ctx_revenue_pie = document.getElementById('revenue-chart').getContext('2d');
	var ctx_shipping_pie = document.getElementById('shipping-chart').getContext('2d');
	var ctx_fee_pie = document.getElementById('fee-chart').getContext('2d');
	var ctx_discount_pie = document.getElementById('discount-chart').getContext('2d');
	var ctx_tax_pie = document.getElementById('tax-chart').getContext('2d');
	var ctx_orders_pie = document.getElementById('orders-chart').getContext('2d');

	window.myLine = new Chart(ctx_line).Line(lineChartData, {
		responsive: true,
		bezierCurve: false,
		scaleShowVerticalLines: false,
	});

	var legend = myLine.generateLegend();
	$("#line-legend").html(legend);

	window.myPie = new Chart(ctx_revenue_pie).Pie(revenuePieData, {responsive: true, animationSteps: 30, animationEasing: "linear"});
	window.myPie = new Chart(ctx_shipping_pie).Pie(shippingPieData, {responsive: true, animationSteps: 30, animationEasing: "linear"});
	window.myPie = new Chart(ctx_fee_pie).Pie(feePieData, {responsive: true, animationSteps: 30, animationEasing: "linear"});
	window.myPie = new Chart(ctx_discount_pie).Pie(discountPieData, {responsive: true, animationSteps: 30, animationEasing: "linear"});
	window.myPie = new Chart(ctx_tax_pie).Pie(taxPieData, {responsive: true, animationSteps: 30, animationEasing: "linear"});
	window.myPie = new Chart(ctx_orders_pie).Pie(ordersPieData, {responsive: true, animationSteps: 30, animationEasing: "linear"});
});

function addCommas(nStr){
	nStr += '';
	x = nStr.split('.');
	x1 = x[0];
	x2 = x.length > 1 ? ',' + x[1] : '';
	var rgx = /(\d+)(\d{3})/;
	while (rgx.test(x1)) {
		x1 = x1.replace(rgx, '$1' + '.' + '$2');
	}
	return x1 + x2;
}


