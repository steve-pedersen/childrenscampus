<h1>Course History for: {$course->shortName|escape}</h1>
<div class="tabs">
    <ul class="tab-list">
        <li><a href="courses/view/{$course->id}">view</a></li>
        <li><a href="courses/students/{$course->id}">students</a></li>
        <li class="active"><span>history</span></li>
    </ul>
</div>
{foreach item='facet' from=$facets}
<h2>{$facet.facet->shortDescription|escape}</h2>
<table class="table">
    <thead>
        <tr>
            <th>Student</th>
            <th>Number of Observations</th>
            <th>Total Time Spent</th>
        </tr>
    </thead>
    <tbody>
    {foreach item='user' from=$facet.users}
        <tr>
            <td>
                {$user.user->displayName|escape} ({$user.user->ldap_user|escape})
            </td>
            <td>{$user.num}</td>
            <td>{$user.time|min2hr}</td>
        </tr>
    {foreachelse}
        <tr>
            <td colspan="3">There have not been any observations for this course</td>
        </tr>
    {/foreach}
    </tbody>
</table>
{/foreach}