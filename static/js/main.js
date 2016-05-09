$(document).ready(function() {
	var _dataFn = $.fn.data;
    $.fn.data = function(key, val){
        if (typeof val !== 'undefined'){
            $.expr.attrHandle[key] = function(elem){
                return $(elem).attr(key) || $(elem).data(key);
            };
        }
        return _dataFn.apply(this, arguments);
    };

	$('#select_all').change(function() {
		var checkboxes = $(this).closest('form').find(':checkbox');
		if($(this).is(':checked')) {
			checkboxes.prop('checked', true);
		} else {
			checkboxes.prop('checked', false);
		}
	});

	if ($('.admin-profit-estimate').length) {
		$('#buyprice').keyup(function(e){ calculate_profit(); });
		$('#qty').keyup(function(e){ calculate_profit(); });
		$('#saleprice').keyup(function(e){ calculate_profit(); });

		$('#buycurrency').change(function(e){ calculate_profit(); });
		$('#salecurrency').change(function(e){ calculate_profit(); });
	}

	var submit = false;

	$('#pull_orders_form').submit(function () {
        if (submit) { return false; }
        else { submit = true;}
    });

    $('#orders_table').DataTable({
		'language': {
			'lengthMenu': 'Rekorder pr. side _MENU_',
			'search': 'Søg: ',
			'zeroRecords': 'Ingenting fundet.',
			'info': 'Viser side _PAGE_ ud af _PAGES_',
			'infoEmpty':'Ingen rekorder',
			'infoFiltered': '(filtrerede fra _MAX_ antal rekorder)',
			'decimal': ',',
			'thousands': '.',
			'paginate': {
				'previous': 'Forrige',
				'next': 'Næste',
			}
		},
		'stateSave': true,
		'lengthMenu': [[10, 25, 50, -1], [10, 25, 50, 'Alle']],
		'columnDefs': [{
			'searchable': false,
			'orderable': false,
			'targets': 4
		}],
		'order': [[ 0, 'desc' ]]
	});
	
	$('#invoice_table').DataTable({
		'language': {
			'lengthMenu': 'Rekorder pr. side _MENU_',
			'search': 'Søg: ',
			'zeroRecords': 'Ingenting fundet.',
			'info': 'Viser side _PAGE_ ud af _PAGES_',
			'infoEmpty':'Ingen rekorder',
			'infoFiltered': '(filtrerede fra _MAX_ antal rekorder)',
			'decimal': ',',
			'thousands': '.',
			'paginate': {
				'previous': 'Forrige',
				'next': 'Næste',
			}
		},
		'stateSave': true,
		'lengthMenu': [[10, 25, 50, -1], [10, 25, 50, 'Alle']],
		'columnDefs': [{
			'searchable': false,
			'orderable': false,
			'targets': [0,3]
		}],
		'order': [[ 1, 'desc' ]]
	});

	if ($('#order_created_at').length) {
		$('#order_created_at').val(get_current_datetime());
		$('#order_updated_at').val(get_current_datetime());
		$('#order_completed_at').val(get_current_datetime());
	}

	if ($('.admin-orders').length) {
		$('#payment_text_row').hide();
		$('#status').on('change', function(){
			if ($(this).val() == 'proforma') {
				$('#payment_text_row').fadeIn();
				$('#is_paid').val('no');
			} else {
				$('#payment_text_row').fadeOut();
				$('#is_paid').val('yes');
			}
		});
	}

	$('#refund-order').on('click', function(e){
		e.preventDefault();
		console.log(this.href);
		if (confirm('Er du sikker?')) {
			window.location.href = this.href;
		}
	});
	$('#complete-order').on('click', function(e){
		e.preventDefault();
		if (confirm('Er du sikker?')) {
			window.location.href = this.href;
		}
	});
	$('#delete-order').on('click', function(e){
		e.preventDefault();
		if (confirm('Er du sikker?')) {
			window.location.href = this.href;
		}
	});
});

