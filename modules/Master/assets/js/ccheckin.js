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
  	  $( '#checkinDate' ).datepicker({
	    formatDate: 'yy-mm-dd',
	    showAnim: ''
	  });
  	  $( '#checkinTime' ).timepicker({
	    timeFormat: 'h:mm p',
	    interval: 60,
	    minTime: '8',
	    maxTime: '6:00pm',
	    // defaultTime: '11',
	    // startTime: '10:00',
	    dynamic: false,
	    dropdown: true,
	    scrollbar: true
	  });

	});

}(jQuery));