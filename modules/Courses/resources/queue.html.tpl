{if !$moreInfo}
<h1>Manage Course Requests</h1>

{if $allowed}
<div class="flash">
    <div class="notice message">
        {foreach from=$allowed item='courseName'}
        <p class="alert alert-success">{$courseName|escape} has been created.</p>
        {/foreach}
    </div>
</div>
{/if}

{if $denied}
<div class="flash">
    <div class="notice message">
        {foreach from=$denied item='courseName'}
        <p class="alert alert-warning">{$courseName|escape} has been removed and will not be created.</p>
        {/foreach}
    </div>
</div>
{/if}

<h2>Course creation requests</h2>
<p><em>Click course name to view more details about the request</em></p>
<form method="post" action="{$smarty.server.REQUEST_URI}">
    <table class="table table-responsive table-bordered table-hover course-requests">
        <thead>
            <tr>
                <th>Course Name</th>
                <th>Requester</th>
                <th>Course Details</th>
                <th>Allow</th>
                <th>Deny</th>
            </tr>
        </thead>
        <tbody>
        {foreach from=$courserequests item='cr'}
        {if !$cr->course->deleted}
            {if $duplicates}
            {foreach from=$duplicates item=d}
                {if $cr->id == $d}
                    {assign var='dup' value=$d}
                {/if}
            {/foreach}
            {/if}
            <tr {if $dup && $dup == $cr->id}class="bg-warning"{/if}>
                <td><a href="admin/courses/queue/{$cr->id}">{$cr->course->shortName|escape}</a> {if $dup && $dup == $cr->id}<span class="label label-default">duplicate request</span>{/if}</td>
                <td><strong><a href="admin/accounts/{$cr->requestedBy->id}?returnTo={$smarty.server.REQUEST_URI}">{$cr->requestedBy->firstName} {$cr->requestedBy->lastName}</a></strong> on {$cr->requestDate->format('M j, h:ia')}</td>
                <td>{$cr->course->facetType->name}<br> 
                    <span class="badge">{$cr->courseEnrollments.students|@count} student{if $cr->courseEnrollments.students|@count > 1}s{/if}</span>
                </td>
                <td class="checkboxes">
                    <label class="box-label" for="allow-{$cr->id}">
                        <input type="checkbox" id="allow-{$cr->id}" name="allow[{$cr->id}]" title="allow {$cr->course->shortName|escape}" />
                    </label>
                </td>
                <td class="checkboxes">
                    <label class="box-label" for="deny-{$cr->id}">
                        <input type="checkbox" id="deny-{$cr->id}" name="deny[{$cr->id}]" title="deny {$cr->course->shortName|escape}" />
                    </label>
                </td>
            </tr>
        {/if}
        {foreachelse}
            <tr><td colspan="7" align="center">There are no courses in the queue.</td></tr>
        {/foreach}
        </tbody>
    </table>
    {if $courserequests}
    <div class="commands">
        <p><input class="btn btn-info" type="submit" name="command[update-creation]" value="Update" /></p>
    </div>
    {/if}
{generate_form_post_key}
</form>
{else}
<h1>Course Request <small> {$courseRequest->course->shortName}</small></h1>
{if $duplicates}
{foreach from=$duplicates item=d}
    {if $courseRequest->id == $d}
        {assign var='dup' value=true}
    {/if}
{/foreach}
{/if}
{if $dup && $dup == $courseRequest->id}
<p class="alert alert-danger">
    <strong>NOTE:</strong> This is a duplicate course request. A course with the same name and type as this one has already been requested at an earlier date. 
    Back to <a href="admin/courses/queue">manage course requests</a>.
</p>
{/if}
<form method="post" action="{$smarty.server.REQUEST_URI}">
    <h2>Request details</h2>
    <div class="course-info">
        <dl class="dl-horizontal">
            <dt>Title:</dt><dd>{$courseRequest->course->fullName}</dd>
            <dt>Short name:</dt><dd>{$courseRequest->course->shortName}</dd>
            {if $courseRequest->course->department}<dt>Department:</dt><dd>{$courseRequest->course->department}</dd>{/if}
            <dt>Semester:</dt><dd>{$courseRequest->course->semester->display}</dd>
            <dt>Start date:</dt><dd>{$courseRequest->course->semester->startDate->format('M j, Y')}</dd>
            <dt>End date:</dt><dd>{$courseRequest->course->semester->endDate->format('M j, Y')}</dd>
            <dt>Course type:</dt><dd>{$courseRequest->course->facetType->name}</dd>
            <dt>Course description:</dt><dd>{$courseRequest->course->facets->index(0)->description}</dd>
            <dt>Course tasks:</dt>
            <dd>
                <ul class="task-list">
                {foreach from=$courseFacet->tasks item=task}
                    <li>{$task}.</li>
                {foreachelse}
                    No tasks specified in this request.
                {/foreach}
                </ul>    
            </dd>
            <dt>Students:</dt><dd>{$courseEnrollments.students|@count}</dd>
            <dt>Teachers:</dt><dd>{$courseEnrollments.teachers|@count}</dd>
        </dl>
    </div>
    <div class="request-info">
        <dl class="dl-horizontal">
            <dt>Requested by:</dt><dd><a href="admin/accounts/{$courseRequest->requestedBy->id}?returnTo={$smarty.server.REQUEST_URI}">{$courseRequest->requestedBy->firstName} {$courseRequest->requestedBy->lastName}</a> &mdash; {$courseRequest->requestedBy->emailAddress}</dd>
            <dt>Request date:</dt><dd>{$courseRequest->requestDate->format('M j, Y — h:ia')}</dd>
        </dl>
    </div>
<!--     <hr>
    <h2>Enrollments</h2>
    <div class="enrollment-info">
        <dl class="dl-horizontal">
            <dt>Student count:</dt><dd>{$courseEnrollments.students|@count}</dd>
            <dt>Teacher count:</dt><dd>{$courseEnrollments.teachers|@count}</dd>
        </dl>
        <h3>Teachers</h3>
        {foreach from=$courseEnrollments.teachers item=teacher}
            <dl class="dl-horizontal teacher-list">
                <dt>Name:</dt><dd><a href="admin/accounts/{$teacher->id}?returnTo={$smarty.server.REQUEST_URI}">{$teacher->firstName} {$teacher->lastName}</a></dd>
                <dt>Email:</dt><dd>{$teacher->emailAddress}</dd>
                <dt>SF State ID:</dt><dd>{$teacher->username}</dd>
            </dl>            
        {/foreach}
        <h3>Students</h3>
        {foreach from=$courseEnrollments.students item=student}
            <dl class="dl-horizontal student-list">
                <dt>Name:</dt><dd><a href="admin/accounts/{$student->id}?returnTo={$smarty.server.REQUEST_URI}">{$student->firstName} {$student->lastName}</a></dd>
                <dt>Email:</dt><dd>{$student->emailAddress}</dd>
                <dt>SF State ID:</dt><dd>{$student->username}</dd>
            </dl>            
        {/foreach}
    </div> -->
    <hr>
    <div class="commands">
        <input type="submit" name="allow[{$courseRequest->id}]" value="Allow" class="btn btn-primary" />
        <input type="submit" name="deny[{$courseRequest->id}]" value="Deny" class="btn btn-danger" />
        <a href="admin/courses/queue">Cancel</a>
    </div>
{generate_form_post_key}
</form>
{/if}