function calculate_profit() {
	var profit_percent;
	var buy = $('#buyprice').val();
	var sale = $('#saleprice').val();
	var qty = $('#qty').val();

	var buy_cur = $('#buycurrency').val();
	var sale_cur = $('#salecurrency').val();

	var percent = $('#avance_percent');
	var bucks = $('#avance_bucks');

	if (buy_cur != sale_cur) {
		buy = calculate_currency(buy_cur, buy);
	}

	if (qty !== undefined && qty !== '') {
		profit_percent = ( ( parseInt(sale*0.8, 10) - parseInt(buy/qty, 10) ) / parseInt(sale*0.8, 10) ) * 100;

		if (!isNaN(profit_percent) && profit_percent != 'Infinity' && profit_percent != '-Infinity' ) {
			percent.val( profit_percent+'%' );
			bucks.val( Math.round(sale*0.8 - buy/qty) );
		} else {
			percent.val( '0%' );
			bucks.val( '0' );
		}
	} else {
		profit_percent = ( ( parseInt(sale*0.8, 10) - parseInt(buy, 10) ) / parseInt(sale*0.8, 10) ) * 100;

		if (!isNaN(profit_percent) && profit_percent != 'Infinity' && profit_percent != '-Infinity' ) {
			percent.val( profit_percent+'%' );
			bucks.val( Math.round(sale*0.8 - buy) );
		} else {
			percent.val( '0%' );
			bucks.val( '0' );
		}
	}

	return true;
}

function calculate_currency(from, amount){
	if (from == 'GBP') {
		//gbp to dkk
		return (1/currencies['GBP'])*amount;
	} else if (from == 'EUR') {
		//eur to dkk
		return (1/currencies['EUR'])*amount;
	} else if (from == 'USD') {
		//usd to dkk
		return (1/currencies['USD'])*amount;
	} else {
		//dkk to dkk = no change
		return amount;
	}
}


function calculate_line(line){
	line = $(line);
	var row = line.attr('data-row');
	var id = line.attr('data-id');
	var name = line.attr('data-name');
	var val = line.val();
	var total_line_items = 0;
	var full_subtotal = 0;
	var full_total_tax = 0;
	var full_total = 0;
	var full_discount = 0;

	var qty_price, qty, subtotal, total_tax, total;

	if (row == 'product') {
		qty_price = $('.product_row input[data-id='+id+'][data-name="qty_price"]').val();
		qty = $('.product_row input[data-id='+id+'][data-name="qty"]').val();
		subtotal = $('.product_row input[data-id='+id+'][data-name="subtotal"]');
		total_tax = $('.product_row input[data-id='+id+'][data-name="total_tax"]');
		total = $('.product_row input[data-id='+id+'][data-name="total"]');

		if (qty === '' || qty === '0' || qty === 0) {
			qty = 1;

			$('.product_row input[data-id='+id+'][data-name="qty"]').val(qty);
		}

		subtotal.val( (qty_price*qty).toFixed(2) );
		total_tax.val( ((qty_price*qty*1.25) - (qty_price*qty)).toFixed(2) );
		total.val( (qty_price*qty*1.25).toFixed(2) );
	} else if (row == 'shipping') {
		subtotal = $('.shipping_row input[data-id='+id+'][data-name="subtotal"]').val();
		total_tax = $('.shipping_row input[data-id='+id+'][data-name="total_tax"]').val();
		total = $('.shipping_row input[data-id='+id+'][data-name="total"]');

		total.val( ((+subtotal) + (+total_tax)).toFixed(2) );
	} else if (row == 'fee') {
		subtotal = $('.fee_row input[data-id='+id+'][data-name="subtotal"]').val();
		total_tax = $('.fee_row input[data-id='+id+'][data-name="total_tax"]').val();
		total = $('.fee_row input[data-id='+id+'][data-name="total"]');

		total.val( ((+subtotal) + (+total_tax)).toFixed(2) );
	} else if (row == 'discount') {
	}

	$('input[data-name="qty"]').each(function(){
		total_line_items = (+$(this).val()) + (+total_line_items);
	});
	$('input[data-name="subtotal"]').each(function(){
		full_subtotal = (+$(this).val()) + (+full_subtotal);
	});
	$('input[data-name="total_tax"]').each(function(){
		full_total_tax = (+$(this).val()) + (+full_total_tax);
	});
	$('input[data-name="total"]').each(function(){
		full_total = (+$(this).val()) + (+full_total);
	});
	$('input[data-name="amount"]').each(function(){
		full_discount = (+$(this).val()) + (+full_discount);
	});

	full_subtotal = (+full_subtotal) - (+full_discount);

	full_total_tax = full_total_tax - (full_discount*1.25*0.2);

	full_total = full_subtotal + full_total_tax;

	$('input#discount_subtotal').val( full_discount.toFixed(2) );
	$('input#discount_total_tax').val( (full_discount*1.25*0.2).toFixed(2) );
	$('input#total_discount').val( (full_discount*1.25).toFixed(2) );

	$('input#total_line_items').val( total_line_items );
	$('input#subtotal').val( (full_subtotal ).toFixed(2) );
	$('input#total_tax').val( full_total_tax.toFixed(2) );
	$('input#total_price').val( full_total.toFixed(2) );
}

