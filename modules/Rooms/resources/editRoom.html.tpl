<h1>{if $new}Create Room{else}Edit Room: {$room->name|escape}{/if}</h1>
<form action="{$smarty.server.REQUEST_URI}" method="post">
    <dl>
        <dt><label for="room-observationType">Observation Type</label></dt>
        <dd><select name="room[observationType]" id="room-observationType">
        {foreach item='type' from=$observationTypes}
            <option value="{$type}"{if $type == $room->observationType} selected="selected"{/if}>{$type}</option>
        {/foreach}
        </select></dd>
        <dt><label for="room-name">Name:</label></dt>
        <dd><input class="textfield" type="text" name="room[name]" id="room-name" value="{$room->name}" /></dd>
		{if $errors.name}<dd class="error">{$errors.name}</dd>{/if}
		<dt><label for="room-maxObservers">Maximum number of observers:</label></dt>
        <dd><input class="textfield" type="text" name="room[maxObservers]" id="room-maxObservers" value="{$room->maxObservers}" /></dd>
		{if $errors.maxObservers}<dd class="error">{$errors.maxObservers}</dd>{/if}
        <dt><label for="room-description">Description</label></dt>
        <dd><textarea name="room[description]" id="room-description" cols="70" rows="4">{$room->description|escape}</textarea></dd>
        <dt><label for="room-days">Days available:</label></dt>
		{foreach item='day' from=$days key='index' name='days'}
		<dd class="checkboxes{if $smarty.foreach.days.last} last{/if}">
			<input type="checkbox" name="room[days][{$index}]" id="room-days-{$index}" value="{$index}" {if in_array($index, $room->days)}checked="checked"{/if} />
			<label for="room-days-{$index}">{$day}</label>
		</dd>
		{/foreach}
        <dt><label for="room-days">Hours available:</label></dt>
		{foreach item='hour' from=$hours name='hours'}
		<dd class="checkboxes{if $smarty.foreach.hours.last} last{/if}">
			<input type="checkbox" name="room[hours][{$hour}]" id="room-days-{$hour}" value="{$hour}" {if in_array($hour, $room->hours)}checked="checked"{/if} />
			<label for="room-days-{$hour}">{$hour|ampm}</label>
		</dd>
		{/foreach}
    </dl>
    <div class="commands">
        <p><input type="submit" name="command[save]" value="Save" /></p>
    </div>
</form>