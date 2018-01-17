<h1>Current Reservations</h1>
<p> 
    These are the current reservations for Children's Campus.  
</p>
<table class="table">
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
        {*    <td>{$reservation->startTime->getDate()|date_format:"%Y-%m-%d %I:%M %p"}</td> *}
            <td>{$reservation->startTime->getDate()|date_format:"%B %e, %Y %I:%M %p"}</td>
            <td>{$reservation->account->displayName|escape} ({if $reservation->account->ldap_user}{$reservation->account->ldap_user}{else}{$reservation->account->email}{/if})</td>
            <td>{$reservation->room->name}</td>
            <td>{$reservation->observation->purpose->shortDescription|escape}</td>
			<td class="actions">
				<a href="reservations/delete/{$reservation->id}">cancel</a>
			<a href="reservations/override/{$reservation->id}">check-in</a>
			</td>
        </tr>
    {foreachelse}
        <tr><td colspan="4" class="single-cell">There are no current observations</td></tr>
    {/foreach}
</table>