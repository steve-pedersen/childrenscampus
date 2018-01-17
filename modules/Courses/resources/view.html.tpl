<h1>View Course: {$course->fullName|escape}</h1>
{if $pView}
<div class="tabs">
    <ul class="tab-list">
        <li class="active"><span>view</span></li>
        <li><a href="courses/students/{$course->id}">students</a></li>
        <li><a href="courses/history/{$course->id}">history</a></li>
    </ul>
</div>
{/if}
<div class="course">
<h2>Overview</h2>
<dl>
	<dt>Instructors:</dt>
	{foreach item='instructor' from=$course->instructors}
	<dd>{$instructor->account->displayName}</dd>
	{/foreach}
{if $pView}
	<dt>Number of students:</dt>
	<dd>{$students->count}</dd>
	{foreach item='facet' from=$course->facets}
	<dt>{$facet->type->name|escape}:</dt>
	<dd>{$facet->description|escape}</dd>
    {/foreach}
{/if}
</dl>
</div>
<div class="users">
{if $pView}
<h2>Students</h2>
<p>To add students to the course, please go to the <a href="courses/students/{$course->id}">add students page</a> for the course.</p>
<ul>
{foreach item='student' from=$students}
<li>
	{if $student->lastLoginDate}
	<p>{$student->displayName|escape}</p>
	{else}
	<p>{$student->ldap_user|escape}</p>
	{/if}
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
            <td>{$totalTime|duration}</td>
        </tr>
    </tfoot>
    {/if}
    <tbody>
    {foreach item='observation' from=$observations}
        <tr>
            <td>{$observation->startTime->getDate()|date_format:"%B %e, %Y %I:%M %p"}</td>
            <td>{$observation->endTime->getDate()|date_format:"%B %e, %Y %I:%M %p"}</td>
            <td>{$observation->duration|duration}</td>
        </tr>
    {foreachelse}
        <tr><td colspan="3">There are no recorded observations for you in this course.</td></tr>
    {/foreach}
    </tbody>
</table>
{/if}
</div>