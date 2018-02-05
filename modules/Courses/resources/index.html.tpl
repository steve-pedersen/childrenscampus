<h1>My Courses</h1>
{foreach item='course' from=$courses}
<div class="course">
<h2><a href="courses/view/{$course->id}">{$course->fullName|escape}{if $course->shortName} ({$course->shortName|escape}){/if}</a></h2>
<dl class="inline">
	<dt>Number of students:</dt>
	<dd>{$course->students->count}</dd>
    {foreach item='facet' from=$course->facets}
	<dt>{$facet->type->name|escape}:</dt>
	<dd>{$facet->description|escape}</dd>
    {/foreach}
</dl>
</div>
{foreachelse}
<p class="alert alert-info">You have no courses yet.</p>
{/foreach}
<div class="course-actions">
    <ul class="list-unstyled">
        {if $canRequest}
        <li><a href="courses/request" class="btn btn-primary">Request a course</a></li>
        {/if}
    </ul>
</div>