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
            <th>Monday</th>
            <th>Tuesday</th>
            <th>Wednesday</th>
            <th>Thursday</th>
            <th>Friday</th>
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
                {if $day.outside}
                <td class="outside-month {$result}">
                {elseif $day.today}
                <td class="month-day today {$result}">
                {else}
                <td class="month-day {$result}">
                {/if}
                    {if $result == 'open-space'}
                        <a href="reservations/reserve/{$room->id}/{$day.date}/{$time}">reserve</a>
                    {elseif $result == 'full'}
                        <p>full</p>
                    {/if}
                </td>
            {/if}
          {/foreach}
        </tr>
        {/foreach}
    </tbody>
</table>