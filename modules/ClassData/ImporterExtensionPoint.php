<?php

/**
 * Extensions provide data importers.
 *
 * @package at:ccheckin:classdata
 * @author  Steve Pedersen (pedersen@sfsu.edu)
 **/
class Ccheckin_ClassData_ImporterExtensionPoint extends Bss_Core_ExtensionPoint
{
	public function getUnqualifiedName () { return 'importerextension'; }
	public function getDescription () { return 'Extensions provide ClassData importers.'; }
	public function getRequiredInterfaces () { return array('Ccheckin_ClassData_ImporterExtension'); }
}