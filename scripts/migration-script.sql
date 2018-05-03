-- ************************** READ BEFORE RUNNING **************************
--
-- 	PRE-MIGRATION:
-- 		- Screenshot room schedules of Old Ccheckin prior to migration

-- 	POST-MIGRATION:
-- 		- Go to /admin/migrate endpoint to finish migration

--	MIGRATION PREPARATION:
-- 		- backup new db, if needed:
pg_dump ccheckin > ~/dbdumps/2018-04-12-ccheckin.sql
-- create old ccheckin db
createdb --encoding=UNICODE --owner=apps oldccheckin
psql oldccheckin < /home/administrator/2017-12-12-childrenscampus.sql
-- 		- dump old tables into new db, there shouldn't be any overwrites
pg_dump oldccheckin | psql ccheckin



--	NOTES:
-- 		- table alias "o" is for "old"
--		- debug statements at bottom... don't run these unless needed
--
-- *************************************************************************


-- TURN EMAIL TESTING ON INITIALLY
BEGIN;
UPDATE at_config_settings
SET VALUE = 1 WHERE key = 'email-testing-only';
UPDATE at_config_settings
SET VALUE = 'pedersen@sfsu.edu' WHERE key = 'email-test-address';
COMMIT;


-- set application admin
BEGIN;
UPDATE bss_authn_accounts 
SET username = 'admin', email_address = 'pedersen@sfsu.edu', first_name = 'Steve', last_name = 'Pedersen', receive_admin_notifications = false
WHERE id = 1;
COMMIT;


-- academia_universities => bss_academia_universities
BEGIN;
INSERT INTO bss_academia_universities (id, name, abbreviation, href)
SELECT o.id, o.title, o.abbrev, o.website
FROM academia_universities o;
-- id_seq
SELECT setval('bss_academia_universities_id_seq', COALESCE((SELECT MAX(id) FROM bss_academia_universities), 1), true);
COMMIT;


-- academia_disciplines => bss_academia_disciplines
BEGIN;
INSERT INTO bss_academia_disciplines (id, name, abbreviation)
SELECT o.id, o.title, o.abbrev
FROM academia_disciplines o;
-- id_seq
SELECT setval('bss_academia_disciplines_id_seq', COALESCE((SELECT MAX(id) FROM bss_academia_disciplines), 1), true);
COMMIT;


-- ClassData API
BEGIN;
UPDATE at_config_settings SET value = 'https://classdata.test.at.sfsu.edu/' WHERE key = 'classdata-api-url';
UPDATE at_config_settings SET value = 'ca1a3f6f-7cac-4e52-9a0a-5cbf82b16bc9' WHERE key = 'classdata-api-key';
UPDATE at_config_settings SET value = '4af2614e-142d-4db8-8512-b3ba13dd0143' WHERE key = 'classdata-api-secret';
COMMIT;


-- at_config_settings
BEGIN;
UPDATE at_config_settings 
SET value = '["Create and lead an activity with children","Pick one child to focus on for observations","Interview a child","Interview a parent","Interview the Head Teacher","Take photos of a child\/children","Take video of a child\/children","Complete a DRDP-r of a child","Complete a portfolio of a child","Create a documentation board of an activity","Complete the ECERS or ITERS on the classroom"]'
WHERE key = 'course-tasks';
COMMIT;


-- auth_user_accounts => bss_authn_accounts
-- don't include ghost accounts or the application admin
BEGIN;
INSERT INTO bss_authn_accounts (id, username, email_address, first_name, middle_name, last_name, last_login_date, created_date, created_by_id, missed_reservation, user_alias, is_active)
SELECT o.id, o.ldap_user::text, o.email, o.first_name, o.middle_name, o.last_name, o.last_login_date, LOCALTIMESTAMP(0), 1, o.missed_reservation, o.user_alias, o.is_active
FROM auth_user_accounts o
WHERE email IS NOT NULL AND last_login_date IS NOT NULL AND id != 1;
-- id_seq
SELECT setval('bss_authn_accounts_id_seq', COALESCE((SELECT MAX(id) FROM bss_authn_accounts), 1), true);
COMMIT;


