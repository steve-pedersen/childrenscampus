{if $nopurpose}
<h1>Reservations</h1>
<div class="flash">
    <div class="error message">
        <p>
            You do not have any active courses to reserve observation time for.  If you
            are supposed to be able to request observation time for a particular course,
            please contact your professor.
        </p>
    </div>
</div>
{else}
<h1>Select the room you want to reserve</h1>
{if $rooms.observe}
<div class="room-section">
    {if $rooms.participate}
    <h2>Observation Only</h2>
    <p>
        Students will be observing children only from the observation room/s.  
        Students will not have opportunity to interact with the children and/or 
        teachers in the classroom.  Generally students whose professors select 
        the “observation only” category observe just one time. 
    </p>
    {/if}
<dl>
{foreach item='room' from=$rooms.observe}
    <dt><a href="reservations/week/{$room->id}">{$room->name|escape}</a></dt>
    <dd>{$room->description|escape}</dd>
{/foreach}
</dl>
</div>
{/if}
{if $rooms.participate}
<div class="room-section">
    {if $rooms.observe}
    <h2>Participation</h2>
    <p>
        This space is reserved for students who will be interacting with the 
        children throughout their required observation hours.  Generally 
        students whose professors select the “participant observer” category 
        are in the classroom for at least 20 hours over the course of the 
        semester.  For the purposes of their course, students are required to 
        interact with the children and teachers.  Teachers at the Children’s 
        Center will ask these students to help in the classroom if they don’t 
        on their own.   Only one participant is allowed in each classroom at 
        a time.  <strong>There are NO participants in the infant classrooms</strong>.
    </p>
    {/if}
<dl>
{foreach item='room' from=$rooms.participate}
    <dt><a href="reservations/week/{$room->id}">{$room->name|escape}</a></dt>
    <dd>{$room->description|escape}</dd>
{/foreach}
</dl>
</div>
{/if}

{/if}