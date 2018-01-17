<h1>Manage Courses: {$coursesIndexTabs.$tab}</h1>
<div class="tabs">
    <ul class="tab-list">
        {foreach item='tabDisplay' key='tabIndex' from=$coursesIndexTabs}
        {if $tab == $tabIndex}
            <li class="active"><span>{$tabDisplay}</span></li>
        {else}
            <li><a href="admin/courses?tab={$tabIndex}">{$tabDisplay}</a></li>
        {/if}
        {/foreach}
    </ul>
</div>
<form method="post" action="{$smarty.server.REQUEST_URI}">
	<table class="table">
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
			<tr>
				<td class="checkbox"><input type="checkbox" name="courses[{$course->id}]" id="courses-{$course->id}" value="{$course->id}" /></td>
				<td>{$course->shortName|escape}</td>
				<td>{foreach item='teacher' from=$course->instructors}{$teacher->account->displayName|escape}{/foreach}</td>
				<td>{foreach item='facet' from=$course->facets}{$facet->type->name|escape}{/foreach}</td>
                <td>{$course->students->count}</td>
				<td>
                    <a href="admin/courses/edit/{$course->id}">edit</a>
                    <a href="admin/courses/dropstudents/{$course->id}">drop students</a>
                </td>
			</tr>
		{/foreach}
		</tbody>
	</table>
	<div class="link-controls">
        <p><a class="new" href="admin/courses/edit/new">Create a Course</a></p>
    </div>
	<div class="commands">
		<p>
            {if $tab == 'active'}
            <input type="submit" name="command[inactive]" value="Deactivate Selected" />
            {else}
            <input type="submit" name="command[active]" value="Activate Selected" />
            {/if}
			{if $diva->user->userAccount->id == 2}
            <input type="submit" name="command[remove]" value="Remove Selected" />
			{/if}
        </p>
	</div>
</form>