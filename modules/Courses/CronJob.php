<?php

/**
 * Interface for extensions that wish to execute periodic code.
 * 
 * @author  Steve Pedersen (pedersen@sfsu.edu)
 * @copyright   Copyright &copy; San Francisco State University.
 */
class Ccheckin_Courses_CronJob extends Bss_Cron_Job
{
    const PROCESS_ACTIVE_JOBS_EVERY = 60 * 24; // once a day

    public function run ($startTime, $lastRun, $timeDelta)
    {
        if ($timeDelta >= self::PROCESS_ACTIVE_JOBS_EVERY)
        {
            $app = $this->getApplication();
            $importer = $app->moduleManager->getExtensionByName('at:ccheckin:courses/enrollments', 'classdata');           
            $importer->updateCourseEnrollments();
            
            $this->archiveCourses();

            return true;
        }
    }

    public function archiveCourses ()
    {
        $app = $this->getApplication();
        $schemaManager = $app->schemaManager;
        $semesters = $schemaManager->getSchema('Ccheckin_Semesters_Semester');
        $courses = $schemaManager->getSchema('Ccheckin_Courses_Course');
        $now = new DateTime;
        
        $expired = $courses->find(
            $courses->endDate->before($now)->andIf(
                $courses->active->isTrue())->andIf(
                $courses->deleted->isNull()->orIf(
                    $courses->deleted->isFalse())
            )
        );
        
        foreach ($expired as $course)
        {
            // // TODO: is this necessary to remove each student's permissions, i.e. course purpose?
            // $facet = $course->facets->index(0);
            // $students = $course->students;        
            // foreach ($students as $student)
            // {
            //     $facet->removeUser($student);
            // }

            $course->active = false;
            $course->save();
        }
    }

    protected function getCourse ($id)
    {
        $service = new Ccheckin_ClassData_Service($this->getApplication());
        list($status, $course) = $service->getCourse($id);
        
        if ($status < 400)
        {
            return $course;
        }
        return false;
    }
    
}
