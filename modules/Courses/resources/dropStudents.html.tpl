<h1>Drop all students from {$course->fullName|escape}</h1>
<p>Are you sure you want ot drop all of the students from this course?</p>
<form action="{$smarty.server.REQUEST_URI|escape}" method="post">
<div class="commands">
    <p>
        <input type="submit" name="command[drop]" value="Drop Students" />
        <a class="cancel" href="{$smarty.server.HTTP_REFERER}">cancel</a>
    </p>
</div>
</form>