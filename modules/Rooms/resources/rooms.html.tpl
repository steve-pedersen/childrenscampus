<h1>Manage Rooms</h1>
{if $message}<div class="flash"><div class="message notice"><p>{$message}</p></div></div>{/if}
<form action="{$smarty.server.REQUEST_URI}" method="post">
    <table class="table">
		<thead>
			<tr>
				<th> </th>
				<th>Name</th>
				<th>Occupancy</th>
                <th>Days</th>
				<th>Actions</th>
			</tr>
		</thead>
		<tbody>
		{foreach item='room' from=$rooms}
			<tr>
				<td class="checkbox"><input type="checkbox" name="rooms[{$room->id}]" id="rooms-{$room->id}" value="{$room->id}" /></td>
				<td><label for="rooms-{$room->id}">{$room->name|escape}</label></td>
				<td>{$room->maxObservers}</td>
				<td>{$room->shortDays}</td>
				<td><a href="admin/rooms/edit/{$room->id}" title="edit {$room->name|escape}">edit</a></td>
			</tr>
		{/foreach}
		</tbody>
	</table>
    <div class="link-controls">
        <p><a class="new" href="admin/rooms/edit/new">Create a Room</a></p>
    </div>
	<div class="commands">
        <p><input type="submit" name="command[remove]" value="Remove Selected" /></p>
    </div>
</form>