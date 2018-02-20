{if $room}
<h1>{$room->name|escape}<br> <small>Schedule for the week of {$calendar.weekofdate->format('M j, Y')}</small></h1>
<div class="tabs">
    <ul class="nav nav-tabs nav-justified">
        <li role="presentation"><a title="previous week" href="{$calendar.previous}"><span class="glyphicon glyphicon-chevron-left"></span> Previous week</a></li>
        <li role="presentation" class="active"><a href="{$smarty.server.REQUEST_URI}">Current<br>{$calendar.week[0].display} - {$calendar.week[6].display}</a></li>
        <li role="presentation"><a title="next week" href="{$calendar.next}">Next week <span class="glyphicon glyphicon-chevron-right"></span></a></li>
    </ul>
</div>
<table class="table calendar">
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
                <p>{$reservation->account->firstName|escape} {$reservation->account->lastName|escape} ({$reservation->observation->purpose->object->course->shortName})</p>
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
    <div class="panel panel-default">
      <div class="panel-heading">
        <h2 class="panel-title">
            <a href="reservations/schedule/{$room->id}"><span class="glyphicon glyphicon-chevron-right"></span> {$room->name|escape}</a>
        </h2>
      </div>
      <div class="panel-body">
        <p class="">{$room->description|escape}</p>
      </div>
    </div>
{/foreach}
</div>

{/if}