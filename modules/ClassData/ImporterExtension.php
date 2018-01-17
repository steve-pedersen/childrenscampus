<?php

/**
 * Base class for extensions to point at:ccheckin:classdata/importer.
 * 
 * @copyright	Copyright &copy; San Francisco State University.
 * @author	Steve Pedersen (pedersen@sfsu.edu)
 */
abstract class Ccheckin_ClassData_ImporterExtension extends Bss_Core_NamedExtension
{
	public static function getExtensionPointName () { return 'at:ccheckin:classdata/importerextension'; }
    
    public function getDataSource ($alias = 'default')
    {
        return $this->getApplication()->dataSourceManager->getDataSource($alias);
    }
    
    public function schema ($recordClass)
    {
        return $this->getApplication()->schemaManager->getSchema($recordClass);
    }
}