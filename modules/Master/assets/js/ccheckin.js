(function ($) {

	// DatePicker 
	$(function(){
	  $.datepicker.setDefaults(
		$.extend( $.datepicker.regional[ '' ] )
	  );
      $('.datepicker').each(function () {
        var $self = $(this);
        $self.datepicker({
        	formatDate: 'yy-mm-dd'
        });
      });
      $('.timepicker').each(function () {
        var $self = $(this);
        $self.timepicker({
			timeFormat: 'h:mm p',
			interval: 15,
			minTime: '8',
			maxTime: '6:00pm',
			dynamic: false,
			dropdown: true,
			scrollbar: true
        });
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
			$('#fileSubmit').removeClass('hide');
		  } 
		});

		$('input[id^="account-role-"]').change(function() {
			if ($(this).hasClass('account-role-Administrator')) {
				if ($(this).is(':checked')) {
					$('input[name=receiveAdminNotifications]').prop( "checked", true );
				} else {
					$('input[name=receiveAdminNotifications]').prop( "checked", false );
				}				
			}
			if ($(this).is(':checked')) {
				$('input[name=status]#account-status').prop( "checked", true );
			} else {
				$('input[name=status]#account-status').prop( "checked", false );
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

