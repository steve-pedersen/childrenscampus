<form class="form-horizontal" action="{$smarty.server.REQUEST_URI}" method="post">

    <div class="form-group">
        <div class="col-xs-12">
            <h1>Welcome module</h1>
            <p>Determine the text to show on welcome/home page.</p>
        </div>
    </div>

    <div class="announcements-section">
        <h2>Site announcements <small> for homepage</small></h2>
        <table class="table table-responsive table-bordered">
            <thead>
                <tr>
                    <th> </th>
                    <th>Announcement</th>
                </tr>
            </thead>
            {foreach from=$announcements key='i' item='announce'}
                <tr class="announcements-table">
                    <td class="checkboxes"><input type="checkbox" name="announcements[{$i}]" id="announcements-{$i}" value="{$i}" />
                    <td><label for="announcements-{$i}">{$announce}</label></td>
                </tr>
            {/foreach}
        </table>

        {if $announcements}
        <div class="commands">
            <input class="btn btn-danger" type="submit" name="command[remove]" id="command-remove" value="Remove Announcements" />
        </div>
        {/if}

        <hr>

        <div class="form-group">
            <div class="col-xs-12">
                <label for="announcement-new">New announcement</label>
                <textarea class="form-control text-field wysiwyg" name="announcement" id="announcement-new" rows="3"></textarea>
            </div>
        </div>

        <div class="commands">
            <input class="btn btn-primary" type="submit" name="command[add]" id="command-add" value="Create Announcement" />
        </div>
    </div>
    <hr>

    <div class="form-group">
        <div class="col-xs-12">
            <label for="welcome-title">Welcome Title</label>
            <textarea class="form-control text-field wysiwyg" name="welcome-title" id="welcome-title" rows="3">{$welcomeTitle}</textarea>
        </div>
    </div>        
    <div class="form-group">
        <div class="col-xs-12">
            <label for="welcome-text">Welcome Text</label>
            <textarea class="form-control text-field wysiwyg" name="welcome-text" id="welcome-text" rows="{if $welcomeText}{$welcomeText|count_paragraphs*2}{else}6{/if}">{$welcomeText}</textarea>
        </div>
    </div>
    <div class="form-group">
        <div class="col-xs-12">
            <label for="welcome-text-extended">Welcome Text Extended (Bullet List)</label>
            <textarea class="form-control text-field wysiwyg" name="welcome-text-extended" id="welcome-text-extended" rows="{if $welcomeTextExtended}{$welcomeTextExtended|count_sentences*2}{else}6{/if}">{$welcomeTextExtended}</textarea>
        </div>
    </div>
    <div class="form-group">
        <div class="col-xs-12">
            <label for="location-message">Location Text to display underneath image of Children's Campus</label>
            <textarea class="form-control text-field wysiwyg" name="location-message" id="location-message" rows="{if $locationMessage}{$locationMessage|count_paragraphs*2}{else}6{/if}">{$locationMessage}</textarea>
        </div>
    </div>
  
    <div class="form-group">
        <div class="col-xs-12">
            <label for="contact-info">Contact Information (applies to right sidebar only)</label>
            <textarea class="form-control text-field wysiwyg" name="contact-info" id="contact-info" rows="{if $contactInfo}{$contactInfo|count_paragraphs*2}{else}8{/if}">{$contactInfo}</textarea>
        </div>
    </div>   

    <div class="form-group">
        <div class="col-xs-12">
            <div class="controls">
                {if $module->inDataSource}<input type="hidden" name="module[id]" value="{$module->id}" />{/if}
                <input class="btn btn-primary" type="submit" name="command[save]" value="{if $module->inDatasource}Save{else}Create{/if}" />
                <a class="cancel btn btn-link" href="">Cancel</a>
        </div>
        </div>
    </div>
{generate_form_post_key}
</form>