-- auth_entity_permissions => bss_authz_permissions
BEGIN;
-- purposes
INSERT INTO bss_authz_permissions (subject_azid, task, object_azid)
SELECT ('bss:core:authN/Account/' || o.subject_entity_id), o.task, ('at:ccheckin:purposes/Purpose/' || o.object_entity_id)
FROM auth_entity_permissions o
WHERE o.task = 'purpose have' OR o.task = 'purpose observe' OR o.task = 'purpose participate';
-- courses
INSERT INTO bss_authz_permissions (subject_azid, task, object_azid)
SELECT ('bss:core:authN/Account/' || o.subject_entity_id), o.task, ('at:ccheckin:courses/Course/' || o.object_entity_id)
FROM auth_entity_permissions o
WHERE o.task = 'course view';
-- roles
INSERT INTO bss_authz_permissions (subject_azid, task, object_azid) 
VALUES 
  ('at:ccheckin:authN/Role/2', 'admin', 'system'),
  ('at:ccheckin:authN/Role/2', 'edit system notifications', 'system'),
  ('at:ccheckin:authN/Role/2', 'receive system notifications', 'system'),
  ('at:ccheckin:authN/Role/2', 'reports generate', 'system'),
  ('at:ccheckin:authN/Role/3', 'course request', 'system'),
  -- ('at:ccheckin:authN/Role/3', 'course view', 'system'),
  ('at:ccheckin:authN/Role/4', 'room view schedule', 'system');
COMMIT;


-- auth_user_account_role_mappings => ccheckin_authn_account_roles
-- Have to migrate roles one at a time
BEGIN;
-- Application Admin
INSERT INTO ccheckin_authn_account_roles (account_id, role_id) VALUES (1, 2);
-- Administrator
INSERT INTO ccheckin_authn_account_roles (account_id, role_id)
SELECT o.account_id, 2
FROM auth_user_account_role_mappings o, auth_roles r, bss_authn_accounts a
WHERE o.role_id = r.id AND r.name = 'Admin' AND o.account_id = a.id;
-- Teacher
INSERT INTO ccheckin_authn_account_roles (account_id, role_id)
SELECT o.account_id, 3
FROM auth_user_account_role_mappings o, auth_roles r, bss_authn_accounts a
WHERE o.role_id = r.id AND r.name = 'Faculty' AND o.account_id = a.id;
-- CC Teacher
INSERT INTO ccheckin_authn_account_roles (account_id, role_id)
SELECT o.account_id, 4
FROM auth_user_account_role_mappings o, auth_roles r, bss_authn_accounts a
WHERE o.role_id = r.id AND r.name = 'CC Teacher' AND o.account_id = a.id;
-- Student
INSERT INTO ccheckin_authn_account_roles (account_id, role_id)
SELECT a.id, 5
FROM bss_authn_accounts a
WHERE a.id NOT IN (
  SELECT account_id FROM ccheckin_authn_account_roles);
COMMIT;


-- course_facet_types => ccheckin_course_facet_types
BEGIN;
INSERT INTO ccheckin_course_facet_types (id, name, sort_name)
SELECT o.id, o.name, o.sort_name
FROM course_facet_types o;
-- id_seq
SELECT setval('ccheckin_course_facet_types_id_seq', COALESCE((SELECT MAX(id) FROM ccheckin_course_facet_types), 1), true);
COMMIT;


-- courses => ccheckin_courses
BEGIN;
INSERT INTO ccheckin_courses (id, full_name, short_name, start_date, end_date, active)
SELECT o.id, o.full_name, o.short_name, o.start_date, o.end_date, o.active
FROM courses o;
-- id_seq
SELECT setval('ccheckin_courses_id_seq', COALESCE((SELECT MAX(id) FROM ccheckin_courses), 1), true);
COMMIT;


-- course_facets => ccheckin_course_facets
BEGIN;
INSERT INTO ccheckin_course_facets (id, course_id, type_id, description, tasks, student_hours, created_date)
SELECT o.id, o.course_id, o.type_id, o.description, o.tasks, o.student_hours, o.created_date
FROM course_facets o;
-- id_seq
SELECT setval('ccheckin_course_facets_id_seq', COALESCE((SELECT MAX(id) FROM ccheckin_course_facets), 1), true);
COMMIT;


-- course_requests => ccheckin_course_requests
BEGIN;
INSERT INTO ccheckin_course_requests (id, course_id, request_date, request_by_id)
SELECT o.id, o.course_id, o.request_date, o.request_by_id
FROM course_requests o;
-- id_seq
SELECT setval('ccheckin_course_requests_id_seq', COALESCE((SELECT MAX(id) FROM ccheckin_course_requests), 1), true);
COMMIT;


