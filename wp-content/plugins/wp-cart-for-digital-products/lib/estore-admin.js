jQuery(document).ready(function($){
    //Add date picker listener on date fields
	if ($.fn.datepicker){
		$('.estore_date').datepicker({
	        dateFormat : 'yy-mm-dd'
	    });
	}
});