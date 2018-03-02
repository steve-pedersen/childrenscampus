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
                        {assign var='blockedDate' value=true}
                    {/if}
                {/foreach}
            {if !$blockedDate}               
                {assign var='isScheduled' value=false}
                {foreach from=$room->schedule[$day.dayOfWeek-1][$time] item=schedule}
                    
                    {if $schedule}                        
                    {assign var='isScheduled' value=true}
                    {/if}
                {/foreach}
                
                {assign var='result' value=$day.times[$time]}
                {if count($result) >= $room->maxObservers}
                <td class="{if $isScheduled}available-date-full{else}unavailable-date{/if}">
                {else}
                <td class="{if $isScheduled}available-date{else}unavailable-date{/if}">
                {/if}
                {foreach item='reservation' from=$result}
                    {if $reservation->account}
                        {assign var='courseArr' value=explode('-',$reservation->observation->purpose->object->course->shortName,3)}
                    <a href="reservations/view/{$reservation->id}">
                        <small>{$reservation->account->firstName|escape} {$reservation->account->lastName|escape} <span class="text-primary">({$courseArr[0]}-{$courseArr[1]})</span></small>
                    </a>
                    {/if}
                {/foreach}
                </td>
            {else}
                {if $timeDisplay@index == 0}
                <td class="blocked-date text-center" rowspan="{$timeDisplay@total}">&mdash; closed &mdash;</td>      
                {/if}
            {/if}
            {/if}
          {/foreach}
        </tr>
        {/foreach}
    </tbody>
</table>

<div class='color-legend'>
<div class='legend-title'>Color legend</div>
<div class='legend-scale'>
  <ul class='legend-labels'>
    <li><span style='background:#dff0d8;'></span>open</li>
    <li><span style='background:#d9edf7;'></span>full</li>
    <li><span style='background:#f5f5f5;'></span>closed</li>
    <li><span style='background:#FFF;'></span>unavailable</li>
  </ul>
</div>
<div class='legend-source'><em>Open&mdash;open for reservations. Full&mdash;reservations are at maximum. Closed&mdash;entire day blocked off from reservations. Unavailable&mdash;timeslot unavailable for the room.</em></div>
</div>

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