<h1>Past Observations</h1>
{foreach item='purposeInfo' from=$purposes}
<h2>{$purposeInfo.purpose->shortDescription}</h2>
<table class="table">
    <thead>
        <tr>
            <th>Room</th>
            <th>Time</th>
            <th>Duration</th>
        </tr>
    </thead>
    <tfoot>
        <tr>
            <td colspan="2">Total Time Spent</td>
            <td>{$purposeInfo.time}</td>
        </tr>
    </tfoot>
    <tbody>
{foreach item='observation' from=$purposeInfo.observations}
        <tr>
            <td>{$observation->room->name|escape}</td>
            <td>{$observation->startTime|date_format:"%b %e, %Y at %l %p"}</td>
            <td>{$observation->duration}</td>
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