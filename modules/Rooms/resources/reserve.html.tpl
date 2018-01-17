<h1>Create Reservation for {$room->name} on {$date->getDate()|date_format:$dateFormat}</h1>
{if $message}
<div class="flash">
    <div class="error message"><p>{$message}</p></div>
</div>
{/if}
<form action="{$smarty.server.REQUEST_URI}" method="post">
    {if $purpose->inDatabase}<input type="hidden" name="purpose" value="{$purpose->id}" />{/if}
    <dl>
        {if !$purpose->inDatabase}
        <dt><label for="purpose">Select a Purpose</label></dt>
        <dd><select name="purpose">
            {foreach from=$purposes  item='purpose'}
            <option value="{$purpose->id}">{$purpose->shortDescription|escape}</option>
            {/foreach}
            </select>
        </dd>
        {/if}
        <dt><label for="duration">Choose the length of time to reserve</label></dt>
        <dd><select name="duration" id="duration">
            <option value="">Choose a time...</option>
            <option value="1">1 hour</option>
            <option value="2">2 hours</option>
            <option value="3">3 hours</option>
            </select>
        </dd>
    </dl>
    
    <div class="commands">
        <p><input type="submit" name="command[reserve]" value="Reserve Room" /></p>
    </div>
</form>