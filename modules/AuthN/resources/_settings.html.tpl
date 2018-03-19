
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
<fieldset class="field">
	<legend>Admin Email Notifications</legend>
	
	<ul class="list-group">
		<li>
			<label for="receiveAdminNotifications">
			<input type="checkbox" name="receiveAdminNotifications" id="receiveAdminNotifications" 
			{if ($account->receiveAdminNotifications) && !$newAccount}checked aria-checked="true" value=true{else}aria-checked="false" value=false{/if} />
			Receive Admin Notifications</label>
		</li>
		<li><p><em> E.g. "course requested" emails.</em></p></li>
	</ul>

</fieldset>
{/if}