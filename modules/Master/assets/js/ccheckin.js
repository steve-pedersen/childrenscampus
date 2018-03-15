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

	$(function () {
		$('input[name=attachment]').change(function() {
		  if (!validateType($("#fileAttachment"))) {
			event.preventDefault();
			event.stopPropagation();
			$('#fileSubmit').addClass('hide');
		  }
		  else {
			$('#fileSubmit').removeClass('hide'); //.css({'margin': '-20px 0 40px -15px'});
		  } 
		});
	});

}(jQuery));

// right now don't restrict file types
function validateType($form) {
	return true;

	var _validFileExtensions = ["pdf", "doc", "docx"];
	var valid = false;
	$("#type-error").hide(); 

	$form.find('[name="attachment"]').each(function () {
		var fileName = this.value.toLowerCase();
		var ext = fileName.substring(fileName.lastIndexOf('.') + 1);

		for (var i in _validFileExtensions) {
			if (ext == _validFileExtensions[i]) {
				valid = true;
			}
		}
	});

	if (!valid) {
		$(this).parent().addClass('has-error'); // adds to containing div
		$("#type-error").show();                // shows the "help-block" text
	}
	return valid;
}

