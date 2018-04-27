<h1>Request a Course</h1>
<div class="course-instruction-container">
    {if $instructionText}
        {$instructionText}
    {else}
        <p class="instructions">
           <b>Instructions for requesting a course:</b>
        </p>
        <ol style="text-align: left">
            <li>Choosing a semester will update your available courses.</li>
            <li>Select one your courses from the list.</li>
            <li>Select the tasks that students are to perform.</li>
        </ol>
        <p><em>Enrolled students will be automatically added to your course and managed by the Children's Campus Check-In application.</em></p> 
    {/if}
    <hr>
</div>

{if $semesters}
<form method="get" action="{$smarty.server.REQUEST_URI|escape}">
    <div class="form-group">
        <label for="semester" class="field-label field-linked">Semester <span class="badge">{if $activeDisplay} {$activeDisplay}{/if}</span></label>
        <select name="semester" class="form-control" onchange="this.form.submit()" required>
        {foreach item='semester' from=$semesters}
            <option value="{$semester->id}" {if $semester->internal == $activeSemester}selected active{/if}">{$semester->display}</option>
        {/foreach}
        </select>
        {if $errors.semester}<p class="error alert alert-danger">{$errors.semester}</p>{/if}
    </div>
</form>
{/if}
<form action="{$smarty.server.REQUEST_URI}" method="post">
    <input class="hidden" name="selected-semester" value="{$selectedSemester->id}" hidden />
    <div class="form-group">
        <label for="course">Course <small> (depends on selected semester)</small></label>
        {if $courses}
        <select class="form-control" name="course" id="course-courseId" required>
            <!-- <option value="" default>Choose a course</option> -->
            {foreach item='course' from=$courses}
                <option value="{$course.id}">{$course.shortName} {$course.title}</option>
            {/foreach}
        </select>
        {else}
            <p class="alert alert-warning">You have no classes for the selected semester.</p>
        {/if}
        {if $errors.course}<p class="error alert alert-danger">{$errors.course}</p>{/if}
    </div>
    <div class="form-group">
        <label for="facet-typeId">Course Type</label>
        <select class="form-control" name="facet[typeId]" id="facet-typeId">
            <!-- <option value="">Choose a type of course</option> -->
        {foreach item='type' from=$facetTypes}
            <option value="{$type->id}"{if $facet && ($facet->typeId == $type->id)} selected="selected"{/if}>{$type->name|escape}</option>
        {/foreach}
        </select>
        {if $errors.facet_type}<p class="error alert alert-danger">{$errors.facet_type}</p>{/if}
    </div>
    <div class="form-group">
        <label>For the purposes of this assignment, will your students have to:</label>
        <table class="table table-bordered table-condensed">
            {foreach item="task" key="taskId" from=$facet->GetAllTasks()}
                <tr>
                    <td class="text-center"><input type="checkbox" name="facet[tasks][{$taskId}]" id="facet-tasks-{$taskId}" value="{$taskId}" {if $facet->tasks.$taskId}checked="checked"{/if} /></td>
                    <td><label for="facet-tasks-{$taskId}">{$task}</label></td>
                </tr>
            {foreachelse}
                <tr>
                    <td colspan="2">There are no course tasks configured in this system.</td>
                </tr>
            {/foreach}
        </table>
    </div>
    <div class="commands">
        {generate_form_post_key}
        <input class="btn btn-info" type="submit" name="command[request]" value="Request Course" />
    </div>
</form>