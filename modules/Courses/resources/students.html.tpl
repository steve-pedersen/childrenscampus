<h1>Students for {$course->fullName|escape}</h1>
<div class="tabs">
    <ul class="tab-list">
        <li><a href="courses/view/{$course->id}">view</a></li>
        <li class="active"><span>students</span></li>
        <li><a href="courses/history/{$course->id}">history</a></li>
    </ul>
</div>
<form action="{$smarty.server.REQUEST_URI|escape}" method="post">
{if !$students->isEmpty}
<table class="table">
    <thead>
        <tr>
            <th>Name</th>
            <th>SFSU ID</th>
            <th>Type</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
{foreach item='student' from=$students}
        <tr>
            <td>{$student->displayName}</td>
            <td>{$student->ldap_user}</td>
            <td>{if $course->studentCanParticipate($student)}participate{else}observe{/if}</td>
            <td><a href="courses/drop/{$course->id}/{$student->id}">drop</a></td>
        </tr>
{/foreach}
    </tbody>
</table>
{else}
<div class="flash">
    <div class="warning message"><p>There are no students in this course.</p></div>
</div>
{/if}

<h2>Request more students enroll</h2>

    <div class="course-students">
        <div class="student-list">
            <p><label for="students-observe">Students for Observation</label></p>
            <p class="description">These will be students who can make observations in the observation rooms.</p>
            <textarea rows="20" cols="40" name="students-observe" id="students-observe">{$studentsObserve|escape}</textarea>
        </div>
        <div class="student-list">
            <p><label for="students-participate">Students for Participation</label></p>
            <p class="description">These will be students who can particpate in the classrooms.</p>
            <textarea rows="20" cols="40" name="students-participate" id="students-participate">{$studentsObserve|escape}</textarea>
        </div>
    </div>
    <div class="commands">
        <p><input type="submit" name="command[request]" value="Request Students" /></p>
    </div>
</form>