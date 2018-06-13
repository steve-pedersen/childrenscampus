
{if $welcomeTitle}{$welcomeTitle}{else}<h1>Welcome to Children's Campus</h1>{/if}

<!-- NOTE: Place divs inside of conditionals, or else within isLoggedIn check -->

{if $userContext->account && $announcements}
<div class="announcements">
<h3 class="announcements-header">Announcements</h3>
	<ul class="">
	{foreach from=$announcements item=announcement}
		<li>{$announcement}</li>
	{/foreach}
	</ul>
</div>
{/if}

{if $pendingRequests}
<div class="alert alert-info">
    <p class="lead">You have {$pendingRequests|@count} pending course request{if $pendingRequests|@count > 1}s{/if}:</p>
    <ul class="">
        {foreach from=$pendingRequests item=request}
            <li>{$request->course->facets->index(0)->shortDescription}</li>
        {/foreach}
    </ul>
    <br>
    <p class=""><small><em>You will be notified when an admin approves or denies your request.</em></small></p>
</div>
{/if}

<div class="row">
{if $userContext->account}
<div id="welcome-text" class="col-xs-6">
	<!-- <h2 class="about">About us</h2> -->
{else}
<div id="welcome-text" class="col-xs-12">

{/if}
{if $welcomeText}
{$welcomeText}
{else}
    <p>Children's Campus supports positive child development through quality care and education for approximately 85 infants, toddlers and preschool children. The Children's Campus also provides opportunities for student internships in a variety of disciplines such as teaching, nursing, child development, psychology, and social work. Faculty and student research is encouraged to improve best practices in early care and education, and the facility will serve as a site for observation to augment classroom instruction. The Children's Campus has been designed and staffed with highly qualified professionals in order to meet state and federal licensing and accreditation requirements and is supported by an advisory board of participating parents, faculty, and staff.</p>
{/if}
</div>

{if $userContext->account}
	<div id="location-message" class="col-xs-6">
		<div class="card" >
		  <img class="card-img-top img-responsive" src="assets/images/ccheckin-entrance.jpg" alt="Front door to the Children's Campus">
		  <div class="card-body">
		    <p class="card-text">
			{if $locationMessage}
			{$locationMessage}
			{else}
			    <p><strong>Students: Please take note of our location!</strong></p>
			    <p>
		The Children’s Campus is located on the campus of San Francisco State University at 
		the corner of North State Drive and Lake Merced Blvd.  We are beside the Library Annex.  
		If you need help locating our center, please check the <u><a href="http://www.sfsu.edu/~sfsumap/" title="Opens in a new window." target="_blank" class="popup">campus map</a></u>
		</p>
			{/if}    	
		    </p>
		  </div>
		</div>
	</div>
	<div id="welcome-text-extended" class="col-xs-12">
	{if $welcomeTextExtended}
	{$welcomeTextExtended}
	{else}
		<h2>Children's Campus At-A-Glance:</h2>
		<ul>
			<li>Priority enrollment for children of SF State faculty and staff.  Community families are welcome, space permitting.</li>
			<li>Serving children from 6 months to 5 years of age.</li>

			<li>Full-day, year-round program operating on the SFSU academic calendar:
				<div style="font-style: italic;">
				Closed New Year's Day, Martin Luther King Day, Lincoln's Birthday (as observed), Washington's Birthday (as observed), 
				Cesar Chavez Day, Memorial Day, Independence Day, Labor Day, Veterans Day, Thanksgiving (Thursday/Friday only), December 
				campus closure (Christmas Day, observed Admissions Day, and delayed observed holidays).  See Operational Calendar for exact dates. 
				</div>
				</li>
			<li>Located on the SF State campus at the corner of North State Drive and Lake Merced Blvd.</li>
			<li>Hours of operation:  7:30 am - 5:30 pm, Monday through Friday.</li>
			<li>Eligible for the University's Dependent Care Reimbursement Program through which employees may allocate up to $5,000 per year pre-tax for child care. 
				(<a href="http://www.sfsu.edu/~hrwww/benefits/flexacct.html">Details available here</a>).</li>

		</ul>
	{/if}
	</div>
{/if}
</div> <!-- end row -->

{if !$userContext->account}
<div class="welcome-module">
    <a href="{$app->baseUrl('login')}" class="btn btn-primary">Click to Log In</a>
</div>
{/if}
