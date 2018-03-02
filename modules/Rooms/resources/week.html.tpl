<h1>Weekly Calendar <br><small>{$room->name|escape}</small></h1>
<div class="tabs">
    <ul class="nav nav-tabs nav-justified weekly-schedule">
        <li role="presentation"><a title="previous week" href="{$calendar.previous}"><span class="glyphicon glyphicon-chevron-left"></span> Previous week</a></li>
        <li role="presentation" class="active"><a href="{$smarty.server.REQUEST_URI}">Current<br><span class="week-range">{$calendar.week[0].display} - {$calendar.week[6].display}<span></a></li>
        <li role="presentation"><a title="next week" href="{$calendar.next}">Next week <span class="glyphicon glyphicon-chevron-right"></span></a></li>
    </ul>
</div>
<table class="table calendar table-bordered table-responsive table-condensed">
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
        <tr class="reservation-table">
            <th scope="row" class="time-display">{$timeDisplay}</th>
            {foreach from=$calendar.week item='day'}
                {assign var='result' value=$day.times[$time]}
            {if $day.dayOfWeek != 0 && $day.dayOfWeek != 6}
                {assign var='blockedDate' value=false}
                {foreach from=$blockDates item=$blocked}
                    {if $blocked->format('Y/m/d') == $day.date}
                        {assign var='blockedDate' value=true}
                    {/if}
                {/foreach}
            {if !$blockedDate}
                {if $day.outside}
                <td class="outside-month {$result} {if $result == 'open-space'}available-date{elseif $result == 'full'}unavailable-date{/if}">
                {elseif $day.today}
                <td class="month-day today {$result} {if $result == 'open-space'}available-date{elseif $result == 'full'}unavailable-date{/if}">
                {else}
                <td class="month-day {$result} {if $result == 'open-space'}available-date{elseif $result == 'full'}unavailable-date{/if}">
                {/if}
                {if $result == 'open-space'}
                    <a href="reservations/reserve/{$room->id}/{$day.date}/{$time}">reserve</a>
                {elseif $result == 'full'}
                    <p>full</p>
                {/if}
                </td>
            {else}
                {if $timeDisplay@index == 0}
                <td class="blocked-date text-center" rowspan="{$timeDisplay@total}">&mdash; closed &mdash;</td>      
                {/if}
            {/if}
            {/if}

                    {foreachelse}
            <td colspan="5"></td>
          {/foreach}
            
        </tr>
        {foreachelse}
            <td colspan="6">Outside of range of course dates.</td>
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