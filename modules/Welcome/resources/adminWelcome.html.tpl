<form class="form-horizontal" action="{$smarty.server.REQUEST_URI}" method="post">

    <div class="form-group">
        <div class="col-xs-12">
            <h1>Welcome module</h1>
            <p>Determine the text to show on welcome/home page.</p>
        </div>
    </div>
        
    <div class="form-group">
        <div class="col-xs-12">
            <label for="welcome-text">Welcome Text</label>
            <textarea class="form-control text-field wysiwyg" name="welcome-text" id="welcome-text" rows="3">{$welcomeText}</textarea>
        </div>
    </div>
       
    <div class="form-group">
        <div class="col-xs-12">
            <div class="controls">
                {generate_form_post_key}
                {if $module->inDatasource}<input type="hidden" name="module[id]" value="{$module->id}" />{/if}
                <input class="btn btn-primary" type="submit" name="command[save]" value="{if $module->inDatasource}Save{else}Create{/if}" />
                <a class="cancel btn btn-link" href="">Cancel</a>
        </div>
        </div>
    </div>
</form>
