<h1>Children's Campus Report Generator</h1>
<p class="alert alert-info">Select the date range for which to generate the observation data.</p>
<p>For large date ranges, it may take a few minutes to generate the report. Please do not refresh your browser or hit back on the page during this process.</p>
<form class="form-horizontal form-inline" method="post" action="{$smarty.server.REQUSET_URI}">
    <div class="data-form">
        <div class="form-group">
            <div class="col-xs-6">
                <label class="field-label field-linked" for="from">From</label>
           
                <input class="form-control datepicker" type="text" name="from" id="datepickerFrom" />
            </div>
        </div>
        <div class="form-group">
            <div class="col-xs-6">
                <label class="field-label field-linked" for="until">Until</label>
           
                <input class="form-control datepicker" type="text" name="until" id="datepickerUntil" value="{$tomorrow->format('m/d/Y')}" />
            </div>
        </div>
    </div>
    <br><hr>
    <div class="form-group commands row">
        <div class="col-xs-12">
        {generate_form_post_key}
        <input class="btn btn-primary" type="submit" name="command[download]" value="Download" />
        <a class="btn btn-link" href="{$app->baseUrl('')}">Cancel</a>
        </div>
    </div>
</form>