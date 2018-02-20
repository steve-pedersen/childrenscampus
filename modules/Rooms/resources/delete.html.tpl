<h1>Confirm Cancelling of Reservation</h1>

<form action="{$smarty.server.REQUEST_URI|escape}" method="post">
    <p>
        Are you sure you want to cancel the reservation for {$reservation->room->name|escape}
        at {$reservation->startTime|date_format:"%b %e, %Y at %l %p"}?
    </p>
    <div class="commands">
        <p><input type="submit" name="command[delete]" value="Cancel Reservation" /><a href="reservations/upcoming">cancel</a></p>
    </div>
{generate_form_post_key}
</form>
