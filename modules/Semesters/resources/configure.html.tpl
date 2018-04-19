<h1>Configure Semesters</h1>

<form action="{$smarty.server.REQUEST_URI}" method="post">

    <h2 class="font-weight-light">Semesters</h2>
    <div class="form-group">
        <table class="table table-bordered table-striped">
            <thead class="thead-dark">
                <tr>
                    <th> </th>
                    <th>Semester</th>
                    <th>Start</th>
                    <th>End</th>
                    <th>Open</th>
                    <th>Close</th>
                </tr>
            </thead>
            {foreach from=$semesters item='s'}
                <tr>
                    <td class="text-center"><input type="checkbox" name="semesters[{$s->id}]" id="semesters-{$s->id}" value="{$s->id}" />
                    <td><label style="display: block;" for="semesters-{$s->id}">{$s->display}</label></td>
                    <td>{$s->startDate->format('M d, Y')}</td>
                    <td>{$s->endDate->format('M d, Y')}</td>
                    <td>{$s->openDate->format('M d, Y')}</td>
                    <td>{$s->closeDate->format('M d, Y')}</td>
                </tr>
            {foreachelse}
                <tr>
                    <td colspan="6">There are no semesters configured in this system.</td>
                </tr>
            {/foreach}
        </table>
    </div>
    <div class="form-group commands">
        <input class="btn btn-default" type="submit" name="command[remove]" id="command-remove" value="Remove Selected Semesters" />
    </div>

<br>

    <h2 class="font-weight-light">Add a Semester</h2>
    <div class="form-group">
        <label for="term">Term:</label>
        <select class="form-control" name="term" id="term">
            {foreach item='term' from=$terms}
                <option value="{$term}">{$term}</option>
            {/foreach}
        </select>
    </div>
    <div class="form-group">
        <label for="startDate">Start Date:</label>
        <input class="form-control" type="text" name="startDate" id="startDate" />
        {if $errors.startDate}<p class="error">{$errors.startDate}</p>{/if}
    </div>
    <div class="form-group">
        <label for="endDate">End Date:</label>
        <input class="form-control" type="text" name="endDate" id="endDate" />
        {if $errors.endDate}<p class="error">{$errors.endDate}</p>{/if}
    </div>
    <div class="form-group">
        <label for="openDate">Open Date <small>(when student reservations can begin)</small>:</label>
        <input class="form-control" type="text" name="openDate" id="openDate" placeholder="" />
    </div>
    <div class="form-group">
        <label for="closeDate">Close Date <small>(when student reservations end)</small>:</label>
        <input class="form-control" type="text" name="closeDate" id="closeDate" />
    </div>
    <hr>
    <div class="form-group commands">
        <input class="btn btn-primary" type="submit" name="command[add]" id="command-add" value="Create Semester" />
    </div>
{generate_form_post_key}
</form>