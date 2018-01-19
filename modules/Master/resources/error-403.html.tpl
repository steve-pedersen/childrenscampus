<h1>Sorry, you do not have permission to access the requested resource.</h1>

{if $message}<p>{$message|escape}</p>{/if}

{if !$userContext->account}
<p>You are not currently logged in to the Children's Campus Check-in application.
If you have an SFSU account, you may
wish to login to see if your account has permission to access the requested
resource.</p>

<a class="btn btn-primary" href="login">Login</a>

<!-- {include file=$loginTemplate} -->

{else}

<a class="btn btn-primary" href="/">Return to Children's Campus Check-in</a>
{/if}
