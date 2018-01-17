<h1>Configure Semesters</h1>

<form action="{$smarty.server.REQUEST_URI}" method="post">
    <h2>Semesters</h2>
    <table class="table">
        <thead>
            <tr>
                <th> </th>
                <th>Semester</th>
                <th>Start</th>
                <th>End</th>
            </tr>
        </thead>
        {foreach from=$semesters item='s'}
            <tr>
                <td class="checkbox"><input type="checkbox" name="semesters[{$s->id}]" id="semesters-{$s->id}" value="{$s->id}" />
                <td><label for="semesters-{$s->id}">{$s->display}</label></td>
                <td>{$s->startDate->getDate()|date_format}</td>
                <td>{$s->endDate->getDate()|date_format}</td>
            </tr>
        {foreachelse}
            <tr>
                <td colspan="4">There are no semesters configured in this system.</td>
            </tr>
        {/foreach}
    </table>
    <div class-"commands">
        <p><input type="submit" name="command[remove]" id="command-remove" value="Remove Selected Semesters" /></p>
    </div>

    <h2>Add a Semester</h2>
    <dl>
        <dt><label for="semester">Semester:</label></dt>
        <dd><select name="semester" id="semester">
            {foreach item='term' from=$terms}
                <option value="{$term}">{$term}</option>
            {/foreach}
            </select>
        </dd>
        <dt><label for="year">Year:</label></dt>
        <dd><select name="year" id="year">
            {foreach item='year' from=$years}
                <option value="{$year}">{$year}</option>
            {/foreach}
            </select>
        </dd>
        {if $errors.display}<dd class="error">{$errors.display}</dd>{/if}
        <dt><label for="startDate">Start Date:</label></dt>
        <dd><input type="text" name="startDate" id="startDate" /></dd>
        {if $errors.startDate}<dd class="error">{$errors.startDate}</dd>{/if}
        <dt><label for="endDate">End Date:</label></dt>
        <dd><input type="text" name="endDate" id="endDate" /></dd>
        {if $errors.endDate}<dd class="error">{$errors.endDate}</dd>{/if}
    </dl>

    <div class-"commands">
        <p><input type="submit" name="command[add]" id="command-add" value="Create Semester" /></p>
    </div>
</form>