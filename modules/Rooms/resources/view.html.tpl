<h1>Reservation for room {$reservation->room->name|escape}</h1>
<div class="callOut">

<img src="{$diva->Link('images/childrensCampus.jpg')}" alt="Photo of the front door to the Children's Campus" />
{if $existing}
    {foreach item='reservation' from=$existing}
    <p>You have a reservation in room {$reservation->room->name} on {$reservation->startTime->getDate()|date_format:"%b %e, %Y at %l %p"}</p>
    {/foreach}
{/if}
<h2>Children's Campus</h2>
<p><strong>Students: Please take note of our location!</strong></p>
<p>
The Children’s Campus is located on the campus of San Francisco State University at 
the corner of North State Drive and Lake Merced Blvd.  We are beside the Library Annex.  
If you need help locating our center, please check the <a href="http://www.sfsu.edu/~sfsumap/" title="Opens in a new window." target="_blank" class="popup">campus map</a>
</p>
</div>
<dl class="inline">
    <dt>Who:</dt>
    <dd>{$reservation->account->displayName}</dd>
    <dt>When:</dt>
    <dd>{$reservation->startTime->getDate()|date_format:$dateFormat}</dd>
    <dt>Until:</dt>
    <dd>{$reservation->endTime->getDate()|date_format:$dateFormat}</dd>
</dl>
<div class="link-controls">
    <p class="first"><a class="ok" href="home">OK</a></p>
    <p class="last"><a class="cancel" href="reservations/delete/{$reservation->id}">cancel reservation</a></p>
</div>