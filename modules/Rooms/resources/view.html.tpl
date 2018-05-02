<h1>Reservation details<br><small>{$reservation->room->name|escape}</small></h1>
<div class="callOut">

{if $ismissed}
<div class="alert alert-danger"><strong>This reservation has been missed.</strong>
    {if $pAdmin}
    <a href="reservations/delete/{$reservation->id}">Cancel reservation</a>
    {/if}
</div>
{/if}

<img class="img-responsive" src="assets/images/childrensCampus.jpg" alt="Photo of the front door to the Children's Campus">
{if $existing}
    {foreach item='reservation' from=$existing}
    <p>You have a reservation in room {$reservation->room->name} on {$reservation->startTime|date_format:"%b %e, %Y at %l %p"}</p>
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
<div class="alert alert-default">
<dl class="inline dl-horizontal">
    <dt>Who:</dt>
    <dd>{$reservation->account->firstName} {$reservation->account->lastName}</dd>
    <dt>When:</dt>
    <dd>{$reservation->startTime->format('F j, Y – ga')} to {$reservation->endTime->format('ga')}</dd>
    <dt>Course Info:</dt>
    <dd>
        {$reservation->observation->purpose->object->course->fullName}<br>({$reservation->observation->purpose->object->course->shortName})
        <ul>
        {foreach from=$reservation->observation->purpose->object->tasks item=task}
            <li>{$task}</li>
        {/foreach}
        </ul>
    </dd>
</dl>
</div>
<div class="link-controls">
    <p class="first">
        <a class="ok btn btn-success" href="reservations/upcoming">OK</a>
        <a class="cancel btn btn-danger" href="reservations/delete/{$reservation->id}">Cancel reservation</a>
    </p>
</div>