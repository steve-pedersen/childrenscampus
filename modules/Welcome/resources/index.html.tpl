{if !$userContext->account}
<h1>{if $welcomeTitle}{$welcomeTitle}{else}Hardcoded: Welcome to the Children's Campus Check-In Application{/if}</h1>
{/if}
<!-- NOTE: Place divs inside of conditionals, or else within isLoggedIn check -->

{if $userContext->account}
	<div id="notice-warning" class="alert alert-warning">
	{if $noticeWarning}
		{$noticeWarning|allow_basic_html}
	{else}
	    <p>Hardcoded noticeWarning</p>
	{/if}
	</div>
	<div id="notice-message" class="alert alert-success">
	{if $noticeMessage}
		{$noticeMessage|allow_basic_html}
	{else}
	    <p>Hardcoded noticeMessage</p>
	{/if}
	</div>
{/if}

<div id="welcome-text">
{if $welcomeText}
{$welcomeText|allow_basic_html}
{else}
    <p>Hardcoded welcomeText</p>
{/if}
</div>

{if $userContext->account}
	<div id="welcome-text-extended">
	{if $welcomeTextExtended}
	{$welcomeTextExtended|allow_basic_html}
	{else}
	    <p>Hardcoded welcomeTextExtended</p>
	{/if}
	</div>

	<div id="location-message">
	{if $locationMessage}
	{$locationMessage|allow_basic_html}
	{else}
	    <p>Hardcoded locationMessage</p>
	{/if}
	</div>
{/if}

{if !$userContext->account}
<div class="welcome-module">
    <a href="{$app->baseUrl('login')}" class="btn btn-primary">Click to Log In</a>
</div>
{/if}
