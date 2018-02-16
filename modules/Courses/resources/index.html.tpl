<h1>My Courses</h1>
{foreach item='course' from=$courses}
<div class="panel panel-default">
  <div class="panel-heading">
    <h2 class="panel-title">
        <a href="courses/view/{$course->id}">{$course->fullName|escape}{if $course->shortName} <br>
        <small>{$course->shortName|escape}</small>{/if}</a>
    </h2>

  </div>
  <div class="panel-body">
    {assign var='facet' value=$course->facets->index(0)}
<!--     <p class="lead"><strong>{$course->students|@count} students - </strong><small>{$facet->type->name|escape}</small></p>
    <p class="">{$facet->description|escape}</p> -->
    <dl class="dl-horizontal">
        <dt>Instructors:</dt>
        {foreach item='instructor' from=$course->teachers}
            <dd>{$instructor->firstName} {$instructor->lastName}</dd>
        {/foreach}
        <dt>Number of students:</dt>
        <dd>{$course->students|@count}</dd>
        <dt>Course type:</dt>
        <dd>{$facet->type->name|escape}</dd>
    </dl>
    <p class="">{$facet->description}</p>
  </div>
</div>
{foreachelse}
<p class="">You have no courses yet.</p>
{/foreach}
<hr>
<div class="course-actions">
    <ul class="list-unstyled">
        {if $canRequest}
        <li><a href="courses/request" class="btn btn-primary">Request a course</a></li>
        {/if}
    </ul>
</div>