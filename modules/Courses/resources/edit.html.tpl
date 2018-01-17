<h1>{if $new}Create a Course{else}Edit Course: {$course->shortName|escape}{/if}</h1>
<form action="{$smarty.server.REQUEST_URI}" method="post">
    <dl>
        <dt><label for="facet-typeId">Type of Course</label></dd>
        <dd><select name="facet[typeId]" id="facet-typeId">
            <option value="">Choose a type of course</option>
        {foreach item='type' from=$facetTypes}
            <option value="{$type->id}"{if $facet && ($facet->typeId == $type->id)} selected="selected"{/if}>{$type->name|escape}</option>
        {/foreach}
        </select></dd>
        {if $errors.facet_type}<dd class="error">{$errors.facet_type}</dd>{/if}
        <dt><label for="semester">Semester:</label></dt>
        <dd>
            <select name="semester" id="semester">
                <option value="">Choose a semester</option>
            {foreach item='sem' from=$semesters key='index' name='days'}
                <option value="{$sem->id}"{if $course->startDate && $course->startDate->getTime() == $sem->startDate->getTime()} selected="selected"{/if}>{$sem->display|escape}</option>
            {/foreach}
            </select>
        </dd>
        {if $errors.startDate}<dd class="error">{$errors.startDate}</dd>{/if}
        <dt><label for="instructor">Instructor:</label></dt>
        <dd>
            <select name="instructor" id="instructor">
                <option value="">Choose an instructor</option>
            {foreach item='instructor' from=$instructors}
                <option value="{$instructor->id}">{$instructor->displayName|escape}</option>
            {/foreach}
            </select>
        </dd>
        <dt><label for="course-fullName">Course Full Name</label></dt>
        <dd><input type="text" class="textfield" name="course[fullName]" id="course-fullName" value="{$course->fullName|escape}" /></dd>
        {if $errors.fullName}<dd class="error">{$errors.fullName}</dd>{/if}
        <dt><label for="course-shortName">Course Short Name</label></dt>
        <dd><input type="text" class="textfield" name="course[shortName]" id="course-shortName" value="{$course->shortName|escape}" /></dd>
        {if $errors.shortName}<dd class="error">{$errors.shortName}</dd>{/if}
        <dt><label for="facet-description">Description</label></dt>
        <dd><textarea rows="3" cols="70" name="facet[description]" id="facet-description">{$facet->description|escape}</textarea></dd>
		<dt><label>For the purposes of this assignment, will your students have to:</label></dt>
		{foreach item="task" key="taskId" from=$facet->GetAllTasks()}
		<dd class="checkboxes"><input type="checkbox" name="facet[tasks][{$taskId}]" id="facet-tasks-{$taskId}" value="{$task}" {if $facet->tasks.$taskId}checked="checked"{/if} /><label for="facet-tasks-{$taskId}">{$task}</label></dd>
		{/foreach}
        {if $course->inDatabase}
        <dt><label>Students in the class</label></dt>
        {foreach item='student' from=$course->students}
        <dd><p>{if $student->lastLoginDate}{$student->displayName|escape}{/if} ({$student->ldap_user|escape}) {if !$student->lastLoginDate}has not logged in yet{/if}</p></dd>
        {foreachelse}
        <dd>There are no students in this course.</dd>
        {/foreach}
        {/if}
    </dl>
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
        <p><input type="submit" name="command[save]" value="{if $new}Create{else}Save{/if} Course" /></p>
    </div>
</form> 