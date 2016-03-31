$(document).ready(function() {
	//$("#myTable").tablesorter().tablesorterPager({container: $("#pager")});
	$('#select_all').change(function() {
		var checkboxes = $(this).closest('form').find(':checkbox');
		if($(this).is(':checked')) {
			checkboxes.prop('checked', true);
		} else {
			checkboxes.prop('checked', false);
		}
	});

	var submit = false;

	$('#pull_orders_form').submit(function () {
        if (submit) { return false; }
        else { submit = true;}
    });
});

//https://datatables.net/examples/styling/bootstrap.html
//https://github.com/drvic10k/bootstrap-sortable
//http://issues.wenzhixin.net.cn/bootstrap-table/index.html