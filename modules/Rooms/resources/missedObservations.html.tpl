<h1>Missed Observations</h1>
<p> 
    These are the observations which were scheduled but the appointment was not kept.  
</p>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Start Time</th>
            <th>Observer</th>
            <th>Room</th>
            <th>Purpose</th>
            <th>Actions</th>
        </tr>
    </thead>
    {foreach item='reservation' from=$reservations}
        <tr>
            <td>{$reservation->startTime|date_format:"%B %e, %Y %I:%M %p"}</td>
            <td>{$reservation->account->firstName} {$reservation->account->lastName} ({if $reservation->account->ldap_user}{$reservation->account->ldap_user}{else}{$reservation->account->emailAddress}{/if})</td>
            <td>{$reservation->room->name}</td>
            <td>{$reservation->observation->purpose->shortDescription|escape}</td>
			<td class="actions">
				<a href="reservations/delete/{$reservation->id}">cancel</a>
				<a href="reservations/override/{$reservation->id}">check-in</a>
			</td>
        </tr>
    {foreachelse}
        <tr><td colspan="5" class="single-cell">There are no missed observations</td></tr>
    {/foreach}
</table>