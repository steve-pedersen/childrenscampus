<h1>Manage blocked off dates</h1>
<p>A <em>blocked off date</em> is a day in which students will not be able to make any reservations. This will apply to all courses and rooms and should be re-configured as needed.</p>
<br>
<form action="{$smarty.server.REQUEST_URI}" method="post">
    <h2>View/Remove Current Blocked Dates</h2>
    <table class="table table-responsive table-bordered">
        <thead>
            <tr>
                <th> </th>
                <th>Date</th>
            </tr>
        </thead>
        {foreach from=$blockDates key='i' item='date'}
            <tr>
                <td class="checkboxes"><input type="checkbox" name="blockDates[{$i}]" id="blockDates-{$i}" value="{$i}" />
                <td><label for="blockDates-{$i}">{$date->format('M j, Y')}</label></td>
            </tr>
        {foreachelse}
            <tr>
                <td colspan="4">There are currently no blocked off dates configured in the system.</td>
            </tr>
        {/foreach}
    </table>
    {if $blockDates}
    <div class="commands">
        <input class="btn btn-danger" type="submit" name="command[remove]" id="command-remove" value="Remove Dates" />
    </div>
    {/if}
    
    <br><hr>

    <h2>Add a Blocked Date</h2>
    <div class="form-group">
        <label for="blockeddatenew">Date:</label>
        <input type="text" class="form-control textfield" name="blockeddatenew" id="blockeddatenew" />
    </div>
    
    <div class="commands">
        {generate_form_post_key}
        <input class="btn btn-primary" type="submit" name="command[add]" id="command-add" value="Add Blocked Date" />
    </div>
</form>