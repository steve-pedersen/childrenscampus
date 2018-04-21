<h1>Create Reservation<br><small>{$room->name} on {$date|date_format:$dateFormat}</small></h1>
{if $message}
<div class="flash">
    <div class="error message alert alert-warning alert-dismissible" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <p><strong>{$message}</strong></p>
    </div>
</div>
{/if}
{if $validDate}
<form action="{$smarty.server.REQUEST_URI}" method="post">
    {if $purpose}<input type="hidden" name="purpose" value="{$purpose->id}" />{/if}
    {if !$purpose}
    <div class="form-group">
        <label for="purpose">Select a Purpose</label>
        <select name="purpose" class="form-control">
            {foreach from=$purposes  item='purpose'}
            <option value="{$purpose->id}">{$purpose->shortDescription|escape}</option>
            {/foreach}
        </select>
    </div>
    {/if}
    <div class="form-group">
        <label for="duration">Choose the length of time to reserve</label>
        <select name="duration" id="duration" class="form-control">
            <option value="">Choose a time...</option>
            <option value="1">1 hour</option>
            <option value="2">2 hours</option>
            <option value="3">3 hours</option>
        </select>
    </div>
    <hr>
    <div class="commands">
        <p><input type="submit" name="command[reserve]" value="Reserve Room" class="btn btn-primary" /></p>
    </div>
{generate_form_post_key}
</form>
{else}
<a href="reservations" class="btn btn-primary">Back to Sign Up</a>
{/if}