-- purposes => ccheckin_purposes
BEGIN;
INSERT INTO ccheckin_purposes (id, object_id, object_type)
SELECT o.id, o.object_id, 'Ccheckin_Courses_Facet'
FROM purposes o;
-- id_seq
SELECT setval('ccheckin_purposes_id_seq', COALESCE((SELECT MAX(id) FROM ccheckin_purposes), 1), true);
COMMIT;


-- rooms => ccheckin_rooms
BEGIN;
INSERT INTO ccheckin_rooms (id, name, description, observation_type, max_observers, deleted, schedule)
SELECT o.id, o.name, o.description, o.observation_type, o.max_observers, false, '{"0":{"9":"true","10":"true","11":"true","12":"true","13":"true","14":"true","15":"true","16":"true","17":"true"},"1":{"9":"true","10":"true","11":"true","12":"true","13":"true","14":"true","15":"true","16":"true","17":"true"},"2":{"9":"true","10":"true","11":"true","12":"true","13":"true","14":"true","15":"true","16":"true","17":"true"},"3":{"9":"true","10":"true","11":"true","12":"true","13":"true","14":"true","15":"true","16":"true","17":"true"},"4":{"9":"true","10":"true","11":"true","12":"true","13":"true","14":"true","15":"true","16":"true","17":"true"}}'
FROM rooms o;
-- id_seq
SELECT setval('ccheckin_rooms_id_seq', COALESCE((SELECT MAX(id) FROM ccheckin_rooms), 1), true);
COMMIT;


-- room_observations => ccheckin_room_observations
BEGIN;
INSERT INTO ccheckin_room_observations (id, room_id, purpose_id, account_id, start_time, end_time, duration)
SELECT o.id, o.room_id, o.purpose_id, o.account_id, o.start_time, o.end_time, o.duration
FROM room_observations o, rooms r, purposes p, bss_authn_accounts a
WHERE o.room_id = r.id AND o.purpose_id = p.id AND o.account_id = a.id;
-- id_seq
SELECT setval('ccheckin_room_observations_id_seq', COALESCE((SELECT MAX(o.id) FROM ccheckin_room_observations o, rooms r, purposes p WHERE o.room_id = r.id AND o.purpose_id = p.id), 1), true);
COMMIT;


-- room_reservations => ccheckin_room_reservations
BEGIN;
INSERT INTO ccheckin_room_reservations (id, room_id, observation_id, account_id, checked_in, start_time, end_time, missed, reminder_sent)
SELECT o.id, o.room_id, o.observation_id, o.account_id, o.checked_in, o.start_time, o.end_time, o.missed, CASE WHEN o.missed = true THEN true ELSE null END
FROM room_reservations o, rooms r, room_observations obs, bss_authn_accounts a
WHERE o.room_id = r.id AND o.observation_id = obs.id AND o.account_id = a.id;
-- id_seq
SELECT setval('ccheckin_room_reservations_id_seq', COALESCE((SELECT MAX(o.id) FROM ccheckin_room_reservations o, rooms r, room_observations obs WHERE o.room_id = r.id AND o.observation_id = obs.id), 1), true);
COMMIT;


-- semesters => ccheckin_semesters
BEGIN;
INSERT INTO ccheckin_semesters (id, display, start_date, end_date, open_date, close_date)
SELECT o.id, o.display, o.start_date, o.end_date, o.start_date, o.end_date
FROM semesters o;
-- id_seq
SELECT setval('ccheckin_semesters_id_seq', COALESCE((SELECT MAX(id) FROM ccheckin_semesters), 1), true);
COMMIT;


-- course_instructors, BUILD: students => ccheckin_course_enrollment_map
BEGIN;
-- Teachers 
INSERT INTO ccheckin_course_enrollment_map (account_id, course_id, role, enrollment_method)
SELECT o.account_id, o.course_id, 'Teacher', 'Migration' -- Migration? I dunno...
FROM course_instructors o, bss_authn_accounts a
WHERE o.account_id = a.id;
-- Students
INSERT INTO ccheckin_course_enrollment_map (account_id, course_id, role, enrollment_method)
SELECT DISTINCT a.id, c.id, r.name, 'Migration'
FROM bss_authn_accounts a, ccheckin_courses c, ccheckin_course_facets f, ccheckin_purposes p, bss_authz_permissions perm, ccheckin_authn_roles r, ccheckin_authn_account_roles ar
WHERE c.id = f.course_id 
	AND f.id = p.object_id 
	AND ('at:ccheckin:purposes/Purpose/' || p.id) = perm.object_azid 
	AND perm.subject_azid = ('bss:core:authN/Account/' || a.id)
	AND a.id = ar.account_id
	AND ar.role_id = r.id
	AND r.name = 'Student'
