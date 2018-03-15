<h1>Manage Email Settings & Content</h1>

{if $sendSuccess}
<div class="alert alert-info">
	<p>{$sendSuccess}</p>
	<p><strong>If you have made changes to the templates please make sure to save the changes below.</strong></p>
</div>
{/if}
<br>

<form id="fileAttachment" method="post" action="{$smarty.server.REQUEST_URI}" enctype="multipart/form-data">
	{generate_form_post_key}

    <h2>Attachment Files</h2>
	<p>Upload new files to the server, which can then be selected to be sent as attachments for each email below.</p>

    <div class="form-group row">
        <div class="col-xs-12">
			{foreach item='att' from=$removedFiles}
			<input type="hidden" name="removed-files[{$att->id}]" value="{$att->id}" />
			{/foreach}
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Attachment</th>
                        <th>Size</th>
                        <th>Actions</th>
                    </tr>
                </thead>
            {foreach item='attachment' from=$attachments}
                <tr>
                    <td>{$attachment->getDownloadLink('admin')}</td>
                    <td>{$attachment->contentLength|bytes}</td>
                    <td>
                        <input type="submit" name="command[remove-attachment][{$attachment->id}]" value="Remove From Server" class="btn btn-xs btn-danger" />
                        <input type="hidden" name="attachments[{$attachment->id}]" value="{$attachment->id}" />
                    </td>
                </tr>
            {foreachelse}
                <tr><td colspan="3">There are no attachments on the server.</td></tr>
            {/foreach}
            </table>
        </div>
    </div>

    <div class="form-group upload-form row">
        <div class="col-xs-8">
            <label for="attachment" class="field-label field-linked">Upload file attachment</label>       
            <input class="form-control" type="file" name="attachment" id="attachment" />
        {foreach item='error' from=$errors.attachment}<div class="error">{$error}</div>{/foreach}
        </div>
        <div class="col-xs-8 help-block text-center">
            <p id="type-error" class="bg-danger" style="display:none"><strong>File format should be doc, docx, or pdf.</strong></p>
        </div>          
    </div>

    <div class="form-group row">  
        <div class="col-xs-8">
            <label for="files-title" class="field-linked inline">Optional title</label>
            <input class="form-control" type="text" name="file[title]" id="files-title" class="inline" />
        </div>
    </div>

    <div class="form-group commands file-submit row">
        <div class="col-xs-8">
            <input type="submit" name="command[upload]" id="fileSubmit" value="Upload File" class="btn btn-info hide" onclick="this.form.submit();" />
        </div>
    </div>    
</form>

<hr>

<h2>Emails</h2>

