<h1>Current Observations</h1>
<div class="tabs">
    <ul class="nav nav-tabs nav-justified weekly-schedule">
        <li role="presentation"><a title="Missed Reservations" href="admin/observations/missed"><span class="glyphicon glyphicon-chevron-left"></span> Missed Reservations</a></li>
        <li role="presentation" class="active"><a href="admin/observations/current">Current Observations</a></li>
        <li role="presentation"><a title="Upcoming Reservations" href="admin/observations/reservations">Upcoming Reservations <span class="glyphicon glyphicon-chevron-right"></span></a></li>
    </ul>
</div>
<p> 
    These are the current, ongoing observations at Children's Campus.  
    You may use this feature to verify that people have successfully logged into the system.
</p>
<table class="table table-bordered table-striped">
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
            <td>{$reservation->observation->startTime->format('M jS, Y g:ia')}</td>
            <td>{$reservation->account->firstName} {$reservation->account->lastName} ({$reservation->account->emailAddress})</td>
            <td><a href="reservations/schedule/{$reservation->room->id}">{$reservation->room->name}</a></td>
            <td>{$reservation->observation->purpose->shortDescription|escape}</td>
            <td>{if !$reservation->observation->endTime}<a class="btn btn-xs btn-default" href="admin/observations/current?checkout={$reservation->id}">check-out</a>{/if}</td>
        </tr>
    {foreachelse}
        <tr><td colspan="5" class="single-cell">There are no current observations</td></tr>
    {/foreach}
</table>