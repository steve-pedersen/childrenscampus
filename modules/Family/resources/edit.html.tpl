<h1>{if $new}Create Family Purpose{else}Edit Family Purpose: {$purpose->name|escape}{/if}</h1>
<form action="{$smarty.server.REQUEST_URI}" method="post">
    <dl>
        <dt><label for="purpose-name">Name:</label></dt>
        <dd><input class="textfield" type="text" name="purpose[name]" id="purpose-name" value="{$purpose->name}" /></dd>
		{if $errors.name}<dd class="error">{$errors.name}</dd>{/if}
    </dl>
    <div class="commands">
    	{generate_form_post_key}
        <p><input type="submit" name="command[save]" value="Save" /></p>
    </div>
</form>