<?php

/**
 * Upgrade this module.
 * 
 * @author      Steve Pedersen (pedersen@sfsu.edu)
 * @copyright   Copyright &copy; San Francisco State University.
 */
class Ccheckin_Admin_ModuleUpgradeHandler extends Bss_ActiveRecord_BaseModuleUpgradeHandler
{
    public function onModuleUpgrade ($fromVersion)
    {
        $app = $this->getApplication();
        $settings = $app->siteSettings;
        
        switch ($fromVersion)
        {
            case 0:
                $settings->defineProperty('siteNotice', 'A highly-visible notice that gets displayed on every page.', 'textarea');
                $settings->defineProperty('blocked-dates', 'A JSON encoded string of dates for when Childrens Campus is closed and reservations are unavailable', 'string');
                $settings->defineProperty('migration-complete', 'If true, the endpoint /admin/migrate will no longer run.', 'bool');

                // Email
                $settings->defineProperty('email-testing-only', 'If set, email will only be sent to the test address. SHOULD ONLY TURNED ON FOR TESTING.', 'int');
                $settings->defineProperty('email-test-address', 'Email address for testing. If set, all emails will be sent here.', 'string');
                $settings->defineProperty('email-default-address', 'The default email address from which emails will be sent.', 'string');
                $settings->defineProperty('email-signature', 'A signature to use for the bottom of every email.', 'string');
                $settings->defineProperty('email-new-account', 'Email content for notifying a user that a new account has been created for them.', 'string');
                $settings->defineProperty('email-course-allowed-teacher', 'Email content for the Course Allowed email to send to the Requesting Teacher.', 'string');
                $settings->defineProperty('email-course-allowed-students', 'Email content for the Course Allowed email to send to the Students in the course.', 'string');
                $settings->defineProperty('email-course-denied', 'Email content for the Course Denied email to send to Requesting Teacher.', 'string');
                $settings->defineProperty('email-course-requested-admin', 'Email content for the Course Requested email to the Admin.', 'string');
                $settings->defineProperty('email-course-requested-teacher', 'Email content for the Course Requested email to Requesting Teacher.', 'string');
                $settings->defineProperty('email-reservation-details', 'Email content for when a Student makes a reservation to send to Requesting Student.', 'string');
                $settings->defineProperty('email-reservation-reminder', 'Email content to remind student about upcoming reservation.', 'string');
                $settings->defineProperty('email-reservation-reminder-time', 'Setting for when to send out reminders (1 day, 2 hours prior, etc.).', 'string');
                $settings->defineProperty('email-reservation-missed', 'Email content for when a Student misses a reservation to send to Student.', 'string');


                $def = $this->createEntityType('ccheckin_admin_files', $this->getDataSource('Ccheckin_Admin_File'));
                $def->addProperty('id', 'int', array('sequence' => true, 'primaryKey' => true));
                $def->addProperty('remote_name', 'string');
                $def->addProperty('local_name', 'string');
                $def->addProperty('content_type', 'string');
                $def->addProperty('content_length', 'int');
                $def->addProperty('hash', 'string');
                $def->addProperty('temporary', 'bool');
                $def->addProperty('title', 'string');
                $def->addProperty('attached_email_keys', 'string');
                $def->addProperty('uploaded_date', 'datetime');
                $def->addProperty('uploaded_by_id', 'int');
                $def->addForeignKey('bss_authn_accounts', array('uploaded_by_id' => 'id'));
                $def->save();

                $def = $this->createEntityType('ccheckin_email_log', $this->getDataSource('Ccheckin_Admin_EmailLog'));
                $def->addProperty('id', 'int', array('sequence' => true, 'primaryKey' => true));
                $def->addProperty('type', 'string');
                $def->addProperty('creation_date', 'datetime');
                $def->addProperty('recipients', 'string');
                $def->addProperty('subject', 'string');
                $def->addProperty('body', 'string');
                $def->addProperty('attachments', 'string');
                $def->addProperty('success', 'bool');
                $def->save();

                break;
        }
    }
}