function add_line(line){
	line = $(line);
	var name = line.attr('data-name');
	var row_el, total_line_items = 0;

	var last_row = $('.'+name+'_row').get($('.'+name+'_row').length-1);

	last_row = $(last_row).find('td').get(1);

	var next_id = +$(last_row).find('input').attr('data-id') + 1;

	if (isNaN(next_id)) {
		next_id = 0;
	}

	if (name == 'product') {
		row_el = "<tr class=\"product_row\"><td>Produkt:</td><td><input class=\"form-control\" data-row=\"product\" data-id="+next_id+" data-name=\"name\" name=\"product["+next_id+"][name]\" type=\"text\"></td><td><input class=\"form-control\" pattern=\"[0-9]+([\\.,][0-9]+)?\" step=\"0.01\" oninput=\"calculate_line(this)\" min=\"0\" data-row=\"product\" data-id="+next_id+" data-name=\"qty_price\" name=\"product["+next_id+"][qty_price]\" type=\"number\"></td><td><input class=\"form-control\" oninput=\"calculate_line(this)\" min=\"1\" data-row=\"product\" data-id="+next_id+" data-name=\"qty\" name=\"product["+next_id+"][qty]\" type=\"number\" value=\"1\"></td><td><input readonly class=\"form-control\" min=\"0\" data-row=\"product\" data-id="+next_id+" data-name=\"subtotal\" name=\"product["+next_id+"][subtotal]\" type=\"number\"></td><td><input readonly class=\"form-control\" data-row=\"product\" data-id="+next_id+" data-name=\"total_tax\" name=\"product["+next_id+"][total_tax]\" type=\"number\"></td><td><input readonly class=\"form-control\" data-row=\"product\" data-id="+next_id+" data-name=\"total\" name=\"product["+next_id+"][total]\" type=\"number\"></td></tr>";
	} else if (name == 'shipping') {
		row_el = "<tr class=\"shipping_row\"><td>Fragt:</td><td><input class=\"form-control\" data-row=\"shipping\" data-id="+next_id+" data-name=\"name\" name=\"shipping["+next_id+"][name]\" type=\"text\"></td><td></td><td></td><td><input class=\"form-control\" pattern=\"[0-9]+([\\.,][0-9]+)?\" step=\"0.01\" oninput=\"calculate_line(this)\" min=\"0\" data-row=\"shipping\" data-id="+next_id+" data-name=\"subtotal\" name=\"shipping["+next_id+"][subtotal]\" type=\"number\"></td><td><input class=\"form-control\" pattern=\"[0-9]+([\\.,][0-9]+)?\" step=\"0.01\" oninput=\"calculate_line(this)\" min=\"0\" data-row=\"shipping\" data-id="+next_id+" data-name=\"total_tax\" name=\"shipping["+next_id+"][total_tax]\" type=\"number\"></td><td><input readonly class=\"form-control\" data-row=\"shipping\" data-id="+next_id+" data-name=\"total\" name=\"shipping["+next_id+"][total]\" type=\"number\"></td></tr>";
	} else if (name == 'fee') {
		row_el = "<tr class=\"fee_row\"><td>Gebyr:</td><td><input class=\"form-control\" data-row=\"fee\" data-id="+next_id+" data-name=\"name\" name=\"fee["+next_id+"][name]\" type=\"text\"></td><td></td><td></td><td><input class=\"form-control\" pattern=\"[0-9]+([\\.,][0-9]+)?\" step=\"0.01\" oninput=\"calculate_line(this)\" min=\"0\" data-row=\"fee\" data-id="+next_id+" data-name=\"subtotal\" name=\"fee["+next_id+"][subtotal]\" type=\"number\"></td><td><input class=\"form-control\" pattern=\"[0-9]+([\\.,][0-9]+)?\" step=\"0.01\" oninput=\"calculate_line(this)\" min=\"0\" data-row=\"fee\" data-id="+next_id+" data-name=\"total_tax\" name=\"fee["+next_id+"][total_tax]\" type=\"number\"></td><td><input readonly class=\"form-control\" data-row=\"fee\" data-id="+next_id+" data-name=\"total\" name=\"fee["+next_id+"][total]\" type=\"number\"></td></tr>";
	} else if (name == 'discount') {
		row_el = "<tr class=\"discount_row\"><td>Rabat:</td><td><input class=\"form-control\" data-row=\"discount\" data-id="+next_id+" data-name=\"name\" name=\"discount["+next_id+"][name]\" type=\"text\"></td><td><input class=\"form-control\" pattern=\"[0-9]+([\\.,][0-9]+)?\" step=\"0.01\" oninput=\"calculate_line(this)\" min=\"0\" data-row=\"discount\" data-id="+next_id+" data-name=\"amount\" name=\"discount["+next_id+"][amount]\" type=\"number\"><td><td></td><td></td></td><td></td></tr>";
	}

	$(row_el).appendTo('#orderlines_table tbody');

	$('input[data-name="qty"]').each(function(){
		total_line_items = (+$(this).val()) + (+total_line_items);
	});
	$('input#total_line_items').val( total_line_items );
}

