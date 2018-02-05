<h1>Manage Rooms</h1><br>
{if $message}<div class="flash"><div class="message notice"><p>{$message}</p></div></div>{/if}
<form action="{$smarty.server.REQUEST_URI}" method="post">
    <table class="table table-responsive table-striped">
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
				<td><input type="checkbox" name="rooms[{$room->id}]" id="rooms-{$room->id}" value="{$room->id}" /></td>
				<td><label for="rooms-{$room->id}">{$room->name|escape}</label></td>
				<td>{$room->maxObservers}</td>
				<td>{$room->shortDays}</td>
				<td><a href="admin/rooms/{$room->id}" title="edit {$room->name|escape}">edit</a></td>
			</tr>
		{foreachelse}
			<tr>
				<td colspan=5>No rooms found.</td>
			</tr>
		{/foreach}
		</tbody>
	</table>
	<hr>
    <div class="link-controls form-group">
        <a class="btn btn-info" class="new" href="admin/rooms/new">Create a Room</a>
        {if $rooms}
        <input class="btn btn-danger" type="submit" name="command[remove]" value="Remove Selected" />
        {/if}
    </div>
{generate_form_post_key}
</form>