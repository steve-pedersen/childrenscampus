<h1>Site notice</h1>
<p>
    When set, this message appears on every page in this application. It can be used for
    important announcements, such as for impending downtime. Be careful not
    to over-use this to avoid annoying users. Some HTML is OK.
</p>

<form method="post" action="{$smarty.server.REQUEST_URI|escape}" class="data-entry">
    <div>
        <div class="form-group">
            <label for="siteNotice" class="field-label field-linked">Site notice:</label>
            <textarea class="form-control simple-wysiwyg" id="siteNotice" name="siteNotice" rows="5" cols="72">{$siteNotice|escape}</textarea>
        </div>
        
        <div class="form-group commands">
            {generate_form_post_key}
            <input type="submit" class="btn btn-primary" name="command[set]" value="Set notice">
            <a class="btn btn-link" href="admin">Cancel</a>
            
        </div>
    </div>
</form>
