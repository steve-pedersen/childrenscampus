<h1>{if $new}Create Room{else}Edit Room: {$room->name|escape}{/if}</h1>
<hr>
<form action="{$smarty.server.REQUEST_URI}" method="post">

    <div class="form-group">
        <label for="room-observationType">Observation Type</label>
        <select class="form-control" name="room[observationType]" id="room-observationType">
        {foreach item='type' from=$observationTypes}
            <option value="{$type}"{if $type == $room->observationType} selected="selected"{/if}>{$type|ucfirst}</option>
        {/foreach}
        </select>
    </div>
    <div class="form-group">
        <label for="room-name">Name:</label>
        <input class="textfield form-control" type="text" name="room[name]" id="room-name" value="{$room->name}" />
        {if $errors.name}<p class="error">{$errors.name}</p>{/if}    
    </div>  
    <div class="form-group">
        <label for="room-maxObservers">Maximum number of observers:</label>
        <input class="textfield form-control" type="text" name="room[maxObservers]" id="room-maxObservers" value="{$room->maxObservers}" />
        {if $errors.maxObservers}<p class="error">{$errors.maxObservers}</p>{/if}   
    </div>
    <div class="form-group">
        <label for="room-description">Description</label>
        <textarea class="form-control" name="room[description]" id="room-description" rows="3">{$room->description|escape}</textarea>
    </div>

    <div class="form-group">
        <label for="room-schedule">Room Availability</label>
        <table class="table table-condensed table-striped table-bordered" id="room-schedule">
            <thead>
            <tr>
                <th></th>
                {foreach from=$days item=day}
                <th>{$day}</th>
                {/foreach}        
            </tr>
            </thead>
            <tbody>
            {foreach from=$hours key=hourkey item=hour}
            {assign var='time' value={$hour|cat:':00'}}
            <tr>
                <td>{$time|date_format:"%l%p"}</td>
                {foreach from=$days key=daykey item=day}              
                <td>
                    <label for="{$day|lcfirst}-{$hour}"><span class="sr-only">{$day|lcfirst} {$hour}</span>                  
                    <input name="room[schedule][{$daykey}][{$hour}]" id="{$day|lcfirst}-{$hour}" type="checkbox" value="true" {if $schedule && $schedule[$daykey][$hour] == 'true'}checked{/if}>
                    </label>
                </td>            
                {/foreach}
            </tr>
            {/foreach}
            </tbody>
        </table>
    </div>  

    <hr>
    <div class="form-group commands">
        <input class="btn btn-info" type="submit" name="command[save]" value="Save" />
    </div>
{generate_form_post_key}
</form>
