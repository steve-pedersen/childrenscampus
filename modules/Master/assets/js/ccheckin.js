(function ($) {

	// DatePicker 
	$(function(){

	  $.datepicker.setDefaults(
	    $.extend( $.datepicker.regional[ '' ] )
	  );
	  $( '#startDate' ).datepicker({
	    formatDate: 'yy-mm-dd'
	  });
	  $( '#endDate' ).datepicker({
	    formatDate: 'yy-mm-dd'
	  });
	  $( '#openDate' ).datepicker({
	    formatDate: 'yy-mm-dd'
	  });
	  $( '#closeDate' ).datepicker({
	    formatDate: 'yy-mm-dd'
	  });
	  $( '#blockeddatenew' ).datepicker({
	    formatDate: 'yy-mm-dd'
	  });

	});

}(jQuery));