{if $room}
<h1>{$room->name|escape} schedule for the week of {$calendar.weekofdate->format('M j, Y')}</h1>
<table class="table calendar">
    <caption>
        <a class="nav previous" title="previous week" href="{$calendar.previous}">&lt;&lt;</a>
                {$calendar.week[0].display} - {$calendar.week[6].display}
        <a class="nav next" title="next week" href="{$calendar.next}">&gt;&gt;</a>
    </caption>
    <thead>
        <tr>
            <th></th>
            <th>Sunday</th>
            <th>Monday</th>
            <th>Tuesday</th>
            <th>Wednesday</th>
            <th>Thursday</th>
            <th>Friday</th>
            <th>Saturday</th>
        </tr>
    </thead>
    
    <tbody>
        {foreach item='timeDisplay' key='time' from=$calendar.times}
        <tr>
            <th scope="row">{$timeDisplay}</th>
          {foreach from=$calendar.week item='day'}
            {assign var='result' value=$day.times[$time]}
            <td>
            {foreach item='reservation' from=$result}
                <p>{$reservation->account->displayName|escape} ({$reservation->observation->purpose->object->course->shortName})</p>
            {/foreach}
            </td>
          {/foreach}
        </tr>
        {/foreach}
    </tbody>
</table>
{else}
<h1>Select a room to see the schedule</h1>
<div class="form-group">
{foreach item='room' from=$rooms}
    <p><a href="reservations/schedule/{$room->id}">{$room->name|escape}</a></p>
    <p>{$room->description|escape}</p>
{/foreach}
</div>

{/if}