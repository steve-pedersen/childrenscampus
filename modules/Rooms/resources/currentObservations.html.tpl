<h1>Current Observations</h1>
<p> 
    These are the current observation which are going on at Children's Campus.  
    You may use this feature to verify that people have successfully logged into the system.
</p>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Start Time</th>
            <th>Observer</th>
            <th>Room</th>
            <th>Purpose</th>
            <th>Action</th>
        </tr>
    </thead>
    {foreach item='reservation' from=$reservations}
        <tr>
            <td>{$reservation->observation->startTime|date_format:"%B %e, %Y %I:%M %p"}</td>
            <td>{$reservation->account->firstName} {$reservation->account->lastName} ({$reservation->account->emailAddress})</td>
            <td>{$reservation->room->name}</td>
            <td>{$reservation->observation->purpose->shortDescription|escape}</td>
            <td>{if !$reservation->observation->endTime}<a class="btn btn-xs btn-default" href="admin/observations/current?checkout={$reservation->id}">check-out</a>{/if}</td>
        </tr>
    {foreachelse}
        <tr><td colspan="5" class="single-cell">There are no current observations</td></tr>
    {/foreach}
</table>