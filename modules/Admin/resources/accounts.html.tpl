<a href="admin">Administrate</a>
<h1>Accounts</h1>

<form method="get" action="{$smarty.server.REQUEST_URI|escape}">
    <p class="field">
        <label for="account-search" class="field-label field-linked">Search</label>
        <input type="text" id="account-search" name="sq" value="{$searchQuery|escape}">
        <input type="submit" name="btn" value="Search">
        {if $searchQuery}<a href="admin/accounts?sort={$sortBy}&amp;dir={$dir}">Remove search</a>{/if}
    </p>
</form>

<table class="data">
    <thead>
        <tr>
            <td><a href="admin/accounts?sort=name&dir={if $sortBy=="name"}{$oppositeDir}{else}asc{/if}">Name</a></td>
            <td><a href="admin/accounts?sort=email&dir={if $sortBy=="email"}{$oppositeDir}{else}asc{/if}">E-mail address</td>
            <td><a href="admin/accounts?sort=uni&dir={if $sortBy=="uni"}{$oppositeDir}{else}asc{/if}">University</td>
            <td><a href="admin/accounts?sort=login&dir={if $sortBy=="login"}{$oppositeDir}{else}asc{/if}">Last login</td>
            <td>Options</td>
        </tr>
    </thead>
    
    <tbody>
{foreach item="account" from=$accountList}
        <tr class="{cycle values=even,odd}">
            <td><a href="{$account->friendlyUrl}">{$account->lastName|escape}, {$account->firstName|escape} {$account->middleName|escape}</a></td>
            <td>{$account->email|escape}</td>
            <td>{$account->university->name|escape|default:'<span class="detail">n/a</a>'}</td>
            <td>{if $account->lastLoginDate}{$account->lastLoginDate->format('M j, Y h:ia')}{else}<span class="detail">never</span>{/if}</td>
            <td><input type="submit" name="command[become][{$account->id}]" value="Become" title="Switch to account {$account->displayName}"></td>
        </tr>
{foreachelse}
        <tr>
            <td colspan="5" class="notice">
                No accounts match your search criteria.
            </td>
        </tr>
{/foreach}
    </tbody>
</table>
