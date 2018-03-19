<!DOCTYPE html>
{assign var="appName" value=$app->configuration->appName}
<html lang="en">
	<head>
{*may not be needed*}		<meta charset="utf-8">
{*may not be needed*}    	<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<title>{if $pageTitle}{$pageTitle|escape} &mdash; {/if}{$appName|escape}</title>
		<base href="{$baseUrl|escape}/">
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
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

	<body>
		<a href="{$smarty.server.REQUEST_URI}#content" class="sr-only sr-only-focusable">Skip Navigation</a>
		<header class="at">
	        <nav class="navbar navbar-default navbar-static-top" role="navigation">
	          <div class="container-fluid">

	            <!-- Brand and toggle get grouped for better mobile display -->
	            <div class="navbar-header">
	              <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
	                <span class="sr-only">Toggle navigation</span>
	                <span class="icon-bar"></span>
	                <span class="icon-bar"></span>
	                <span class="icon-bar"></span>
	              </button>
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


<!-- MAIN CONTENT -->
        
<div class="container" id="content" tabindex="-1">
	<div class="row">

		{if $viewer}
		<!-- LEFT NAVIGATION -->		
        <div class="col-md-3">
            <div class="sidebar-nav">
                <div class="well">
                	<h3 class="menu-header">Menu</h3>
                    <ul class="nav left-nav">
                        <li><a href="reservations">Sign Up For a Visit</a></li>
                        <li><a href="reservations/upcoming">My Reservations</a></li>
                        {if $pAdmin}
                        <li><a href="reports/generator">Create Reports</a></li>
                        {/if}
                        {if $isCCTeacher || $pAdmin}
                        <li><a href="reservations/schedule">Room Schedules</a></li>
                        {/if}
                        {if $isTeacher || $pAdmin}            
                        <li><a href="courses">View Courses</a></li>
                        <li><a href="courses/request">Request a Course</a></li>
                        {/if}
                        <li><a href="reservations/missed">Missed Reservations</a></li>
                        <li><a href="reservations/observations">Past Observations</a></li>
						{if !$pAdmin}
                        <li><a href="profile">Edit Profile</a></li>
                        {/if}
                    </ul>
                </div>
                <!--/.well -->
            </div>
            <!--/sidebar-nav-fixed -->
        </div>
		{/if}

		<!-- MIDDLE - MAIN CONTENT -->
		{if $viewer}
			{if $adminPage}
			<div class="col-md-9 main-content">
			{else}
			<div class="col-md-7 main-content">
			{/if}
		{elseif !$viewer && $homePage}
			<div class="col-md-10 main-content">
		{else}
			<div class="col-md-12 main-content">
		{/if}
		{include file=$contentTemplate}
		</div>
		
		{if ($viewer && !$adminPage) || (!$viewer && $homePage)}
		<!-- RIGHT SIDEBAR - Contact -->
        <div class="col-md-2 col-sm-4 col-xs-12">
            <div class="right-sidebar pull-right">
                <div class="well">
					<div id="resources">	
						<div class="res_box">
							<h3>Contact</h3>
							<strong>Children's Campus</strong><br>

							San Francisco State University<br><br>
							<strong>Physical Address:</strong><br>
							North State Drive @ Lake Merced Boulevard<br><br>
							<strong>Mailing Address:</strong><br>
							1600 Holloway Ave.<br>
							San Francisco, CA 94132<br><br>
							Email: <a href="mailto:children@sfsu.edu">children@sfsu.edu</a><br>
							Phone: 415-405-4011<br>
							Fax: 415-405-3832<br>
						</div>
					</div>
                </div>
                <!--/.well -->
            </div>
            <!--/sidebar-nav-fixed -->
        </div>
        {/if}

	</div>
</div>

		{if !$viewer}
		<div id="login-box" class="modal fade">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button class="btn btn-primary btn-sm" type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
						<h3>Choose Login Method</h3>
					</div>
					<div class="modal-body">
						<p>Loading login options&hellip;</p>
					</div>
				</div>
			</div>
		</div>
		{/if}
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
