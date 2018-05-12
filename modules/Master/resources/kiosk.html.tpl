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
	</head>



<body id="kiosk-page">
	<a href="{$smarty.server.REQUEST_URI}#content" class="sr-only sr-only-focusable">Skip Navigation</a>
	<header class="at">
        <nav class="navbar navbar-default navbar-static-top" role="navigation">
          <div class="container-fluid">

            <!-- Brand and toggle get grouped for better mobile display -->
            <div class="navbar-header">
              <a class="navbar-brand" href="{$baseUrl}">Children's Campus at SF State:</a>
              <p class="navbar-text" id="navbar-subheading">Check-In Application</p>
            </div> 
            {if $viewer}
            <div class="navbar-collapse collapse">	
	            <ul class="nav navbar-nav navbar-right">
					{if $viewer}
					{if $pAdmin}
					<li>
						<div class="navbar-text text-capitalize text-center">Hello, {$userContext->account->firstName|escape}</div>
					</li>
					<li>
						<a class="manage-link btn btn-link" href="admin"><i class="halflings-icon white cog" aria-hidden="true"></i> Administrate</a>
					</li>
					<li style="text-align:center;">
						<a href="kiosk/logout" class="btn btn-default">Logout</a>
					</li>
					{/if}
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
<!-- <div id="content kiosk-content" class="col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2 col-xs-10 col-xs-offset-1">
<div class="callOutBox kiosk text-center"> -->
<div id="kioskContainer">
	<div class="wrapper text-center">

{if $shibbolethLogout}

<iframe src="{$shibbolethLogout}" width="0" height="1px" style="border:none;"></iframe>
<div class="check-in-content">
	<div class="logout-img">
		<img src="assets/images/ccheckin-observation2.jpg" class="img-responsive" alt="Children's playroom">
	</div>
	<div class="logout-info">
		<h2>Logging out...</h2>
		<p></p>
	</div>
</div>

{else} <!-- Show Kiosk Logged in/out -->

	{if $reservation}
		<div class="check-in-content">
			<div class="logout-img">
				<img src="assets/images/ccheckin-observation2.jpg" class="img-responsive" alt="Children's playroom">
			</div>
			<div class="logout-info">
				<h2 class="">Checked in</h2>
				<p>{$reservation->room->name|escape}<br>
				<strong>On {$reservation->observation->startTime|date_format:"%b %e, %Y at %I:%M %p"}</strong>
				</p>
				<div class="kiosk-link">
					<a href="kiosk/logout" class="btn btn-default btn-lg">Logout</a>
				</div>
			</div>
		</div>

	{elseif $checkedOut}
		<div class="check-in-content">
			<div class="logout-img">
				<img src="assets/images/ccheckin-observation2.jpg" class="img-responsive" alt="Children's playroom">
			</div>
			<div class="logout-info">
				<h2 class="">Checked out</h2>
				<p>You have successfully checked out.</p>
				<div class="kiosk-link">
					<a href="kiosk/logout" class="btn btn-default btn-lg">Logout</a>
				</div>
			</div>
		</div>

	{elseif $late || $early}
		<div class="check-in-content">
			<div class="logout-img">
				<img src="assets/images/ccheckin-observation2.jpg" class="img-responsive" alt="Children's playroom">
			</div>
			<div class="logout-info">
			{if $late}
				<h2>Late for appointment</h2>
				<p>You are late for your appointment at <strong>{$late.time|date_format:"%b %e, %Y at %I:%M %p"}</strong>.  You must sign up again to reserve time for observations.</p>
			{/if}
			{if $early}
				<h2>Early for appointment</h2>
				<p class="">You are early for your appointment at {$early.time|date_format:"%b %e, %Y at %I:%M %p"}. Please come back at time closer to your appointment.</p>
			{/if}
				<div class="kiosk-link">
					<a href="kiosk/logout" class="btn btn-default btn-lg">Logout</a>
				</div>
			</div>
		</div>

	{elseif $empty}
		<div class="check-in-content">
			<div class="logout-img">
				<img src="assets/images/ccheckin-observation2.jpg" class="img-responsive" alt="Children's playroom">
			</div>
			<div class="logout-info">
				<h2>No reservations found</h2>
				<p>We have not found any reservations for you.</p>
				<div class="kiosk-link">
					<a href="kiosk/logout" class="btn btn-default btn-lg">Logout</a>
				</div>
			</div>
		</div>

	{elseif $earlycheckout}
		<div class="check-in-content">
			<div class="logout-img">
				<img src="assets/images/ccheckin-observation2.jpg" class="img-responsive" alt="Children's playroom">
			</div>
			<div class="logout-info">
				<h2>Cannot checkout too early</h2>
				<p>You must wait at least five minutes before you can checkout.</p>
				<div class="kiosk-link">
					<a href="kiosk/logout" class="btn btn-default btn-lg">Logout</a>
				</div>
			</div>
		</div>
	
	{else} <!-- Login -->
		<form method="post" action="login/complete/sfsu-shib">
			<!-- <form method="post" action="login/complete/sfsu-pw" autocomplete="false"> -->
			<!-- <form method="post" action="login/complete/ad" autocomplete="false"> -->
			<input type="hidden" name="returnTo" value="{$smarty.server.REQUEST_URI|escape}" />
			<div class="login-img">
				<img src="assets/images/ccheckin-playroom-cropped.png" class="img-responsive img-1" alt="Children's playroom">
				<img src="assets/images/ccheckin-outside.jpg" class="img-responsive img-2" alt="Backyard playground">
			</div>
			<div class="login-info">
				<div class="login-text">
					<h1>Children's Campus</h1>
					<h2>at SF State</h2>
					<p>A Center for Early Care and Educational, Professional Development and Research</p>
				</div>
				<div class="login-btn">
					<button class="command-button btn btn-primary" type="submit" name="command[login]">Login</button>
				</div>
			</div>
		{generate_form_post_key}
		</form>


	{/if} <!-- end kiosk options -->

{/if} <!-- end Show Kiosk Logged in/out -->

	</div> <!-- end wrapper div -->
</div> <!-- end loginForm div -->
<br style="clear: both;" />
</div> <!-- end row div -->
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
