{if $cacheExists}
<a href="admin">Administrate</a>
<h1>Clear the cache?</h1>
<p>You may wish to do this if you have modified the permissions in the database, or if you suspect the
permissions (or anything else that DIVA caches) have gotten out of whack. In general, this should not
harm anything, but it might slow down DIVA for a little while, since the cache will need to be 
rebuilt.</p>

<form method="post" action="{$smarty.server.REQUEST_URI|escape}">
<p>{generate_form_post_key}<input type="submit" name="clear" value="Clear the cache"></p>
</form>
{else}
<h1>No cache detected.</h1>
<p>Is the cache not installed? Or is something else doing caching? This is designed for APC.</p>
{/if}
