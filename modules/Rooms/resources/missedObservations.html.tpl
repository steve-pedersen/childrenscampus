<h1>Missed Observations</h1>
<div class="tabs">
    <ul class="nav nav-tabs nav-justified weekly-schedule">
        <li role="presentation" class="active"><a title="Missed Reservations" href="admin/observations/missed"></span> Missed Reservations</a></li>
        <li role="presentation" ><a title="Current Observations" href="admin/observations/current">Current Observations <span class="glyphicon glyphicon-chevron-right"></span></a></li>
        <li role="presentation" ><a href="admin/observations/reservations">Upcoming Reservations <span class="glyphicon glyphicon-chevron-right"></span></span></a></li>
    </ul>
</div>
<p> 
    These are the observations which were scheduled but the appointment was not kept.  
</p>
<table class="table table-bordered table-striped">
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
            <td>{$reservation->startTime->format('M j, Y g:ia')}</td>
            <td>{$reservation->account->firstName} {$reservation->account->lastName} ({$reservation->account->emailAddress})</td>
            <td>{$reservation->room->name}</td>
            <td>{$reservation->observation->purpose->shortDescription|escape}. <a href="reservations/view/{$reservation->id}">View reservation details</a></td>
			<td class="actions">
				<a href="reservations/delete/{$reservation->id}" class="btn btn-xs btn-default">cancel</a>
				<a href="reservations/override/{$reservation->id}" class="btn btn-xs btn-default">check-in</a>
			</td>
        </tr>
    {foreachelse}
        <tr><td colspan="5" class="single-cell">There are no missed observations</td></tr>
    {/foreach}
</table>