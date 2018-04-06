<h1>Current Reservations
{if $pAdmin}
<div class="pull-right">
    {if $view != 'upcoming'}
        <a class="btn btn-default" href="admin/observations/reservations?view=upcoming" role="button">View Upcoming</a>
    {else}
        <a class="btn btn-default" href="admin/observations/reservations?view=all" role="button">View All</a>
    {/if}
</div>
{/if}
</h1>
<p> 
    These are the current reservations for Children's Campus.  
</p>
<table class="table table-bordered table-responsive">
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
            <td><a href="admin/accounts/{$reservation->account->id}?returnTo={$smarty.server.REQUEST_URI}">{$reservation->account->firstName} {$reservation->account->lastName}</a> ({if $reservation->account->ldap_user}{$reservation->account->ldap_user}{else}{$reservation->account->emailAddress}{/if})</td>
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