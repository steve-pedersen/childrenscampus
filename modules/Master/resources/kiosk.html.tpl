<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html lang="en" xml:lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
{$metaRedirect}
<title>Children's Campus at SF State{if $pageTitle} - {$pageTitle}{/if}</title>
<base href="{$siteUrl}/" />

<script type="text/javascript">
    diva = {literal}{}{/literal};
    diva.baseURL = '{$siteUrl}';
    diva.controllerName = '{$controllerName}';
    diva.actionName = '{$actionName}';
</script>
        

{foreach item="cssFile" from=$cssFiles}
{if $cssFile.ieVersion}
		<!--[if {$cssFile.ieVersion}]>
{/if}
		<link rel="{$cssFile.rel}" type="text/css" href="{$cssFile.href}{$cacheKiller}"{if $cssFile.title} title="{$cssFile.title}{/if}{if $cssFile.media} media="{$cssFile.media}"{/if} />
{if $cssFile.ieVersion}
		<![endif]-->
{/if}
{/foreach}

</head>



<body id="kiosk-page">
<div id="jumplinks">
	<a href="#content">Skip to content</a>
	<a href="#resources">Skip to resources column</a>
</div>


<div id="pgTop">
	<!-- pgTopContainer -->
	<div id="pgTopContainer">
		<div id="sfsuSearch">

			<a href="http://www.sfsu.edu/" class="sfsuHomeLink">San Francisco State University</a>
			<a href="http://www.sfsu.edu/">Home</a>
			<a href="http://www.sfsu.edu/calendar/">Calendar</a>
			<a href="http://www.sfsu.edu/atoz/">A–Z Index</a>
			<a href="http://www.sfsu.edu/search.htm">Search Tools</a>

			
			<form action="http://google.sfsu.edu/search" method="get" title="Search SF State" style="display: inline; margin-left: 20px;">
			<div style="display: inline;">
			<label for="search" style="position: absolute; left: -9999px;">Search SF State</label>
			<input id="search" name="q" size="12" maxlength="50" value="" type="text" />
			<input src="{$diva->Link('images/searchbutton.png')}" style="vertical-align: middle; border: none; padding: none; background-color: #333;" alt="Search SF State" type="image" />
			<input name="method" value="and" type="hidden" />
			<input name="format" value="builtin-short" type="hidden" />
			<input name="config" value="htdig" type="hidden" />

			<input name="restrict" value="" type="hidden" />
			</div>
			</form>
		</div>
		
		<div id="masthead"><a href="index.php">Children's Campus at SF State</a></div>
	</div>
	<!-- end pgTopContainer -->
</div> 


<div id="filmstrip"><img src="{$diva->Link('images/imagebox.jpg')}" alt="Children's Campus Banner" /></div> <!-- end filmstrip div -->


<div id="container">

<div id="page">

<div id="content">
<div class="callOutBox kiosk">
{if $reservation}
	<h2>You have checked in for your observation in room:</h2>
	<p>{$reservation->room->name|escape}</p>
	<p>On {$reservation->observation->startTime->getDate()|date_format:"%b %e, %Y at %I:%M %p"}</p>
	<div class="kiosk-link">
		<a href="auth/logout">OK</a>
	</div>
{elseif $checkedOut}
	<h2>You have successfully checked out</h2>
	<div class="kiosk-link">
		<a href="auth/logout">OK</a>
	</div>
{elseif $late || $early}
	{if $late}
		<div class="flash"><div class="message error"><p>You are late for your appointment at <strong>{$late.time->getDate()|date_format:"%b %e, %Y at %I:%M %p"}</strong>.  You must sign up again to reserve time for observations.</p></div></div>
	{/if}
	{if $early}
		<p>You are early for your appointment at {$early.time->getDate()|date_format:"%b %e, %Y at %I:%M %p"}. Please come back at time closer to your appointment.</p>
	{/if}
	<div class="kiosk-link">
		<a href="auth/logout">OK</a>
	</div>
{elseif $empty}
	<h2>No reservations found</h2>
	<p>We have not found any reservation for you.</p>
	<div class="kiosk-link">
		<a href="auth/logout">OK</a>
	</div>
{elseif $earlycheckout}
	<h2>Cannot checkout too early</h2>
	<p>You must wait at least five minutes before you can checkout.</p>
	<div class="kiosk-link">
		<a href="auth/logout">OK</a>
	</div>
{else}
	<h2>Sign-In Here</h2>
	<form method="post" action="auth/kiosk">
		<div id="loginForm">
			{if $loginError}
				<div class="flash"><div class="message error"><p>We could not find any accounts which match the ID and password.</p></div></div>
			{/if}
			<input type="hidden" name="returnTo" value="{$smarty.server.REQUEST_URI|escape}" />
			<dl>
				<dt><label for="login_email">SF State ID: </label></dt>
				<dd><input type="text" class="textfield" name="email" id="login_email" value="{$email|escape}" /></dd>
				<dt><label for="login_password">Password: </label></dt>
				<dd><input type="password" name="password" class="textfield" id="login_password" /></dd>
			</dl>
			<div class="kiosk-link">
				<input type="submit" name="login_submit" value="Login" />
			</div>
		</div>
	</form>
{/if}
</div>
</div> <!-- end the content div -->


<br style="clear: both;" /></div> <!-- end page div -->
</div> <!-- end container div -->



<!-- pgBot -->

<div id="pgBot">
	<!-- pgBotContainer -->
	<div id="pgBotContainer">
		<div id="footer">
			<a href="http://www.sfsu.edu">SF State Home</a>
			<a href="http://www.sfsu.edu/emailref.htm">Contact</a>
			1600 Holloway Avenue, San Francisco, CA 94132 . Tel (415) 338-1111
		</div>
		<div id="footerLogo"><a href="http://www.sfsu.edu"><img src="{$diva->Link('images/sfsuLogo.png')}" alt="San Francisco State University" /></a></div>

		<div style="clear: both;"></div>	
	</div>
	<!-- end pgBotContainer -->
</div> 
<!-- end pgBot -->
{foreach item="jsFile" from=$javaScriptFiles}
		<script type="text/javascript" src="{$jsFile}{$cacheKiller}"></script>
{/foreach}

{if $javaScriptCodeFragments}
		<script type="text/javascript">// <![CDATA[
{foreach item="jsCode" from=$javaScriptCodeFragments}
			{$jsCode}
{/foreach}

		// ]]>
		</script>
{/if}

</body>
</html>
