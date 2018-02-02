<h1>Manage Course Types</h1>

<form action="{$smarty.server.REQUEST_URI}" method="post">
<!--     <h2>Course Types</h2> -->
    <table class="table table-responsive table-bordered">
        <thead>
            <tr>
                <th> </th>
                <th>Name</th>
            </tr>
        </thead>
        {foreach from=$courseTypes item='type'}
            <tr>
                <td><input type="checkbox" name="courseTypes[{$type->id}]" id="courseTypes-{$type->id}" value="{$type->id}" />
                <td><label for="courseTypes-{$type->id}">{$type->name|escape}</label></td>
            </tr>
        {foreachelse}
            <tr>
                <td colspan="4">There are no course types configured in this system.</td>
            </tr>
        {/foreach}
    </table>
    {if $courseTypes}
    <div class="commands">
        <input class="btn btn-danger" type="submit" name="command[remove]" id="command-remove" value="Remove Selected Types" />
    </div>
    {/if}
    
    <br><hr>

    <h2>Add a Course Type</h2>
    <div class="form-group">
        <label for="name">Name:</label>
        <input type="text" class="form-control textfield" name="name" id="name" />
        {if $errors.name}<p class="error">{$errors.name}</p>{/if}
    </div>

    <div class="commands">
        <input class="btn btn-primary" type="submit" name="command[add]" id="command-add" value="Create Course Type" />
    </div>
</form>