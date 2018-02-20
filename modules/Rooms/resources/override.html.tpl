<h1>Confirm Check-in of Reservation</h1>

<form action="{$smarty.server.REQUEST_URI|escape}" method="post">
    <p>
        Are you sure you want to check the reservation in for {$reservation->room->name|escape}
        at {$reservation->startTime|date_format:"%b %e, %Y at %l %p"}?
    </p>
    <div class="commands">
        <p><input type="submit" name="command[override]" value="Check-in Reservation" /><a href="reservations/upcoming">cancel</a></p>
    </div>
{generate_form_post_key}
</form>
