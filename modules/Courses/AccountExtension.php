<?php

/**
 * Adds properties/methods to accounts.
 * 
 * @author      Steve Pedersen (pedersen@sfsu.edu)
 * @copyright   Copyright &copy; San Francisco State University.
 */
class Ccheckin_Courses_AccountExtension extends Bss_AuthN_AccountExtension //implements Bss_AuthN_IAccountSettingsExtension
{
    
    /**
     * Get all properties to add to an account.
     * 
     * @return array
     */
    public function getExtensionProperties ()
    {
        return array(
            'enrollments' => array('N:M',
                'to' => 'Ccheckin_Courses_Course',
                'via' => 'ccheckin_course_enrollment_map',
                'fromPrefix' => 'account',
                'toPrefix' => 'course',
                'properties' => array('term' => 'string', 'role' => 'string', 'enrollment_method' => 'string', 'drop_date' => 'datetime')
            ),
        );
    }

    // TODO: FIgure this out *******************************
    public function getSubjectProxies ($account)
    {
        return $account->enrollments->asArray();
        // $reservation = $res->find($res->account->id->equals($account->id));
        // return $reservation->asArray();
    }

    /**
     * Get the methods to add to instances of the account class.
     * 
     * @return array
     */
    public function getExtensionMethods ()
    {
        return array('handleSettings');
    }

     /**
     * Get the weight of these settings, which determines their order in
     * the form. A heavier item always comes after a lighter item. Two
     * items of the same weight are presented in the order they are
     * loaded, which may vary.
     * 
     * @return int
     */
    public function getAccountSettingsWeight ()
    {
        return 10;
    }
    
    /**
     * Get the path to a template file for rendering as part of the
     * account settings form. May return null if this extension does not
     * render any settings (the extension's processAccountSettings method
     * will still be called).
     * 
     * @return string
     */
    public function getAccountSettingsTemplate ()
    {
        return null;
        // return $this->getModule()->getResource('_settings.html.tpl');
    }
    
    public function getAccountSettingsTemplateVariables (Bss_Routing_Handler $handler)
    {
        // $courses = $handler->schema('Ccheckin_Courses_Course');
        
        // return array('missedReservations' => $missedReservations);
        return array();
    }
    
    /**
     * Called when the settings form is submitted with the request that
     * submitted the form and the account instance for which the settings
     * are being modified.
     * 
     * @param Bss_AuthZ_IParticipant $viewer
     *    The person who is submitting the form (for "Account settings", this
     *    is the person that is logged in; for editing an account, it's the
     *    administrator doing the editing).
     * @param Bss_Core_IRequest $request
     *    The request that has submitted the form.
     * @param Bss_AuthN_Account $account
     *    The account for which the settings have been submitted.
     * @param array& $errorMap
     *    A reference to an associative array mapping field names to arrays of
     *    error messages related to that field. This method will modify this
     *    error map with any errors that it causes to be set. If any errors are
     *    set in the error map, this method must return false.
     * @return bool
     *    True if the submission did not contain any errors for this settings
     *    extension. Else false. If any errors are set into the error map, this
     *    method must return false.
     */
    public function processAccountSettings (Bss_AuthZ_IParticipant $viewer, Bss_Core_IRequest $request, Bss_AuthN_Account $account, &$errorMap)
    {
        $authZ = $this->getApplication()->authorizationManager;
        
        if ($authZ->hasPermission($viewer, 'admin'))
        {

        }
        if (empty($errorMap))
        {
            return true;
        }
        return false;
    }

}
