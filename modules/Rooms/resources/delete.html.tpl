<h1>Confirm Cancelling of Reservation</h1>

<form action="{$smarty.server.REQUEST_URI|escape}" method="post">
    <p>
        Are you sure you want to cancel the reservation for {$reservation->room->name|escape}
        at {$reservation->startTime->format('M j, Y g:ia')}?
    </p>
    <hr>
    <div class="commands">
        <input class="btn btn-danger" type="submit" name="command[delete]" value="Cancel Reservation" />
        <a class="btn btn-default" href="reservations/upcoming">Go back</a>
    </div>
{generate_form_post_key}
</form>
