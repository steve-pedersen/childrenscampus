<h1>View Course - <small>{$course->fullName|escape}</small></h1>
{if $pView}

<div class="tabs">
    <ul class="nav nav-tabs nav-justified">
        <li role="presentation" class="active"><a href="courses/view/{$course->id}">View</a></li>
        <li role="presentation"><a href="courses/history/{$course->id}">History</a></li>
    </ul>
</div>

{/if}
<div class="course">
<h2>Overview</h2>
<dl class="dl-horizontal">
	<dt>Instructor{if $course->teachers|@count > 1}s{/if}:</dt>
	{foreach item='instructor' from=$course->teachers}
	<dd>{$instructor->firstName} {$instructor->lastName}</dd>
	{/foreach}
    <dt>Semester</dt>
    <dd>{$course->semester->display}</dd>
{if $pView}
	<dt>Number of students:</dt>
	<dd>{$students|@count}</dd>
{/if}
    <dt>Course tasks:</dt>
    <dd>
        <ul class="task-list">
        {foreach from=$course->facets->index(0)->tasks item=task}
            <li>{$task}.</li>
        {foreachelse}
            No tasks specified for this course.
        {/foreach}
        </ul>    
    </dd>
</dl>
{foreach item='facet' from=$course->facets}
<p class=""><strong>{$facet->type->name|escape}. </strong>{$facet->description}</p>
{/foreach}
</div>
<div class="users">
{if $pView}
<h2>Students</h2>
<p><em>Student enrollment status synchronizes with official university records. Changes to courses will be updated periodically.</em></p>
<ul class="">
{foreach item='student' from=$students}
<li>
	{$student->firstName} {$student->lastName}
</li>
{foreachelse}
<li>There are no students in this course</li>
{/foreach}
</ul>
{else}
<h2>History</h2>
<table class="table">
    <thead>
        <tr>
            <th>Start Time</th>
            <th>End Time</th>
            <th>Duration</th>
        </tr>
    </thead>
    {if $totalTime}
    <tfoot>
        <tr>
            <th colspan="2">Total Duration</th>
            <td>{$totalTime}</td>
        </tr>
    </tfoot>
    {/if}
    <tbody>
    {foreach item='observation' from=$observations}
        <tr>
            <td>{$observation->startTime|date_format:"%B %e, %Y %I:%M %p"}</td>
            <td>{$observation->endTime|date_format:"%B %e, %Y %I:%M %p"}</td>
            <td>{$observation->duration}</td>
        </tr>
    {foreachelse}
        <tr><td colspan="3">There are no recorded observations for you in this course.</td></tr>
    {/foreach}
    </tbody>
</table>
{/if}
</div>