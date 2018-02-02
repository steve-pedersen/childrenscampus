<a href="admin">Administrate</a>
<h1>What is installed?</h1>

<div class="row">
    <div class="col-xs-12">
        <h2>Table of contents</h2>
        <ul class="bullet columnar">
        {foreach item="module" from=$moduleList}
            <li><a href="admin/colophon#module-{$module->id}">{$module->name|escape}</a></li>
        {/foreach}
        </ul>
    </div>
</div>

{foreach item="module" from=$moduleList}
<div style="border-bottom: 1px solid #ccc; margin-bottom: 1.25em;">
<h2 id="module-{$module->id}">{$module->name} v.{$module->version}</h3>
<p class="detail">{$module->id} created by {foreach name="authors" item="author" from=$module->authors}{$author|escape}{if !$smarty.foreach.authors.last}, {/if}{/foreach}. {$module->copyright}.</p>
<p>{$module->description}</p>
{if !empty($module->classes)}
<h3>Classes</h3>
<table class="data">
    <thead>
        <tr>
            <th>Class name</th>
            <th>Path</th>
        </tr>
    </thead>
    
    <tbody>
{foreach key="className" item="classPath" from=$module->getClasses()}
        <tr class="{cycle values='even,odd'}">
            <td>{$className|escape}</td>
            <td>{$classPath|escape}</td>
        </tr>
{/foreach}
    </tbody>
</table>
{/if}
{if !empty($module->extensionPoints)}
<h3>Extension points</h3>
<table class="data">
    <thead>
        <tr>
            <th>Name</th>
            <th>Description</th>
            <th>Registered extensions</th>
        </tr>
    </thead>
    
    <tbody>
{foreach item="point" from=$module->extensionPoints}
        <tr>
            <td>{$point->getUnqualifiedName()}</td>
            <td>{$point->getDescription()}</td>
            <td>
<ul>
{foreach key="extName" item="extInfo" from=$point->getExtensionDefinitions()}
                <li>{$extName|escape} <span class="de-emphasized detail">from <a href="admin/colophon#module-{$extInfo[1]->id}">{$extInfo[1]->name}</a></span></li>
{foreachelse}
                <li>None</li>
{/foreach}
</ul>
            </td>
        </tr>
{/foreach}
    </tbody>
</table>
{/if}
</div>
{/foreach}