ORDER BY c.id;
COMMIT;


-- Email Defaults
BEGIN;

UPDATE at_config_settings
SET value = 'children@sfsu.edu'
WHERE key = 'email-default-address';

UPDATE at_config_settings
SET value = '
--
The Children''s Campus'
WHERE key = 'email-signature';

UPDATE at_config_settings
SET value = '
<p>|%FIRST_NAME%| |%LAST_NAME%|,</p><br>
<p>Children''s Campus has created an account for you in our Check-In web application.
You can access it at |%SITE_LINK%| using your SFSU ID and password.</p>
<p>If you need any further help, feel free to contact us by responding to this email address.</p>'
WHERE key = 'email-new-account';

UPDATE at_config_settings
SET value = '
<p>A new course has been requested by |%FIRST_NAME%| |%LAST_NAME%|.</p>
<p>Click here to view your requested course: |%REQUEST_LINK%|</p>
<p>You can go to the manage course requests page by clicking the provided link or copying the above URL into your browser.</p>'
WHERE key = 'email-course-requested-admin';

UPDATE at_config_settings
SET value = '
<p>Dear |%FIRST_NAME%| |%LAST_NAME%|,</p>
<p>You have requested a new course:</p>
<ul>
	<li>|%COURSE_FULL_NAME%|</li>
	<li>|%COURSE_SHORT_NAME%|</li>
	<li>|%SEMESTER%|</li>
</ul>
<br>
<p>Your request will be reviewed and you will be notified of our decision.</p>'
WHERE key = 'email-course-requested-teacher';

UPDATE at_config_settings
SET value = '
<p>Dear |%FIRST_NAME%| |%LAST_NAME%|,</p>
<p>Thank you for choosing the Children''s Campus for your students to conduct observations. 
The following course has been approved:</p>
<ul>
	<li>|%COURSE_FULL_NAME%|</li>
	<li>|%COURSE_SHORT_NAME%|</li>
</ul>
<br>
<p>Attached to this email you''ll find our guidelines for student observers, both in the classroom and the observation rooms.  
Please see that your students receive a copy of this prior to their first observation at the center.  
If you or the students have any questions about their observations they should contact us.</p>
<br>
<p>You can access it here |%COURSE_VIEW_LINK%|, using your SFSU ID and password.</p>
<br>
<p>Your students have already been automatically enrolled and will be able to make reservations from |%OPEN_DATE%| to |%CLOSE_DATE%|.</p>'
WHERE key = 'email-course-allowed-teacher';

UPDATE at_config_settings
SET value = '
<p>Dear Student,</p>
<p>You have been invited to conduct observations at Children''s Campus &mdash; SF State''s quality Early Care and Education Center. 
Here are the details of the course:</p>
<ul>
	<li>|%COURSE_FULL_NAME%|</li>
	<li>|%COURSE_SHORT_NAME%|</li>
</ul>
<br>
<p>Attached to this email you''ll find our guidelines for student observers, both in the classroom and the observation rooms. 
Please see that you read this documentation prior to your first observation at the center. 
If you have any questions about your observations they should contact me.</p>
<br>
<p>You can begin making reservations from |%OPEN_DATE%| until |%CLOSE_DATE%|, which can be done here |%SITE_LINK%|, using your SFSU ID and password to login.</p>'
WHERE key = 'email-course-allowed-students';

UPDATE at_config_settings
SET value = '
<p>Dear |%FIRST_NAME%| |%LAST_NAME%|,</p>
<p>Children''s Campus has denied the following course you requested:</p>
<ul>
	<li>|%COURSE_FULL_NAME%|</li>
	<li>|%COURSE_SHORT_NAME%|</li>
	<li>|%SEMESTER%|</li>
</ul>
<br>
<p>If you need any further help, feel free to contact us by responding to this email address.</p>'
WHERE key = 'email-course-denied';

UPDATE at_config_settings
SET value = '
<p>Dear |%FIRST_NAME%| |%LAST_NAME%|,</p>
<p>Thank you for choosing the Children''s Campus for your course observation requirement. 
Here are the details of your reservation:</p>				
<ul>
	<li>|%RESERVE_DATE%|</li>
	<li>|%PURPOSE_INFO%|</li>
	<li>|%ROOM_NAME%|</li>
	<li>|%RESERVE_VIEW_LINK%|</li>
