<h1>{if $new}Create a Course{else}Edit Course: <small>{$course->shortName|escape}</small>{/if}</h1>
<form action="{$smarty.server.REQUEST_URI}" method="post">
    <div class="form-group">
        <label for="facet-typeId">Type of Course</label>
        <select class="form-control" name="facet[typeId]" id="facet-typeId">
            <option value="">Choose a type of course</option>
        {foreach item='type' from=$facetTypes}
            <option value="{$type->id}"{if $facet && ($facet->typeId == $type->id)} selected="selected"{/if}>{$type->name|escape}</option>
        {/foreach}
        </select>
        {if $errors.facet_type}<p class="error">{$errors.facet_type}{/if}</p>
    </div>
    <div class="form-group">
        <label for="semester">Semester:</label>        
        <select class="form-control" name="semester" id="semester">
            <option value="">Choose a semester</option>
        {foreach item='sem' from=$semesters key='index' name='days'}
            <option value="{$sem->id}"{if $course->startDate && $course->startDate == $sem->startDate} selected="selected"{/if}>{$sem->display|escape}</option>
        {/foreach}
        </select>      
        {if $errors.startDate}<p class="error">{$errors.startDate}{/if}</p>
    </div>
    <div class="form-group">
        <label for="instructor">Instructor:</label>     
        <select class="form-control" name="instructor" id="instructor">
            <option value="">Choose an instructor</option>
        {foreach item='instructor' from=$instructors}
            <option value="{$instructor->id}" {if $course->teachers[0]->id == $instructor->id}selected default{/if}>{$instructor->firstName} {$instructor->lastName}</option>
        {/foreach}
        </select>
    </div>
    <div class="form-group">
        <label for="course-fullName">Course Full Name</label>
        <input type="text" class="textfield form-control" name="course[fullName]" id="course-fullName" value="{$course->fullName|escape}" />
        {if $errors.fullName}<p class="error">{$errors.fullName}{/if}</p>
    </div>
    <div class="form-group">
        <label for="course-shortName">Course Short Name</label>
        <input type="text" class="textfield form-control" name="course[shortName]" id="course-shortName" value="{$course->shortName|escape}" />
        {if $errors.shortName}<p class="error">{$errors.shortName}{/if}</p>
    </div>
    <div class="form-group">
        <label for="department">Department</label>
        <input type="text" class="textfield form-control" name="course[department]" id="department" value="{$course->department|escape}" />
    </div>
    <div class="form-group">
        <label for="facet-description">Description</label>
        <textarea class="form-control" rows="3" cols="70" name="facet[description]" id="facet-description">{$facet->description|escape}</textarea>
    </div>
    <div class="form-group">
        <label>For the purposes of this assignment, will your students have to:</label>
        {foreach item="task" key="taskId" from=$facet->GetAllTasks()}
            <div class="checkbox">
            <label for="facet-tasks-{$taskId}">
              <input type="checkbox" name="facet[tasks][{$taskId}]" id="facet-tasks-{$taskId}" value="{$task}" {if $facet->tasks.$taskId}checked="checked"{/if} /> {$task}
            </label>
            </div>
        {/foreach}
    </div>
    <div class="form-group">
    {if $course->inDataSource}
        <h3>Students in the class <small class="label label-success">{$course->students|@count} total</small></h4>
        <table class="table table-striped table-condensed table-bordered">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Last login</th>
                </tr>
            </thead>
        {foreach item='student' from=$course->students}
            <tr>
                <td><a class="" href="admin/accounts/{$student->id}?returnTo={$smarty.server.REQUEST_URI}">{$student->firstName} {$student->lastName}</a></td>
                <td>{if !$student->lastLoginDate}--{else}{$student->lastLoginDate->format('M j, d h:ia')}{/if}</td>
            </tr>
        {foreachelse}
        There are no students in this course.
        {/foreach}
        </table>
    {/if}
    </div>

<!--     <div class="course-students row">
        <div class="student-list form-control col-xs-6">
            <label for="students-observe">Students for Observation</label>
            <p class="description">These will be students who can make observations in the observation rooms.</p>
            <textarea class="form-control" rows="5" name="students-observe" id="students-observe">{$studentsObserve|escape}</textarea>
        </div>
        <div class="student-list form-control col-xs-6">
            <label for="students-participate">Students for Participation</label>
            <p class="description">These will be students who can particpate in the classrooms.</p>
            <textarea class="form-control" rows="5" name="students-participate" id="students-participate">{$studentsObserve|escape}</textarea>
        </div>
    </div> -->
    <hr>
    <div class="commands">
        {generate_form_post_key}
        <input class="btn btn-primary" type="submit" name="command[save]" value="{if $new}Create{else}Save{/if} Course" />
        <a class="btn btn-default" href="admin/courses">Cancel</a>
    </div>
</form>
