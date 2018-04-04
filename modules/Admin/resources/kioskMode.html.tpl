<h1>Manage Kiosk Mode</h1>
    <div class="flash">
        <div class="message {if $isKiosk}warning{else}notice{/if}">
    {if $isKiosk}
        <p>This machine is set for kiosk mode</p>
    {else}<p>This machine is not in kiosk mode</p>{/if}
        </div>
    </div>
<form method="post" action="{$smarty.server.REQUEST_URI}">

    <div class="commands">
        <p><input class="btn btn-{if $isKiosk}primary{else}success{/if}" name="command[{if $isKiosk}un{/if}set]" id="command-{if $isKiosk}un{/if}set" type="submit" value="{if $isKiosk}Unset{else}Set{/if} Kiosk Mode" /></p>
    </div>
</form>