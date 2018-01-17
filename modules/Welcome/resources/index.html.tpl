<h1>Hardcoded: Welcome to the Children's Campus Check-In Application</h1>

<div id="welcome-text">
{if $welcomeText}
{$welcomeText|allow_basic_html}
{else}
    <p>Hardcoded welcomeText</p>
{/if}
</div>

{if !$userContext->account}
<div class="welcome-module">
    <a href="{$app->baseUrl('login')}" class="btn btn-primary">Click to Log In</a>
</div>
{/if}
