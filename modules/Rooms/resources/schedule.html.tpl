{if $room}
<h1>{$room->name|escape}<br> <small>Schedule for the week of {$calendar.weekofdate->format('M j, Y')}</small></h1>
<div class="tabs">
    <ul class="nav nav-tabs nav-justified weekly-schedule">
        <li role="presentation"><a title="previous week" href="{$calendar.previous}"><span class="glyphicon glyphicon-chevron-left"></span> Previous week</a></li>
        <li role="presentation" class="active"><a href="{$smarty.server.REQUEST_URI}">Current<br><span class="week-range">{$calendar.week[0].display} - {$calendar.week[6].display}<span></a></li>
        <li role="presentation"><a title="next week" href="{$calendar.next}">Next week <span class="glyphicon glyphicon-chevron-right"></span></a></li>
    </ul>
</div>
<table class="table calendar table-bordered">
    <thead>
        <tr>
            <th></th>
            <!-- <th>Sunday</th> -->
            <th>Monday<br><small>{$calendar.week[1].month} {$calendar.week[1].dayOfMonth}{$calendar.week[1].suffix}</small></th>
            <th>Tuesday<br><small>{$calendar.week[2].month} {$calendar.week[2].dayOfMonth}{$calendar.week[2].suffix}</small></th>
            <th>Wednesday<br><small>{$calendar.week[3].month} {$calendar.week[3].dayOfMonth}{$calendar.week[3].suffix}</small></th>
            <th>Thursday<br><small>{$calendar.week[4].month} {$calendar.week[4].dayOfMonth}{$calendar.week[4].suffix}</small></th>
            <th>Friday<br><small>{$calendar.week[5].month} {$calendar.week[5].dayOfMonth}{$calendar.week[5].suffix}</small></th>
            <!-- <th>Saturday</th> -->
        </tr>
    </thead>
    
    <tbody>
        {foreach item='timeDisplay' key='time' from=$calendar.times}
        <tr class="schedule-table">
            <th scope="row" class="time-display">{$timeDisplay}</th>
          {foreach from=$calendar.week item='day'}
            {if $day.dayOfWeek != 0 && $day.dayOfWeek != 6}
                {assign var='blockedDate' value=false}
                {foreach from=$blockDates item=$blocked}
                    {if $blocked->format('Y/m/d') == $day.date}
                        <!-- <h1>MATCH FOUND: {$blocked->format('Y/m/d')}</h1> -->
                        {assign var='blockedDate' value=true}
                    {/if}
                {/foreach}
                {if !$blockedDate}
                    {assign var='result' value=$day.times[$time]}
                    <td>
                    {foreach item='reservation' from=$result}
                        {if $reservation->account}
                        <p>
                            <a href="reservations/view/{$reservation->id}">
                            {$reservation->account->firstName|escape} {$reservation->account->lastName|escape} ({$reservation->observation->purpose->object->course->shortName})
                            </a>
                        </p>
                        {else}
                        <!-- <p class="unavailable text-center">&mdash;</p> -->
                        {/if}
                    {/foreach}
                    </td>
                {else}
                    <td class="blocked-date text-center">&mdash;<small>closed</small>&mdash;</td>
                {/if}
            {/if}
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