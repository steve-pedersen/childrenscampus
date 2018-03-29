<?php

/**
 * Extensions provide data importers.
 *
 * @package at:ccheckin:classdata
 * @author  Steve Pedersen (pedersen@sfsu.edu)
 **/
class Ccheckin_Courses_EnrollmentsImporterExtensionPoint extends Bss_Core_ExtensionPoint
{
	public function getUnqualifiedName () { return 'enrollments'; }
	public function getDescription () { return 'Extensions provide ClassData importers.'; }
	public function getRequiredInterfaces () { return array('Ccheckin_Courses_EnrollmentsImporterExtension'); }
}