<h1>Course History - <small>{$course->shortName|escape}</small></h1>
<div class="tabs">
    <ul class="nav nav-tabs nav-justified">
        <li role="presentation"><a href="courses/view/{$course->id}">View</a></li>
        <!-- <li role="presentation"><a href="courses/students/{$course->id}">Students</a></li> -->
        <li role="presentation" class="active"><a href="courses/history/{$course->id}">History</a></li>
    </ul>
</div>
{foreach item='facet' from=$facets}
<h2>{$facet.facet->shortDescription|escape}</h2>
<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>Student</th>
            <th>Number of Observations</th>
            <th>Total Time Spent <small>&nbsp;(minutes)</small></th>
        </tr>
    </thead>
    <tbody>
    {foreach item='user' from=$facet.users}
        <tr>
            <td>
                {$user.user->firstName|escape} {$user.user->lastName|escape} ({$user.user->emailAddress|escape})
            </td>
            <td>{$user.num}</td>
            <td class="duration">{$user.time}</td>
        </tr>
    {foreachelse}
        <tr>
            <td colspan="3">There have not been any observations for this course</td>
        </tr>
    {/foreach}
    </tbody>
</table>
{/foreach}