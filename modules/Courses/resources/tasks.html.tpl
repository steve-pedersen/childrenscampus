<h1>Manage Course Tasks</h1>

<form action="{$smarty.server.REQUEST_URI}" method="post">
    <!-- <h2>Course Tasks</h2> -->
    <table class="table table-responsive table-bordered">
        <thead>
            <tr>
                <th> </th>
                <th>Name</th>
            </tr>
        </thead>
        {foreach from=$courseTasks key=i item=task}
            <tr>
                <td><input type="checkbox" name="courseTasks[{$i}]" id="courseTasks-{$i}" value="{$i}" />
                <td><label for="courseTasks-{$i}">{$task|escape}</label></td>
            </tr>
        {foreachelse}
            <tr>
                <td colspan="4">There are no course tasks configured in this system.</td>
            </tr>
        {/foreach}
    </table>
    {if $courseTasks}
    <div class="commands">
        <input class="btn btn-danger" type="submit" name="command[remove]" id="command-remove" value="Remove Tasks" />
    </div>
    {/if}
    
    <br><hr>

    <h2>Add a Course Task</h2>
    <div class="form-group">
        <label for="name">Name:</label>
        <input type="text" class="form-control textfield" name="name" id="name" />
        {if $errors.name}<p class="error">{$errors.name}</p>{/if}
    </div>

    <div class="commands">
        {generate_form_post_key}
        <input class="btn btn-primary" type="submit" name="command[add]" id="command-add" value="Create Course Task" />
    </div>
</form>