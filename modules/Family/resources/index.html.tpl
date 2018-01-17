<h1>Manage Family Purpose</h1>
{if $message}<p class="success">{$message}</p>{/if}
<form action="{$smarty.server.REQUEST_URI}" method="post">
    <ul>
    {foreach item='purpose' from=$purposes}
        <li>
            <input type="checkbox" name="purposes[{$purpose->id}]" id="purposes-{$purpose->id}" value="{$purpose->id}" />
            <label for="purposes-{$purpose->id}">{$purpose->name|escape}</label>
            <a href="admin/purposes/edit/{$purpose->id}" title="edit {$purpose->name|escape}">edit</a>
        </li>
    {foreachelse}
        <li><p>There are no family purposes configured.</p>
    {/foreach}
    </ul>
    <p><a class="new" href="admin/family/edit/new">Create a new family purpose</a></p>
	<div class="commands">
        <p><input type="submit" name="command[remove]" value="Remove Selected" /></p>
    </div>
</form>