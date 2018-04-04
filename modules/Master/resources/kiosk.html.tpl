<!DOCTYPE html>
{assign var="appName" value=$app->configuration->appName}
<html lang="en">
	<head>
{*may not be needed*}		<meta charset="utf-8">
{*may not be needed*}    	<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<title>{if $pageTitle}{$pageTitle|escape} &mdash; {/if}{$appName|escape}</title>
		<base href="{$baseUrl|escape}/">
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
		{$metaRedirect}
		<link rel="stylesheet" type="text/css" href="assets/less/master.less.css">
    <link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
		<link rel="stylesheet" type="text/css" href="assets/css/bootstrap-accessibility.css">
		<link href='//fonts.googleapis.com/css?family=Montserrat:400,700' rel='stylesheet' type='text/css'>
		<link rel="stylesheet" type="text/css" href="assets/css/print.css" media="print">
		<link rel="stylesheet" type="text/css" href="assets/css/app-js.css" media="screen">
		<link rel="stylesheet" type="text/css" href="assets/css/jquery.timepicker.min.css">
		<!-- <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous"> -->
		<!-- <script>document.write('<link rel="stylesheet" type="text/css" href="assets/css/app-js.css" media="screen">');</script>  -->
	</head>



	<body id="kiosk-page">
		<a href="{$smarty.server.REQUEST_URI}#content" class="sr-only sr-only-focusable">Skip Navigation</a>
		<header class="at">
	        <nav class="navbar navbar-default navbar-static-top" role="navigation">
	          <div class="container-fluid">

	            <!-- Brand and toggle get grouped for better mobile display -->
	            <div class="navbar-header">
<!-- 	              <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
	                <span class="sr-only">Toggle navigation</span>
	                <span class="icon-bar"></span>
	                <span class="icon-bar"></span>
	                <span class="icon-bar"></span>
	              </button> -->
	              <a class="navbar-brand" href="{$baseUrl}">Children's Campus at SF State:</a>
	              <p class="navbar-text" id="navbar-subheading">Check-In Application</p>
	            </div> 
	            {if $viewer}
	            <div class="navbar-collapse collapse">	
		            <ul class="nav navbar-nav navbar-right">
						{if $viewer}
						<li>
							<div class="navbar-text text-capitalize text-center">Hello, {$userContext->account->firstName|escape}</div>
						</li>
						{if $pAdmin}
						<li>
							<a class="manage-link btn btn-link" href="admin"><i class="halflings-icon white cog" aria-hidden="true"></i> Administrate</a>
						</li>
						{/if}
						<li style="text-align:center;">
							<form method="post" action="logout">
								<button class="btn btn-link logout navbar-btn" type="submit" name="command[logout]" id="logout-button" value="Logout">Logout</button>
							</form>
						</li>
						{else}
						<li class="{if $activeTab=='login'} active{/if}">
							<a class="btn btn-link login-button" data-toggle="modal" href="login">Login</a>
						</li> 
						{/if} 
		            </ul>
		        </div>
		        {/if}
	            <!-- Collect the nav links, forms, and other content for toggling -->
	          </div><!-- /.container-fluid -->
	        </nav>
	        <div id="filmstrip"><img class="img-responsive" src="assets/images/imagebox.jpg" alt="Children's Campus Banner"></div>
	        <div class="bc">
				{if $breadcrumbList}
				<div class="container">
					<ol class="at breadcrumb">
						{foreach name="breadcrumbs" item="crumb" from=$breadcrumbList}
						<li{if $smarty.foreach.breadcrumbs.last} class="active"{elseif $smarty.foreach.breadcrumbs.first} class="first"{/if}>
						{l text=$crumb.text href=$crumb.href}
						{if !$smarty.foreach.breadcrumbs.last}{/if}
						</li>
						{/foreach}
					</ol>
				</div>
				{/if}
	        </div>
	    </header>
		{if $app->siteSettings->siteNotice}
		<div class="site-notice action notice">
			{$app->siteSettings->siteNotice}
		</div> 
		{/if}

		{if $flashContent}
		<div id="user-message" class="alert alert-success alert-dismissable">
			<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
			<div class="primary">{$flashContent}</div>
		</div> 
		{/if}

		{if $userMessageList}
		<div id="user-message" class="alert alert-success alert-dismissable">
			<button type="button" class="btn btn-primary btn-sm" data-dismiss="alert" aria-hidden="true">&times;</button>
			{foreach item="msg" from=$userMessageList}
			<div class="primary">{$msg.primary}</div>
			{foreach item="detail" from=$msg.details}<div class="detail">{$detail}</div>{/foreach}
			{/foreach}
		</div> 
		{/if}


<div id="container" class="container">

<div id="row" class="row">

<div id="content kiosk-content" class="col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2 col-xs-10 col-xs-offset-1">

<div class="callOutBox kiosk text-center">

{if $reservation}
	<h2>You have checked in for your observation in room:</h2>
	<p>{$reservation->room->name|escape}</p>
	<p>On {$reservation->observation->startTime|date_format:"%b %e, %Y at %I:%M %p"}</p>
	<div class="kiosk-link">
		<form method="post" action="logout">
			<button class="btn btn-default" type="submit" name="command[logout]" id="logout-button" value="Logout">Logout</button>
		</form>
	</div>

