<h1>Upcoming Reservations</h1>
{if $reservations}
<table class="table">
    <thead>
        <tr>
            <th>Time</th>
            <th>User</th>
            <th>Room</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
{foreach item='reservation' from=$reservations}
        <tr>
            <td>{$reservation->startTime|date_format:"%b %e, %Y at %l %p"}</td>
            <td>{$reservation->account->firstName} {$reservation->account->lastName}</td>
            <td>{$reservation->room->name|escape}</td>
            <td class="actions">
				<a href="reservations/delete/{$reservation->id}">cancel</a>
			{if $pAdmin}<a href="reservations/override/{$reservation->id}">check-in</a>{/if}
			</td>
        </tr>
{/foreach}
    </tbody>
</table>
{else}
<div class="flash">
    <div class="warning message"><p>There are no reservations set up.</p></div>
</div>
{/if}