<h1>Drop {$student->displayName|escape} from {$course->fullName|escape}</h1>
<p>Are you sure you want ot drop this student?</p>
<form action="{$smarty.server.REQUEST_URI|escape}" method="post">
<div class="commands">
	{generate_form_post_key}
    <p>
        <input type="submit" name="command[drop]" value="Drop Student" />
        <a class="cancel" href="{$smarty.server.HTTP_REFERER}">cancel</a>
    </p>
</div>
</form>