<form action="{$smarty.server.REQUEST_URI}" method="post">
	{generate_form_post_key}

	<div class="row">
		<div class="col-xs-8">
			<div class="form-group">
				<label for="defaultAddress">Default email address</label>
				<input type="email" class="form-control" name="defaultAddress" id="defaultAddress" value="{$defaultAddress}" placeholder="e.g. children@sfsu.edu" />				
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-xs-8">
			<div class="form-group">
				<label for="signature">Email Signature</label>
				<textarea name="signature" id="signature" class="wysiwyg form-control" rows="5" placeholder="  --The Children's Campus">{$signature}</textarea>		
			</div>
		</div>
	</div>
	<hr>

	<div class="row">
		<div class="col-xs-8">
			<div class="form-group choice-widget">
				<label for="courseRequestedAdmin">Course Requested Admin: <span class="email-type-description">sent to Administrator as a notification of a course request.</span></label>
				<textarea name="courseRequestedAdmin" id="courseRequestedAdmin" class="wysiwyg form-control" rows="{if $courseRequestedAdmin}{$courseRequestedAdmin|count_paragraphs*2}{else}8{/if}">{$courseRequestedAdmin}</textarea>
				<span class="help-block">
					You can use the following tokens for context replacements to fill out the template: 
					<code>|%FIRST_NAME%|</code>, <code>|%LAST_NAME%|</code>, <code>|%COURSE_FULL_NAME%|</code>, <code>|%COURSE_SHORT_NAME%|</code>, <code>|%REQUEST_LINK%|</code>, <code>|%SEMESTER%|</code>
				</span>
			</div>
		</div>

		<div class="col-xs-4">
			<label id="testcourserequestedadmin">Test Course-Requested-Admin Template</label>
			<p class="lead">This will send an email to your account showing how the email will look to you.</p>
			<button type="submit" name="command[send][courseRequestedAdmin]" aria-describedby="testcourserequestedadmin" class="btn btn-default">Send Test</button>
		</div>
	</div>
	<hr>

	<div class="row">
		<div class="col-xs-8">
			<div class="form-group choice-widget">
				<label for="courseRequestedTeacher">Course Requested Teacher: <span class="email-type-description">sent as a receipt to Teacher who requested the course, once request is submitted.</span></label>
				<textarea name="courseRequestedTeacher" id="courseRequestedTeacher" class="wysiwyg form-control" rows="{if $courseRequestedTeacher}{$courseRequestedTeacher|count_paragraphs*2}{else}8{/if}">{$courseRequestedTeacher}</textarea>
				<span class="help-block">
					You can use the following tokens for context replacements to fill out the template: 
					<code>|%FIRST_NAME%|</code>, <code>|%LAST_NAME%|</code>, <code>|%COURSE_FULL_NAME%|</code>, <code>|%COURSE_SHORT_NAME%|</code>, <code>|%SEMESTER%|</code>
				</span>
			</div>
		</div>

		<div class="col-xs-4">
			<label id="testcourserequestedteacher">Test Course-Requested-Teacher Template</label>
			<p class="lead">This will send an email to your account showing how the email will look to you.</p>
			<button type="submit" name="command[send][courseRequestedTeacher]" aria-describedby="testcourserequestedteacher" class="btn btn-default">Send Test</button>
		</div>
	</div>
	<hr>

	<div class="row">
		<div class="col-xs-8">
			<div class="form-group choice-widget">
				<label for="courseAllowedTeacher">Course Allowed Teacher: <span class="email-type-description">sent to Teacher who requested the course, once approved.</span></label>
				<textarea name="courseAllowedTeacher" id="courseAllowedTeacher" class="wysiwyg form-control" rows="{if $courseAllowedTeacher}{$courseAllowedTeacher|count_paragraphs*2}{else}8{/if}">{$courseAllowedTeacher}</textarea>
				<span class="help-block">
					You can use the following tokens for context replacements to fill out the template: 
					<code>|%FIRST_NAME%|</code>, <code>|%LAST_NAME%|</code>, <code>|%COURSE_FULL_NAME%|</code>, <code>|%COURSE_SHORT_NAME%|</code>, <code>|%OPEN_DATE%|</code>, <code>|%CLOSE_DATE%|</code>, <code>|%COURSE_VIEW_LINK%|</code>
				</span>
			</div>
		</div>

		<div class="col-xs-4">
			<label id="testcourseallowedteacher">Test Course-Allowed-Teacher Template</label>
			<p class="lead">This will send an email to your account showing how the email will look to you.</p>
			<button type="submit" name="command[send][courseAllowedTeacher]" aria-describedby="testcourseallowedteacher" class="btn btn-default">Send Test</button>
		</div>
	</div>
	<hr>

	<div class="row">
		<div class="col-xs-8">
			<div class="form-group choice-widget">
				<label for="courseAllowedStudents">Course Allowed Students: <span class="email-type-description">sent to all enrolled Students in a course, once approved.</span></label>
				<textarea name="courseAllowedStudents" id="courseAllowedStudents" class="wysiwyg form-control" rows="{if $courseAllowedStudents}{$courseAllowedStudents|count_paragraphs*2}{else}8{/if}">{$courseAllowedStudents}</textarea>
				<span class="help-block">
					You can use the following tokens for context replacements to fill out the template: 
					<code>|%FIRST_NAME%|</code>, <code>|%LAST_NAME%|</code>, <code>|%COURSE_FULL_NAME%|</code>, <code>|%COURSE_SHORT_NAME%|</code>, <code>|%OPEN_DATE%|</code>, <code>|%CLOSE_DATE%|</code>, <code>|%SITE_LINK%|</code>
				</span>
			</div>
		</div>

		<div class="col-xs-4">
			<label id="testcourseallowedstudents">Test Course-Allowed-Students Template</label>
			<p class="lead">This will send an email to your account showing how the email will look to you.</p>
			<button type="submit" name="command[send][courseAllowedStudents]" aria-describedby="testcourseallowedstudents" class="btn btn-default">Send Test</button>
		</div>
	</div>
	<hr>

	<div class="row">
		<div class="col-xs-8">
			<div class="form-group choice-widget">
				<label for="courseDenied">Course Denied: <span class="email-type-description">sent to Teacher who requested the course, once denied.</span></label>
				<textarea name="courseDenied" id="courseDenied" class="wysiwyg form-control" rows="{if $courseDenied}{$courseDenied|count_paragraphs*2}{else}8{/if}">{$courseDenied}</textarea>
				<span class="help-block">
					You can use the following tokens for context replacements to fill out the template: 
					<code>|%FIRST_NAME%|</code>, <code>|%LAST_NAME%|</code>, <code>|%COURSE_FULL_NAME%|</code>, <code>|%COURSE_SHORT_NAME%|</code>, <code>|%SEMESTER%|</code>
				</span>
			</div>
		</div>

		<div class="col-xs-4">
			<label id="testcoursedenied">Test Course-Denied Template</label>
			<p class="lead">This will send an email to your account showing how the email will look to you.</p>
			<button type="submit" name="command[send][courseDenied]" aria-describedby="testcoursedenied" class="btn btn-default">Send Test</button>
		</div>
	</div>
	<hr>
	
	<div class="row">
		<div class="col-xs-8">
			<div class="form-group choice-widget">
				<label for="reservationDetails">Reservation Details: <span class="email-type-description">sent as a receipt with pertinent info to Student who made a reservation, once one is made.</span></label>
				<textarea name="reservationDetails" id="reservationDetails" class="wysiwyg form-control" rows="{if $reservationDetails}{$reservationDetails|count_paragraphs*2}{else}8{/if}">{$reservationDetails}</textarea>
				<span class="help-block">
					You can use the following tokens for context replacements to fill out the template: 
					<code>|%FIRST_NAME%|</code>, <code>|%LAST_NAME%|</code>, <code>|%RESERVE_DATE%|</code>, <code>|%RESERVE_VIEW_LINK%|</code>, <code>|%RESERVE_CANCEL_LINK%|</code>, <code>|%PURPOSE_INFO%|</code>, <code>|%ROOM_NAME%|</code>
				</span>
			</div>
		</div>

		<div class="col-xs-4">
			<label id="testreservationdetails">Test Reservation-Details Template</label>
			<p class="lead">This will send an email to your account showing how the email will look to you.</p>
			<button type="submit" name="command[send][reservationDetails]" aria-describedby="testreservationdetails" class="btn btn-default">Send Test</button>
		</div>
	</div>
	<hr>

	<div class="row">
		<div class="col-xs-8">
			<div class="form-group choice-widget">
				<label for="reservationReminderTime">Reservation Reminder Time: <span class="email-type-description">specify an amount of time to prior to a reservation to send a reminder email.</span></label>
				<input type="text" class="form-control" name="reservationReminderTime" id="reservationReminderTime" value="{$reservationReminderTime}" placeholder="e.g. 1 day, 4 hours, or 8 hours" />
			</div>
		</div>

		<div class="col-xs-8">
			<div class="form-group choice-widget">
				<label for="reservationReminder">Reservation Reminder: <span class="email-type-description">send reservation details to Student prior to start of reservation.</span></label>
				<textarea name="reservationReminder" id="reservationReminder" class="wysiwyg form-control" rows="{if $reservationReminder}{$reservationReminder|count_paragraphs*2}{else}8{/if}">{$reservationReminder}</textarea>
				<span class="help-block">
					You can use the following tokens for context replacements to fill out the template: 
					<code>|%FIRST_NAME%|</code>, <code>|%LAST_NAME%|</code>, <code>|%RESERVE_DATE%|</code>, <code>|%RESERVE_VIEW_LINK%|</code>, <code>|%RESERVE_CANCEL_LINK%|</code>, <code>|%PURPOSE_INFO%|</code>, <code>|%ROOM_NAME%|</code>
				</span>
			</div>
		</div>

		<div class="col-xs-4">
			<label id="testreservationdetails">Test Reservation-Reminder Template</label>
			<p class="lead">This will send an email to your account showing how the email will look to you.</p>
			<button type="submit" name="command[send][reservationReminder]" aria-describedby="testreservationdetails" class="btn btn-default">Send Test</button>
		</div>
	</div>
	<hr>

	<div class="row">
		<div class="col-xs-8">
			<div class="form-group choice-widget">
				<label for="reservationMissed">Reservation Missed: <span class="email-type-description">sent to Student when they miss a reservation.</span></label>
				<textarea name="reservationMissed" id="reservationMissed" class="wysiwyg form-control" rows="{if $reservationMissed}{$reservationMissed|count_paragraphs*2}{else}8{/if}">{$reservationMissed}</textarea>
				<span class="help-block">
					You can use the following tokens for context replacements to fill out the template: 
					<code>|%FIRST_NAME%|</code>, <code>|%LAST_NAME%|</code>, <code>|%RESERVE_DATE%|</code>, <code>|%PURPOSE_INFO%|</code>, <code>|%RESERVATION_MISSED_LINK%|</code>
				</span>
			</div>
		</div>

		<div class="col-xs-4">
			<label id="testreservationmissed">Test Reservation-Missed Template</label>
			<p class="lead">This will send an email to your account showing how the email will look to you.</p>
			<button type="submit" name="command[send][reservationMissed]" aria-describedby="testreservationmissed" class="btn btn-default">Send Test</button>
		</div>
	</div>
	<hr>

	<div class="controls">
		<button type="submit" name="command[save]" class="btn btn-primary">Save</button>
		<a href="admin" class="btn btn-default pull-right">Cancel</a>
	</div>
</form>