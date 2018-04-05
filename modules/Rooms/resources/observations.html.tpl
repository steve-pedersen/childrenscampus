<h1>Past Observations</h1>
{foreach item='purposeInfo' from=$purposes}
<h2>{$purposeInfo.purpose->object->course->shortName}<br><small>{$purposeInfo.purpose->object->type->name}</small></h2>
<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>Room</th>
            <th>Time</th>
            <th>Duration <small> &nbsp;(minutes)</small></th>
        </tr>
    </thead>
    <tfoot>
        <tr>
            <td colspan="2"><strong>Total Time Spent</strong></td>
            <td class="duration"><strong>{$purposeInfo.time}</strong></td>
        </tr>
    </tfoot>
    <tbody>
{foreach item='observation' from=$purposeInfo.observations}
        <tr>
            <td>{$observation->room->name|escape}</td>
            <td>{$observation->startTime|date_format:"%b %e, %Y at %l %p"}</td>
            <td class="duration">{$observation->duration}</td>
        </tr>
{/foreach}
    </tbody>
</table>
{foreachelse}
<div class="flash">
    <div class="warning message">
        <p>
            There are no past observations in the system for you yet.  
        </p>
    </div>
</div>
{/foreach}