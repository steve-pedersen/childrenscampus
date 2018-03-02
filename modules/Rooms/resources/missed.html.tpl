<h1>Missed Reservations</h1>
<p> 
    These are the reservations which were scheduled but the appointment was not kept.  
    {if $pAdmin}Includes all missed reservations that are over 30 minutes past their ending time.{/if}
</p>
<table class="table">
    <thead>
        <tr>
            <th>Start Time</th>
            <th>Room</th>
            <th>Purpose</th>
        </tr>
    </thead>
    {foreach item='reservation' from=$reservations}
        <tr>
            <td>{$reservation->startTime->format('M j, Y g:ia')}</td>
            <td>{$reservation->room->name}</td>
            <td>{$reservation->observation->purpose->shortDescription|escape}. 
                <a href="reservations/view/{$reservation->id}">View details</a>
            </td>
        </tr>
    {foreachelse}
        <tr><td colspan="4" class="single-cell">There are no missed reservations</td></tr>
    {/foreach}
</table>