function remove_line(line){
	var name = $(line).attr('data-name');
	var total_line_items = 0;
	var full_subtotal = 0;
	var full_total_tax = 0;
	var full_total = 0;
	var full_discount = 0;

	if ($('.'+name+'_row').last().length === 0) {
		return;
	} else {
		$('.'+name+'_row').last().remove();
			$('input[data-name="qty"]').each(function(){
			total_line_items = (+$(this).val()) + (+total_line_items);
		});

		$('input[data-name="subtotal"]').each(function(){
			full_subtotal = (+$(this).val()) + (+full_subtotal);
		});
		$('input[data-name="total_tax"]').each(function(){
			full_total_tax = (+$(this).val()) + (+full_total_tax);
		});
		$('input[data-name="total"]').each(function(){
			full_total = (+$(this).val()) + (+full_total);
		});
		$('input[data-name="amount"]').each(function(){
			full_discount = (+$(this).val()) + (+full_discount);
		});

		full_subtotal = (+full_subtotal) - (+full_discount);

		full_total_tax = full_total_tax - (full_discount*1.25*0.2);

		full_total = full_subtotal + full_total_tax;

		$('input#total_discount').val( full_discount.toFixed(2) );
		$('input#total_line_items').val( total_line_items );
		$('input#subtotal').val( (full_subtotal ).toFixed(2) );
		$('input#total_tax').val( full_total_tax.toFixed(2) );
		$('input#total_price').val( full_total.toFixed(2) );
	}
}

function get_current_datetime() {
	now = new Date();
	year = "" + now.getFullYear();
	month = "" + (now.getMonth() + 1); if (month.length == 1) { month = "0" + month; }
	day = "" + now.getDate(); if (day.length == 1) { day = "0" + day; }
	hour = "" + now.getHours(); if (hour.length == 1) { hour = "0" + hour; }
	minute = "" + now.getMinutes(); if (minute.length == 1) { minute = "0" + minute; }
	second = "" + now.getSeconds(); if (second.length == 1) { second = "0" + second; }
	return year + "-" + month + "-" + day + " " + hour + ":" + minute + ":" + second;
}