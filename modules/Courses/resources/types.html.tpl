<h1>Manage Course Types</h1>

<form action="{$smarty.server.REQUEST_URI}" method="post">
    <h2>Course Types</h2>
    <table class="table">
        <thead>
            <tr>
                <th> </th>
                <th>Name</th>
            </tr>
        </thead>
        {foreach from=$courseTypes item='type'}
            <tr>
                <td class="checkbox"><input type="checkbox" name="courseTypes[{$type->id}]" id="courseTypes-{$type->id}" value="{$type->id}" />
                <td><label for="courseTypes-{$type->id}">{$type->name|escape}</label></td>
            </tr>
        {foreachelse}
            <tr>
                <td colspan="4">There are no course types configured in this system.</td>
            </tr>
        {/foreach}
    </table>
    <div class-"commands">
        <p><input type="submit" name="command[remove]" id="command-remove" value="Remove Selected Semesters" /></p>
    </div>

    <h2>Add a Course Type</h2>
    <dl>
        <dt><label for="name">Name:</label></dt>
        <dd><input type="text" class="textfield" name="name" id="name" /></dd>
        {if $errors.name}<dd class="error">{$errors.name}</dd>{/if}
    </dl>

    <div class-"commands">
        <p><input type="submit" name="command[add]" id="command-add" value="Create Course Type" /></p>
    </div>
</form>