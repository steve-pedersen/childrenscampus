
{if $pAdmin}
	<fieldset class="field">
		<legend>Roles</legend>
		<ul class="list-group">
{foreach item="role" from=$roleList}
			<li>
				<label for="account-role-{$role->id}">
				<input type="checkbox" name="role[{$role->id}]" id="account-role-{$role->id}" class="account-role-{$role->name}" 
				{if $account->roles->has($role)}checked aria-checked="true"{else}aria-checked="false"{/if} />
				{$role->name|escape}</label>
			</li>
{/foreach}
		</ul>
	</fieldset>
{/if}
<fieldset class="field">
	<legend>Activate account</legend>
	<ul class="list-group">
		<li>
			<label for="account-status">
			<input type="checkbox" name="status" id="account-status" 
			{if $account->isActive}checked aria-checked="true"{else}aria-checked="false"{/if} />
			Active</label>
		</li>
	</ul>
</fieldset>
{if $canEditNotifications}
	{if ($authZ->hasPermission($account, 'admin') || $authZ->hasPermission($account, 'receive system notifications'))}
		{assign var=canReceiveNotifications value=true}
	{else}
		{assign var=canReceiveNotifications value=false}
	{/if}
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
			{if !$canReceiveNotifications}<p class="alert alert-info"> -- Note: This user is unable to receive system notifications. Contact an admin if this is incorrect or upgrade this user's role to one that can receive system notifications.</p>{/if}
		</li>
		{if $canReceiveNotifications}
		<li><p><em> E.g. "course requested" emails.</em></p></li>
		{/if}
	</ul>

</fieldset>
{/if}