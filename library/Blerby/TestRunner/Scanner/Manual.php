<?php
/**
  Copyright 2006-2010, Elijah Insua
  BSD Licensed (see: LICENCE.txt)
*/
/**
 * @author     Elijah Insua
 * @package    Blerby_TestRunner
 * @subpackage  Blerby_TestRunner_Scanner
 * @version    #0.6#
 */

require_once BTR_LIB_PATH . "/Scanner.php";
require_once BTR_LIB_PATH . "/Config.php";

/**
 * Bunk Scanner, does nothing really.
 * 
 * @author Elijah Insua
 * @version #0.6#
 * @package Blerby_TestRunner
 */
class Blerby_TestRunner_Scanner_Manual extends Blerby_TestRunner_Scanner
{

    /**
     * Constructor
     * 
     * @param Blerby_TestRunner_Config $blerby
     */
    public function __construct(Blerby_TestRunner_Config $config)
	{
        parent::__construct($config);
    }
    
    public function getScanTimeout()
    {
        return 0;   
    }    
    
    /**
     * Store modtime information
     * 
     * @return void
     */
    public function save() {}
    
    /**
     * attempt to load a cache
     * 
     */
    protected function load() {}

    /**
     * Get Deleted Files
     * 
     * @return array
     */
    public function getDeletions() {}

    /**
     * Add a file change
     * 
     * @param string $file
     * @return bool
     */
    public function addFile($file) { }
    
    /**
     * Scan directory for files
     * 
     * @param string $path
     * @param bool $recursive
     */
    public function scan($path, $recursive = true) {}
}
