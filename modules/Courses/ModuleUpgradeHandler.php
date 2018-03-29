<?php

/**
 * Upgrade/Install this module.
 * 
 * @author      Steve Pedersen (pedersen@sfsu.edu)
 * @copyright   Copyright &copy; San Francisco State University.
 */
class Ccheckin_Courses_ModuleUpgradeHandler extends Bss_ActiveRecord_BaseModuleUpgradeHandler
{
    public function onModuleUpgrade ($fromVersion)
    {
        $siteSettings = $this->getApplication()->siteSettings;
        switch ($fromVersion)
        {
            case 0:
                $siteSettings->defineProperty('course-tasks', 'List of tasks that can be assigned to a particular course. JSON encoded data.', 'string');
                $siteSettings->defineProperty('course-request-text', 'Instruction text for requesting a course.', 'string');
                
                /**
                *   Create tables
                */
                $def = $this->createEntityType('ccheckin_courses', $this->getDataSource('Ccheckin_Courses_Course'));
                $def->addProperty('id', 'int', array('sequence' => true, 'primaryKey' => true));
                $def->addProperty('full_name', 'string');               
                $def->addProperty('short_name', 'string');
                $def->addProperty('department', 'string');
                $def->addProperty('start_date', 'datetime');
                $def->addProperty('end_date', 'datetime');
                $def->addProperty('active', 'bool');
                $def->addProperty('deleted', 'bool');
                $def->addProperty('external_course_key', 'string');
                $def->save();

                $def = $this->createEntityType('ccheckin_course_enrollment_map', $this->getDataSource('Ccheckin_Courses_Course'));
                $def->addProperty('account_id', 'int', array('primaryKey' => true));
                $def->addProperty('course_id', 'int', array('primaryKey' => true));
                $def->addProperty('term', 'string');
                $def->addProperty('role', 'string');
                $def->addProperty('enrollment_method', 'string');
                $def->addProperty('drop_date', 'datetime');
                $def->addForeignKey('bss_authn_accounts', array('account_id' => 'id'));
                $def->addForeignKey('ccheckin_courses', array('course_id' => 'id'));
                $def->save();               

                $def = $this->createEntityType('ccheckin_course_facet_types', $this->getDataSource('Ccheckin_Courses_FacetType'));
                $def->addProperty('id', 'int', array('sequence' => true, 'primaryKey' => true));
                $def->addProperty('name', 'string');
                $def->addProperty('sort_name', 'string');
                $def->save();

                $def = $this->createEntityType('ccheckin_course_facets', $this->getDataSource('Ccheckin_Courses_Facet'));
                $def->addProperty('id', 'int', array('sequence' => true, 'primaryKey' => true));
                $def->addProperty('course_id', 'int');
                $def->addProperty('type_id', 'int');
                $def->addProperty('description', 'string');
                $def->addProperty('tasks', 'string');
                $def->addProperty('student_hours', 'int');
                $def->addProperty('created_date', 'datetime');
                $def->addForeignKey('ccheckin_courses', array('course_id' => 'id'));
                $def->addForeignKey('ccheckin_course_facet_types', array('type_id' => 'id'));
                $def->save();

                $def = $this->createEntityType('ccheckin_course_requests', $this->getDataSource('Ccheckin_Courses_Request'));
                $def->addProperty('id', 'int', array('sequence' => true, 'primaryKey' => true));
                $def->addProperty('course_id', 'int');
                $def->addProperty('request_date', 'datetime');
                $def->addProperty('request_by_id', 'int');               
                $def->addForeignKey('ccheckin_courses', array('course_id' => 'id')); 
                $def->addForeignKey('bss_authn_accounts', array('request_by_id' => 'id'));
                $def->save();

                break;

        }
    }
}





