{if $observation}
<h1>View & Edit Observations<br><small>for {$user->fullName} ({$user->emailAddress})</small></h1>

<h2>Edit duration<br>
    <small>
        {$observation->purpose->object->shortDescription}<br>
        Check-in date: {$observation->startTime->format('M j, h:ia')}
    </small>
</h2>
<br>
<form action="{$smarty.server.REQUEST_URI}" method="post" class="form-inline">
    <div class="row">
        <div class="form-group col-xs-12">
            <label for="duration">Duration (minutes): &nbsp;</label> 
            <input class="textfield form-control" type="text" name="duration" id="duration" value="{$observation->duration}" />
        </div>
    </div>
    <div class="form-group commands">
        <input class="btn btn-info" type="submit" name="command[save]" value="Save" />
    </div>
{generate_form_post_key}
</form>
<hr><br>
<h2>All of {$user->fullName}'s Observations</h2>
{else}
<h1>All of {$user->fullName}'s Observations</h1>
{/if}
<div class="">
<table class="table table-condensed table-striped table-bordered">
    <thead>
        <tr>
            <th>Details</th>
            <th>Date</th>
            <th>Duration</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
    {foreach item=obs from=$userObservations}
        <tr>
            <td>{$obs->purpose->object->shortDescription}</td>
            <td>{$obs->startTime->format('M j, h:ia')}</td>
            <td class="duration">{if $obs->duration}{$obs->duration}{else}0{/if}<small>&nbsp;(mins)</small></td>
            <td>{if $pAdmin}<a class="pull-right btn btn-xs btn-default" href="admin/observations/{$user->id}/{$obs->id}">edit time</a>{/if}</td>
        </tr>
    {foreachelse}
        <tr>
            <td colspan="4">There have not been any observations for this user.</td>
        </tr>
    {/foreach}
    </tbody>
</table>
</div>