{elseif $checkedOut}
	<h2>You have successfully checked out</h2>
	<div class="kiosk-link">
		<form method="post" action="logout">
			<button class="btn btn-default" type="submit" name="command[logout]" id="logout-button" value="Logout">Logout</button>
		</form>
	</div>

{elseif $late || $early}
	{if $late}
		<div class="flash"><div class="message error"><p>You are late for your appointment at <strong>{$late.time|date_format:"%b %e, %Y at %I:%M %p"}</strong>.  You must sign up again to reserve time for observations.</p></div></div>
	{/if}
	{if $early}
		<p>You are early for your appointment at {$early.time|date_format:"%b %e, %Y at %I:%M %p"}. Please come back at time closer to your appointment.</p>
	{/if}
	<div class="kiosk-link">
		<form method="post" action="logout">
			<button class="btn btn-default" type="submit" name="command[logout]" id="logout-button" value="Logout">Logout</button>
		</form>
	</div>

{elseif $empty}
	<h2>No reservations found</h2>
	<p>We have not found any reservation for you.</p>
	<div class="kiosk-link">
		<form method="post" action="logout">
			<button class="btn btn-default" type="submit" name="command[logout]" id="logout-button" value="Logout">Logout</button>
		</form>
	</div>

{elseif $earlycheckout}
	<h2>Cannot checkout too early</h2>
	<p>You must wait at least five minutes before you can checkout.</p>
	<div class="kiosk-link">
		<form method="post" action="logout">
			<button class="btn btn-default" type="submit" name="command[logout]" id="logout-button" value="Logout">Logout</button>
		</form>
	</div>

{else}
	<h2>Sign-In Here</h2>
	<form method="post" action="login/complete/sfsu-pw">
		<div id="loginForm">
			{if $loginError}
				<div class="flash"><div class="message error"><p>We could not find any accounts which match the ID and password.</p></div></div>
			{/if}
			<input type="hidden" name="returnTo" value="{$smarty.server.REQUEST_URI|escape}" />
            <div class="form-group">
                <input type="text" class="form-control" id="password-username" name="username" placeholder="SF State ID or Email" alt="SF State ID or Email">
            </div>

            <div class="form-group">
                <input type="password" class="form-control" id="password-password" name="password" placeholder="Password" alt="password">
            </div>
               
            <div class="form-group">
                {generate_form_post_key}
                <button class="command-button btn btn-primary" type="submit" name="command[login]">Login</button>
            </div>
		</div>
	</form>
<!-- 	<form method="post" action="login/password?c=%2Flogin%2Fcomplete%2Fsfsu-pw">
		<div id="loginForm">
			{if $loginError}
				<div class="flash"><div class="message error"><p>We could not find any accounts which match the ID and password.</p></div></div>
			{/if}
			<input type="hidden" name="returnTo" value="{$smarty.server.REQUEST_URI|escape}" />
			<dl>
				<dt><label for="login_email">SF State ID: </label></dt>
				<div class="row">
				<dd class="col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2 col-xs-10 col-xs-offset-1"><input type="text" class="form-control" name="email" id="login_email" value="{$email|escape}" /></dd>
				</div>
				<dt><label for="login_password">Password: </label></dt>
				<div class="row">
				<dd class="col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2 col-xs-10 col-xs-offset-1"><input type="password" name="password" class="form-control" id="login_password" /></dd>
				</div>
			</dl>
			<div class="kiosk-link">
				<input class="btn btn-default" type="submit" name="login_submit" value="Login" />
			</div>
		</div>
	</form> -->
{/if}
</div>
</div> <!-- end the content div -->


<br style="clear: both;" /></div> <!-- end page div -->
</div> <!-- end container div -->



    <footer class="sticky-footer">
      <div class="at-footer">
        <div class="container">
          <div class="row">
            <div class="info">
              <h1>Maintained by <a href="http://at.sfsu.edu" class="title">Academic Technology</a></h1>
              <p>Academic Technology supports and advances effective learning, teaching, scholarship, and community service with technology.</p>
            </div>
            <div class="learn-more">
              <div class="row">
                <div class="half">
                  <h2>We Also Work On</h2>
                  <ul>
                    <li><a href="https://ilearn.sfsu.edu/">iLearn</a></li>
                    <li><a href="http://at.sfsu.edu/labspace">Labspace</a></li>
                    <li><a href="http://at.sfsu.edu/coursestream">CourseStream</a></li>
                  </ul>
                </div>
                <div class="half">
                  <h2>Need Help?</h2>
                  <ul>
                    <li>(415) 405-5555</li>
                    <li>ilearn@sfsu.edu</li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="footer">
        <div class="container">
          <div id="footer-row" class="row">
            <div id="contact-university" class="col-sm-6">
              <a href="http://www.sfsu.edu/"> <img src="assets/images/logo.png" alt="San Francisco State University Logo" width="50" class="logo"></a>
              <ul class="list-unstyled ">
                <li><a href="http://www.sfsu.edu/">San Francisco State University</a></li>
                <li class="first"><a href="http://www.calstate.edu/">A California State University Campus</a></li>
              </ul>
            </div>
            <div id="contact-local" class="col-sm-6">
              <ul class="list-unstyled">
                <li><strong>Academic Technology</strong></li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </footer>
		<script src="assets/js/app.js?modified=2015060200"></script>
	</body>
</html>
