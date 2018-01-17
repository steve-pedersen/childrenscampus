<h1>Request a Course</h1>
<form action="{$smarty.server.REQUEST_URI}" method="post">
    <div class="flash">
        <div class="message error">
            <p><b>You may add your students to your course request</b>.  
            If you do not, you can request students be added 
            later by visiting your course page and clicking the &ldquo;students&rdquo; link.</p>
        </div>
    </div>
    <div class="flash">
        <div class="message warning">
            <p class="instructions">
               <b>To add your students to this request:</b>
            </p>
            <ol style="text-align: left">
                <li><span>Go to iLearn and login at <a href="https://ilearn.sfsu.edu" target="_blank">https://ilearn.sfsu.edu</a></span></li>
                <li><span>Navigate to your course section</span></li>
                <li><span>Click the gradebook link in the Administration block</span></li>
                <li><span>Go to the &ldquo;Export&rdquo; page and click &ldquo;Excel spreadsheet&rdquo;</span></li>
                <li><span>Download the excel file and open in Excel</span></li>
                <li><span>Copy the students' IDs in the &ldquo;ID number&rdquo; column</span></li>
                <li><span>On this form, right-click the &ldquo;Students for Observation&rdquo; text box and select &ldquo;Paste&rdquo;. The student SFSU ID's should appear in a list within the text box.</span></li>
                <li><span>Repeat the selection of students for those you wish to have participate in the classrooms.</span></li>
            </ol>
        </div>
    </div>
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
        <dt><label for="course-fullName">Course Full Name</label></dt>
        <dd><input type="text" class="textfield" name="course[fullName]" id="course-fullName" value="{$course->fullName|escape}" /></dd>
        {if $errors.fullName}<dd class="error">{$errors.fullName}</dd>{/if}
        <dt><label for="course-shortName">Course Short Name</label></dt>
        <dd><input type="text" class="textfield" name="course[shortName]" id="course-shortName" value="{$course->shortName|escape}" /></dd>
        {if $errors.shortName}<dd class="error">{$errors.shortName}</dd>{/if}
        <dt><label for="facet-description">Description</label></dt>
        <dd><textarea rows="3" cols="70" name="facet[description]" id="facet-description">{$facet->description|escape}</textarea></dd>
        <!--<dt><label for="facet-studentHours">Total Hours per Student</label></dt>
        <dd><input type="text" class="textfield" name="facet[studentHours]" id="facet-studentHours" value="{$facet->studentHours|escape}" /></dd>-->
		<dt><label>For the purposes of this assignment, will your students have to:</label></dt>
		{foreach item="task" key="taskId" from=$facet->GetAllTasks()}
		<dd class="checkboxes"><input type="checkbox" name="facet[tasks][{$taskId}]" id="facet-tasks-{$taskId}" value="{$task}" {if $facet->tasks.$taskId}checked="checked"{/if} /><label for="facet-tasks-{$taskId}">{$task}</label></dd>
		{/foreach}
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
        <p><input type="submit" name="command[request]" value="Request Course" /></p>
    </div>
</form>