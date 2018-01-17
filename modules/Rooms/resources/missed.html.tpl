<h1>Missed Reservations</h1>
<p> 
    These are the reservations which were scheduled but the appointment was not kept.  
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
            <td>{$reservation->startTime->getDate()|date_format:"%B %e, %Y %I:%M %p"}</td>
            <td>{$reservation->room->name}</td>
            <td>{$reservation->observation->purpose->shortDescription|escape}</td>
        </tr>
    {foreachelse}
        <tr><td colspan="4" class="single-cell">There are no missed reservations</td></tr>
    {/foreach}
</table>