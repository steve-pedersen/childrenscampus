<h1>Upcoming Reservations
<!-- {if $pAdmin}
<div class="pull-right">
    {if $view != 'upcoming'}
        <a class="btn btn-default" href="admin/observations/reservations?view=upcoming" role="button">View Upcoming</a>
    {else}
        <a class="btn btn-default" href="admin/observations/reservations?view=all" role="button">View All</a>
    {/if}
</div>
{/if} -->
</h1>
<div class="tabs">
    <ul class="nav nav-tabs nav-justified weekly-schedule">
        <li role="presentation"><a title="Missed Reservations" href="admin/observations/missed"><span class="glyphicon glyphicon-chevron-left"></span> Missed Reservations</a></li>
        <li role="presentation" ><a title="Current Observations" href="admin/observations/current"><span class="glyphicon glyphicon-chevron-left"></span> Current Observations</a></li>
        <li role="presentation" class="active"><a href="admin/observations/reservations">Upcoming Reservations</span></a></li>
    </ul>
</div>
<p> 
    These are the current reservations for Children's Campus.  
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
        {assign var='courseArr' value=explode('-',$reservation->observation->purpose->object->course->shortName,3)}
        <tr>
            <td>{$reservation->startTime->format("M j, Y h:ia")}</td>
            <td>
                {if $pAdmin}
                <a href="admin/accounts/{$reservation->account->id}?returnTo={$smarty.server.REQUEST_URI}">{$reservation->account->firstName} {$reservation->account->lastName}</a> ({$reservation->account->emailAddress})
                {else}
                {$reservation->account->firstName} {$reservation->account->lastName} ({$reservation->account->emailAddress})
                {/if}
            </td>
            <td><a href="reservations/schedule/{$reservation->room->id}">{$reservation->room->name}</a></td>
            <td><!-- <a href="admin/courses/edit/{$reservation->observation->purpose->object->course->id}"> -->{$reservation->observation->purpose->shortDescription|escape}. <!-- </a> -->
                <a href="reservations/view/{$reservation->id}">View reservation details</a>
            </td>
			<td class="actions">
				<a href="reservations/delete/{$reservation->id}" class="btn btn-xs btn-default">cancel</a>
                <a href="reservations/override/{$reservation->id}" class="btn btn-xs btn-default">check-in</a>
			</td>
        </tr>
    {foreachelse}
        <tr><td colspan="5" class="single-cell">There are no current observations</td></tr>
    {/foreach}
</table>