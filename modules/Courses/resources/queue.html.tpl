{if !$moreInfo}
<h1>Manage Course Requests</h1>

{if $allowed}
<div class="flash">
    <div class="notice message">
        {foreach from=$allowed item='courseName'}
        <p>{$courseName|escape} has been created.</p>
        {/foreach}
    </div>
</div>
{/if}

{if $denied}
<div class="flash">
    <div class="notice message">
        {foreach from=$denied item='courseName'}
        <p>{$courseName|escape} has been removed and will not be created.</p>
        {/foreach}
    </div>
</div>
{/if}

<h2>Course creation requests</h2>
<p><em>Click course name to view more details about the request</em></p>
<form method="post" action="{$smarty.server.REQUEST_URI}">
    <table class="table table-responsive table-bordered course-requests">
        <thead>
            <tr>
                <th>Course name</th>
                <th>Requester</th>
                <!-- <th>Date requested</th> -->
                <th>Semester</th>
                <th>Students</th>
                <th>Type</th>
                <th>Allow</th>
                <th>Deny</th>
            </tr>
        </thead>
        <tbody>
        {foreach from=$courserequests  item='cr'}
            <tr>
                <td><strong><a href="admin/courses/queue/{$cr->id}">{$cr->course->shortName|escape}</a></strong></td>
                <td>{$cr->requestedBy->lastName|escape} <em>on {$cr->requestDate->format('M j h:ia')}</em></td>
                <!-- <td></td> -->
                <td>{$cr->course->semester->display|escape}</td>
                <td>{$cr->courseEnrollments.students|@count}</td>
                <td>{$cr->course->facetType->name}</td>
                <td class="text-center"><input type="checkbox" name="allow[{$cr->id}]" title="allow {$cr->course->shortName|escape}" /></td>
                <td class="text-center"><input type="checkbox" name="deny[{$cr->id}]" title="deny {$cr->course->shortName|escape}" /></td>
            </tr>
        {foreachelse}
            <tr><td colspan="7" align="center">There are no courses in the queue.</td></tr>
        {/foreach}
        </tbody>
    </table>
    <div class="commands">
        <p><input class="btn btn-info" type="submit" name="command[update-creation]" value="Update" /></p>
    </div>
{generate_form_post_key}
</form>
{else}
<h1>Course Request <small> {$courseRequest->course->shortName}</small></h1>
<form method="post" action="{$smarty.server.REQUEST_URI}">
    <h2>Request details</h2>
    <div class="course-info">
        <dl class="dl-horizontal">
            <dt>Title:</dt><dd>{$courseRequest->course->fullName}</dd>
            <dt>Short name:</dt><dd>{$courseRequest->course->shortName}</dd>
            {if $courseRequest->course->department}<dt>Department:</dt><dd>{$courseRequest->course->department}</dd>{/if}
            <dt>Semester:</dt><dd>{$courseRequest->course->semester->display}</dd>
            <dt>Course type:</dt><dd>{$courseRequest->course->facetType->name}</dd>
            <dt>Course tasks:</dt>
            <dd>
            {foreach from=$courseFacet->tasks item=task}
                {$task}.{if !$task@last}<br>{/if}
            {foreachelse}
                No tasks specified in this request.
            {/foreach}    
            </dd>
        </dl>
    </div>
    <div class="request-info">
        <dl class="dl-horizontal">
            <dt>Requested by:</dt><dd>{$courseRequest->requestedBy->firstName} {$courseRequest->requestedBy->lastName} &mdash; {$courseRequest->requestedBy->emailAddress}</dd>
            <dt>Request date:</dt><dd>{$courseRequest->requestDate->format('M j, Y — h:ia')}</dd>
            <dt>Student count:</dt><dd>{$courseEnrollments.students|@count}</dd>
            <dt>Teacher count:</dt><dd>{$courseEnrollments.teachers|@count}</dd>
        </dl>
    </div>
    <hr>
    <h2>Enrollments</h2>
    <div class="enrollment-info">
        <h3>Teachers</h3>
        {foreach from=$courseEnrollments.teachers item=teacher}
            <dl class="dl-horizontal teacher-list">
                <dt>Name:</dt><dd>{$teacher->firstName} {$teacher->lastName}</dd>
                <dt>Email:</dt><dd>{$teacher->emailAddress}</dd>
                <dt>SF State ID:</dt><dd>{$teacher->username}</dd>
            </dl>            
        {/foreach}
        <h3>Students</h3>
        {foreach from=$courseEnrollments.students item=student}
            <dl class="dl-horizontal student-list">
                <dt>Name:</dt><dd>{$student->firstName} {$student->lastName}</dd>
                <dt>Email:</dt><dd>{$student->emailAddress}</dd>
                <dt>SF State ID:</dt><dd>{$student->username}</dd>
            </dl>            
        {/foreach}
    </div>
    <hr>
    <div class="commands">
        <input type="submit" name="allow[{$courseRequest->id}]" value="Allow" class="btn btn-primary" />
        <input type="submit" name="deny[{$courseRequest->id}]" value="Deny" class="btn btn-danger" />
        <a href="admin/courses/queue">Cancel</a>
    </div>
{generate_form_post_key}
</form>
{/if}