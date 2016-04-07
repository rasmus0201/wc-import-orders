$(document).ready(function() {
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

