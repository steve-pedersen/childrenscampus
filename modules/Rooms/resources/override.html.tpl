<h1>Confirm Check-in of Reservation</h1>

<form action="{$smarty.server.REQUEST_URI|escape}" method="post" class="form-inline" autocomplete="off">
	<p>This reservation is scheduled for <strong>{$reservation->startTime->format('n/j/Y')}</strong> from <strong>{$reservation->startTime->format('g:i A')}</strong> to <strong>{$reservation->endTime->format('g:i A')}</strong>.</p>
    <p>Choose the date & time to check-in this student:</p>
    <div class="form-group">
    	<input class="form-control datepicker" type="text" name="checkinDate" id="checkinDate" value="{$reservation->startTime->format('n/j/Y')}" required/>
        <label for="checkinDate">Date</label>
    </div>
    <br>
    <div class="form-group">
    	<input class="form-control timepicker" type="text" name="checkinTime" id="checkinTime" value="{$topOfHour->format('g:i A')}" required/>
        <label for="checkinTime">Time</label>
    </div>
	<hr>
    <div class="commands">
        <input class="btn btn-primary" type="submit" name="command[override]" value="Check-in Reservation" />
        <a class="btn btn-default" href="reservations/upcoming"> Cancel</a>
    </div>
{generate_form_post_key}
</form>
