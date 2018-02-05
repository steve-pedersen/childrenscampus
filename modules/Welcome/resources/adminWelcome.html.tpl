<form class="form-horizontal" action="{$smarty.server.REQUEST_URI}" method="post">

    <div class="form-group">
        <div class="col-xs-12">
            <h1>Welcome module</h1>
            <p>Determine the text to show on welcome/home page.</p>
        </div>
    </div>

    <div class="form-group">
        <div class="col-xs-12">
            <label for="welcome-title">Welcome Title</label>
            <textarea class="form-control text-field wysiwyg" name="welcome-title" id="welcome-title" rows="2">{$welcomeTitle}</textarea>
        </div>
    </div>        
    <div class="form-group">
        <div class="col-xs-12">
            <label for="welcome-text">Welcome Text</label>
            <textarea class="form-control text-field wysiwyg" name="welcome-text" id="welcome-text" rows="4">{$welcomeText}</textarea>
        </div>
    </div>
    <div class="form-group">
        <div class="col-xs-12">
            <label for="welcome-text-extended">Welcome Text Extended (Bullet List)</label>
            <textarea class="form-control text-field wysiwyg" name="welcome-text-extended" id="welcome-text-extended" rows="4">{$welcomeTextExtended}</textarea>
        </div>
    </div>
    <div class="form-group">
        <div class="col-xs-12">
            <label for="notice-warning">Warning Notice (yellow background)</label>
            <textarea class="form-control text-field wysiwyg" name="notice-warning" id="notice-warning" rows="2">{$noticeWarning}</textarea>
        </div>
    </div>
    <div class="form-group">
        <div class="col-xs-12">
            <label for="notice-message">Regular Notice (green background)</label>
            <textarea class="form-control text-field wysiwyg" name="notice-message" id="notice-message" rows="2">{$noticeMessage}</textarea>
        </div>
    </div>
    <div class="form-group">
        <div class="col-xs-12">
            <label for="location-message">Location Text to display underneath image of Children's Campus</label>
            <textarea class="form-control text-field wysiwyg" name="location-message" id="location-message" rows="3">{$locationMessage}</textarea>
        </div>
    </div>
      
    <div class="form-group">
        <div class="col-xs-12">
            <div class="controls">
                {if $module->inDatasource}<input type="hidden" name="module[id]" value="{$module->id}" />{/if}
                <input class="btn btn-primary" type="submit" name="command[save]" value="{if $module->inDatasource}Save{else}Create{/if}" />
                <a class="cancel btn btn-link" href="">Cancel</a>
        </div>
        </div>
    </div>
{generate_form_post_key}
</form>
