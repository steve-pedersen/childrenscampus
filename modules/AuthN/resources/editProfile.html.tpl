<h1>Edit account: {$account->username|escape}</h1>

<form method="post" action="{$smarty.server.REQUEST_URI|escape}" class="data-entry">

	<div class="form-group">
		<label for="username">Username</label>
		<p>{$account->username}</p>
	</div>
	<div class="form-group">
		<label for="emailaddress">Email Address</label>
		<p>{$account->emailAddress}</p>
	</div>
	<div class="form-group">
		<label for="firstname">First name</label>
		<input type="text" class="form-control" name="firstname" value="{$account->firstName}" placeholder="{$account->firstName}">
	</div>
	<div class="form-group">
		<label for="middlename">Middle name or initial</label>
		<input type="text" class="form-control" name="middlename" value="{$account->middleName}" placeholder="{$account->middleName}">
	</div>
	<div class="form-group">
		<label for="lastname">Last name</label>
		<input type="text" class="form-control" name="lastname" value="{$account->lastName}" placeholder="{$account->lastName}">
	</div>

	{if $canEditNotifications}
	<div class="form-group">
	<fieldset class="field">
		<legend>Admin Email Notifications</legend>
		<ul class="list-group">
			<li>
				<label for="receiveAdminNotifications">		 
				{if !$canReceiveNotifications}
					Unable to 
				{else}
					<input type="checkbox" name="receiveAdminNotifications" id="receiveAdminNotifications"
					{if ($account->receiveAdminNotifications) && !$newAccount}
						checked aria-checked="true" value=true
					{else}
						aria-checked="false" value=false
					{/if}
					/>
				{/if} 	
				
				Receive Admin Notifications</label>
				{if !$canReceiveNotifications}<p class="alert alert-info"> -- Note: you are unable to receive system notifications. Contact an admin if this is incorrect or request an upgrade to your role to one that can receive system notifications.</p>{/if}
			</li>
			{if $canReceiveNotifications}
			<li><p><em> E.g. "course requested" emails.</em></p></li>
			{/if}
		</ul>
	</fieldset>
	</div>
	{/if}

	<hr>
	<div>
		<input type="submit" class="btn btn-primary btn-sm" value="Save Settings" name="command[save]"></input>
		<a class="btn btn-link" href="home">Cancel</a>
	</div>

{generate_form_post_key}
</form>