</ul>
<br>
<p>If you made this reservation by mistake, please cancel your reservation here |%RESERVE_CANCEL_LINK%|. 
Observation at the Children''s Campus is a privilege and should not be taken for granted. 
Thank you for understanding.</p>'
WHERE key = 'email-reservation-details';

UPDATE at_config_settings
SET value = '
<p>Dear |%FIRST_NAME%| |%LAST_NAME%|,</p>
<p>This is a reminder about your upcoming reservation at Children''s Campus for your course observation requirement. 
Here are the details of your reservation:</p>
<ul>
	<li>|%RESERVE_DATE%|</li>
	<li>|%PURPOSE_INFO%|</li>
	<li>|%ROOM_NAME%|</li>
	<li>|%RESERVE_VIEW_LINK%|</li>
</ul>
<br>
<p>If you need to cancel this reservation, please do so immediately here |%RESERVE_CANCEL_LINK%|. 
Observation at the Children''s Campus is a privilege and should not be taken for granted. 
Thank you for understanding.</p>'
WHERE key = 'email-reservation-reminder';

UPDATE at_config_settings
SET value = '1 day'
WHERE key = 'email-reservation-reminder-time';

UPDATE at_config_settings
SET value = '
<p>Dear |%FIRST_NAME%| |%LAST_NAME%|,</p>
<p>Thank you for choosing the Children''s Campus for your course observation requirement.  
Our online reservation system is showing that you missed your last observation appointment.  
<u>If you miss one more</u>, the system will remove all future reservations that you''ve created.  
If the system removes your reservations, you will be allowed to re-reserve rooms but the same rules will apply.  
Please respect that other students need to do observations and are affected by your decision to miss your appointment.</p>
<p>Here are the details of your missed reservation:</p>
<ul>
	<li>|%RESERVE_DATE%|</li>
	<li>|%PURPOSE_INFO%|</li>
	<li>|%RESERVE_MISSED_LINK%|</li>
</ul>
<br>
<p>In the future if you''re going to miss your observation appointment, you should cancel your reservation in advance.  
Many students want to observe at the CC but cannot due to our full reservation system.</p>
<p>Observation at the Children''s Campus is a privilege and should not be taken for granted.  
Thank you for understanding.</p>'
WHERE key = 'email-reservation-missed';

UPDATE at_config_settings
SET value = '
<p>Dear |%FIRST_NAME%| |%LAST_NAME%|,</p>
<p>You reservation at Children''s Campus has been canceled.</p>
<p>Here are the details of your canceled reservation:</p>
<ul>
	<li>|%RESERVE_DATE%|</li>
	<li>|%PURPOSE_INFO%|</li>
	<li></li>
</ul>
<br>
<p>Please contact us with any questions or |%RESERVE_SIGNUP_LINK%| if needed.
Thank you for understanding.</p>'
WHERE key = 'email-reservation-canceled';

COMMIT;
-- END Email Defaults ***************************************************


-- Site text defaults
BEGIN;
UPDATE at_config_settings
SET value = '<h1>Welcome to Children''s Campus</h1>'
WHERE key = 'welcome-title';

UPDATE at_config_settings
SET value = '
<p>Children''s Campus supports positive child development through quality care and education for approximately 85 infants, toddlers and preschool children. 
The Children''s Campus also provides opportunities for student internships in a variety of disciplines such as teaching, nursing, child development, psychology, and social work. 
Faculty and student research is encouraged to improve best practices in early care and education, and the facility will serve as a site for observation to augment classroom instruction. 
The Children''s Campus has been designed and staffed with highly qualified professionals in order to meet state and federal licensing and accreditation requirements and is supported 
by an advisory board of participating parents, faculty, and staff.</p>'
WHERE key = 'welcome-text';

UPDATE at_config_settings
SET value = '
<p><strong>Students: Please take note of our location!</strong></p>
<p>The Children''s Campus is located on the campus of San Francisco State University at the corner of North State Drive and Lake Merced Blvd.  We are beside the Library Annex.  
If you need help locating our center, please check the <u><a href="http://www.sfsu.edu/~sfsumap/" title="Opens in a new window." target="_blank" class="popup">campus map</a></u></p>'
WHERE key = 'location-message';

