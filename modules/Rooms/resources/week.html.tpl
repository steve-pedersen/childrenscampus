<h1>Weekly Calendar for {$room->name|escape}</h1>
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
          {/foreach}
        </tr>
        {/foreach}
    </tbody>
</table>