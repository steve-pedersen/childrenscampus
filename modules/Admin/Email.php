<?php

class Ccheckin_Admin_Email extends Bss_ActiveRecord_Base
{
    
    public static function SchemaInfo ()
    {
        return array(
            '__type' => 'ccheckin_admin_email',
            '__pk' => array('id'),
            
            'id' => 'int',
            
            'type' => 'string',         
            'creationDate' => array('datetime', 'nativeName' => 'creation_date'),
            'senderId' => array('int', 'nativeName' => 'sender_id'),
            'recipients' => 'string', // array('string', 'nativeName' => 'recipients'),
            'subject' => 'string',

            'sender' => array('1:1', 'to' => 'Bss_AuthN_Account', 'keyMap' => array('sender_id' => 'id')),
            // 'recipients' => array('1:N', 'to' => 'Bss_AuthN_Account', ....),
        );
    }

    // public function addLogEntry($type, $message, $viewer)
    // {
    //     $entry = $this->logs->getReference()->getToSchema()->createInstance();
    //     $entry->request = $this;
    //     $entry->faculty = $this->faculty;
    //     $entry->term = $this->term;
    //     $entry->type = $type;
    //     $entry->message = $message;
    //     $entry->entryDate = new DateTime;
    //     $entry->enteredBy = $viewer;
    //     $this->logs->add($entry);
    // }
}