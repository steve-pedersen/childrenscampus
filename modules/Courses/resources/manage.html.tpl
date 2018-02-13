<h1>Manage Courses: <small>{$coursesIndexTabs.$tab}</small></h1>
<div class="tabs">
	<ul class="nav nav-tabs nav-justified">
        {foreach item='tabDisplay' key='tabIndex' from=$coursesIndexTabs}
        {if $tab == $tabIndex}
            <li role="presentation" class="active"><a href="admin/courses?tab={$tabIndex}"><strong>{$tabDisplay}</strong></a></li>
        {else}
            <li role="presentation"><a class="text-muted" href="admin/courses?tab={$tabIndex}">{$tabDisplay}</a></li>
        {/if}
        {/foreach}
	</ul>
</div>
<br><br>
<form method="post" action="{$smarty.server.REQUEST_URI}">
	<table class="table table-responsive table-striped table-bordered">
		<thead>
			<tr>
				<th> </th>
				<th>Short Name</th>
				<th>Instructor</th>
				<th>Type</th>
                <th>Students</th>
				<th>Actions</th>
			</tr>
		</thead>
		<tbody>
		{foreach item='course' from=$courses}
		{if !$course->deleted}
			<tr>
				<td class="checkboxes"><label for="courses-{$course->id}"><input type="checkbox" name="courses[{$course->id}]" id="courses-{$course->id}" value="{$course->id}" /></label></td>
				<td><label for="courses-{$course->id}">{$course->shortName|escape}</label></td>
				<td>
					{foreach item='teacher' from=$course->teachers}
						<a href="admin/accounts/{$teacher->id}?returnTo={$smarty.server.REQUEST_URI}">{$teacher->firstName} {$teacher->lastName}</a>
					{/foreach}
				</td>
				<td>{foreach item='facet' from=$course->facets}{$facet->type->name|escape}{/foreach}</td>
                <td>{$course->students|@count}</td>
				<td>
					{if $tab == 'inactive'}
						<a href="admin/courses/queue/{$course->id}" class="btn btn-xs btn-default">view</a>
					{/if}
                    <a href="admin/courses/edit/{$course->id}" class="btn btn-xs btn-default">edit</a>
                    <!-- <a href="admin/courses/dropstudents/{$course->id}" class="btn btn-xs btn-default">drop students</a> -->
                </td>
			</tr>
		{/if}
		{foreachelse}
			<tr>
				<td colspan="6">---</td>
			</tr>
		{/foreach}
		</tbody>
	</table>
	<br><br>
	{if $courses}
	<div class="commands form-group">
		{generate_form_post_key}
        {if $tab == 'active'}
        <input class="btn btn-info" type="submit" name="command[inactive]" value="Deactivate Selected" />
        {else}
        <input class="btn btn-primary" type="submit" name="command[active]" value="Activate Selected" />
        {/if}
		{if $pAdmin}
        <input class="btn btn-danger" type="submit" name="command[remove]" value="Remove Selected" />
		{/if}
		<hr>
	</div>
	{else}
	<p>No courses found.</p>
	{/if}
</form>
<br>
<div class="link-controls form-group">
	<a class="new btn btn-success" href="admin/courses/edit/new"><span class="glyphicon glyphicon-plus"></span> Create a Course</a>
</div>