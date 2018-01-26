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
<form method="post" action="{$smarty.server.REQUEST_URI}">
    <table class="table table-responsive table-bordered">
        <thead>
            <tr>
                <th>Course Name</th>
                <th>Requester</th>
                <th>Semester</th>
                <th>Observers</th>
                <th>Participants</th>
                <th>Allow</th>
                <th>Deny</th>
            </tr>
        </thead>
        <tbody>
        {foreach from=$courserequests  item='cr'}
            <tr>
                <td>{$cr->course->shortName|escape}</td>
                <td>{$cr->requestedBy->displayName|escape}</td>
                <td>{$cr->course->semester->display|escape}</td>
                <td>{$cr->courseUsers.observe|@count}</td>
                <td>{$cr->courseUsers.participate|@count}</td>
                <td ><input type="checkbox" name="allow[{$cr->id}]" title="allow {$cr->course->shortName|escape}" /></td>
                <td ><input type="checkbox" name="deny[{$cr->id}]" title="deny {$cr->course->shortName|escape}" /></td>
            </tr>
        {foreachelse}
            <tr><td colspan="7" align="center">There are no courses in the queue.</td></tr>
        {/foreach}
        </tbody>
    </table>
    <div class="commands">
        <p><input class="btn btn-info" type="submit" name="command[update-creation]" value="Update" /></p>
    </div>
</form>

<br><hr>

<h2>Course users requests</h2>
<form method="post" action="{$smarty.server.REQUEST_URI}">
    <table class="table table-responsive table-bordered">
        <thead>
            <tr>
                <th>Course Name</th>
                <th>Requester</th>
                <th>Semester</th>
                <th>Observers</th>
                <th>Participants</th>
                <th>Allow</th>
                <th>Deny</th>
            </tr>
        </thead>
        <tbody>
        {foreach from=$courseUserRequests  item='cur'}
            <tr>
                <td>{$cur->course->shortName|escape}</td>
                <td>{$cur->requestedBy->displayName|escape}</td>
                <td>{$cur->course->semester->display|escape}</td>
                <td>{$cur->users.observe|@count}</td>
                <td>{$cur->users.participate|@count}</td>
                <td ><input type="checkbox" name="allow[{$cur->id}]" title="allow {$cr->course->shortName|escape}" /></td>
                <td ><input type="checkbox" name="deny[{$cur->id}]" title="deny {$cr->course->shortName|escape}" /></td>
            </tr>
        {foreachelse}
            <tr><td colspan="7" align="center">There are no users requests in the queue.</td></tr>
        {/foreach}
        </tbody>
    </table>
    <div class="commands">
        <p><input class="btn btn-primary" type="submit" name="command[update-users]" value="Update" /></p>
    </div>
</form>