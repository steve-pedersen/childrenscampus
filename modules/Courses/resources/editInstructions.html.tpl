<h1>Edit course request instructions</h1>

<form action="{$smarty.server.REQUEST_URI}" method="post">

    <div class="form-group">
        <textarea class="form-control text-field wysiwyg" name="instructions" id="instructions" rows=10>{$instructions}</textarea>
    </div>

    <div class="commands">
        <input class="btn btn-primary" type="submit" name="command[save]" id="command-save" value="Save Instructions" />
    </div>
</form>