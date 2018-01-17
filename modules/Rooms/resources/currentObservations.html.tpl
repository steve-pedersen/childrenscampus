<h1>Current Observations</h1>
<p> 
    These are the current observation which are going on at Children's Campus.  
    You may use this feature to verify that people have successfully logged into the system.
</p>
<table class="table">
    <thead>
        <tr>
            <th>Start Time</th>
            <th>Observer</th>
            <th>Room</th>
            <th>Purpose</th>
        </tr>
    </thead>
    {foreach item='reservation' from=$reservations}
        <tr>
            <td>{$reservation->observation->startTime->getDate()|date_format:"%B %e, %Y %I:%M %p"}</td>
            <td>{$reservation->account->displayName|escape} ({if $reservation->account->ldap_user}{$reservation->account->ldap_user}{else}{$reservation->account->email}{/if})</td>
            <td>{$reservation->room->name}</td>
            <td>{$reservation->observation->purpose->shortDescription|escape}</td>
        </tr>
    {foreachelse}
        <tr><td colspan="4" class="single-cell">There are no current observations</td></tr>
    {/foreach}
</table>