UPDATE at_config_settings
SET value = '
<h2>Children''s Campus At-A-Glance:</h2>
<ul>
	<li>Priority enrollment for children of SF State faculty and staff.  Community families are welcome, space permitting.</li>
	<li>Serving children from 6 months to 5 years of age.</li>

	<li>Full-day, year-round program operating on the SFSU academic calendar:
		<div style="font-style: italic;">
		Closed New Year''s Day, Martin Luther King Day, Lincoln''s Birthday (as observed), Washington''s Birthday (as observed), 
		Cesar Chavez Day, Memorial Day, Independence Day, Labor Day, Veterans Day, Thanksgiving (Thursday/Friday only), December 
		campus closure (Christmas Day, observed Admissions Day, and delayed observed holidays).  See Operational Calendar for exact dates. 
		</div>
		</li>
	<li>Located on the SF State campus at the corner of North State Drive and Lake Merced Blvd.</li>
	<li>Hours of operation:  7:30 am - 5:30 pm, Monday through Friday.</li>
	<li>Eligible for the University''s Dependent Care Reimbursement Program through which employees may allocate up to $5,000 per year pre-tax for child care. 
		(<a href="http://www.sfsu.edu/~hrwww/benefits/flexacct.html">Details available here</a>).</li>

</ul>'
WHERE key = 'welcome-text-extended';

-- UPDATE at_config_settings
-- SET value = '
-- ["Observation and participation times are reserved on a <b>\"First Come\u2013First Serve\"<\/b> basis.&nbsp;Please plan your reservations accordingly.",
-- "To view your past observations, click the <b><a href=\"http:\/\/childrenscampus.dev.at.sfsu.edu\/reservations\/observations\" target=\"_self\" rel=\"\">
-- Past Observations<\/a><\/b> link and choose the course you want to view."]'
-- WHERE key = 'announcements';

UPDATE at_config_settings
SET value = '
<strong>Children''s Campus</strong><br>

San Francisco State University<br><br>
<strong>Physical Address:</strong><br>
North State Drive @ Lake Merced Boulevard<br><br>
<strong>Mailing Address:</strong><br>
1600 Holloway Ave.<br>
San Francisco, CA 94132<br><br>
Email: <a href="mailto:children@sfsu.edu">children@sfsu.edu</a><br>
Phone: 415-405-4011<br>
Fax: 415-405-3832<br>
'
WHERE key = 'contact-info';

UPDATE at_config_settings
SET value = '
<p class="instructions">
   <b>Instructions for requesting a course:</b>
</p>
<ol style="text-align: left">
    <li>Choosing a semester will update your available courses.</li>
    <li>Select one your courses from the list.</li>
    <li>Select the tasks that students are to perform.</li>
</ol>
<p><em>Enrolled students will be automatically added to your course and managed by the Children''s Campus Check-In application.</em></p> 
'
WHERE key = 'course-request-text';

COMMIT;
-- END Site Text Defaults ***************************************************


-- DROP OLD DATABASE TABLES
-- drop tables
BEGIN;
DROP TABLE 
academia_disciplines,
academia_positions,
academia_universities,
auth_access_levels,
auth_entities,
auth_entity_permissions,
auth_forgot_tokens,
auth_invitations,
auth_ip_role_assignments,
auth_roles,
auth_user_account_role_mappings,
auth_user_accounts,
course_facet_types,
course_facets,
course_instructors,
course_requests,
course_user_requests,
courses,
family_purposes,
purposes,
report_saved_reports,
research,
room_observations,
room_reservations,
rooms,
semesters,
service_providers;
COMMIT;

-- drop id_seq sequences
BEGIN;
DROP SEQUENCE 
academia_discipline_id_seq,
academia_position_id_seq,
academia_university_id_seq,
auth_entities_id_seq,
auth_invitations_id_seq,
cframe_content_list_modules_entry_id_seq,
cframe_content_modules_id_seq,
cframe_content_resource_id_seq,
cframe_image_manipulation_id_seq,
content_types_id_seq,
course_facet_types_id_seq,
course_instructors_id_seq,
course_requests_id_seq,
course_user_requests_id_seq,
file_bundle_comments_id_seq,
file_purposes_id_seq,
license_terms_id_seq,
metadata_records_id_seq,
purpose_users_id_seq,
room_observations_id_seq,
room_reservations_id_seq,
semesters_id_seq;
COMMIT;

-- END MIGRATION
-- ******************** Now go to /admin/migrate ************************
-- **********************************************************************
-- **********************************************************************
