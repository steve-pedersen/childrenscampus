<?php

/**
 * Interface for extensions that wish to execute periodic code.
 * 
 * @author  Steve Pedersen (pedersen@sfsu.edu)
 * @copyright   Copyright &copy; San Francisco State University.
 */
class Ccheckin_Courses_CronJob extends Bss_Cron_Job
{
    const PROCESS_ACTIVE_JOBS_EVERY = 0; // 2 minutes

    public function run ($startTime, $lastRun, $timeDelta)
    {
        if ($timeDelta >= self::PROCESS_ACTIVE_JOBS_EVERY)
        {
            $semesters = $this->schema('Ccheckin_Semesters_Semester');
            $app = $this->getApplication();

            $semesterCode = $semesters->guessActiveSemester();

            $importer = $app->moduleManager->getExtensionByName('at:ccheckin:courses/enrollments', 'classdata');
            $importer->updateCourseEnrollments($semesterCode);

            $importer->archiveCourses();

            return true;
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
