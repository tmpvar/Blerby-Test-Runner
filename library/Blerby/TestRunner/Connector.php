<?php
/**
  Copyright 2006-2010, Elijah Insua
  BSD Licensed (see: LICENCE.txt)
*/

/**
 * @license    ##LICENSE_URL##
 * @author     Elijah Insua
 * @package    Blerby_TestRunner
 * @subpackage  Blerby_TestRunner_Connector
 * @version    #0.6#
 */

/**
 * Connect scanned tests to their sources
 * 
 * @author Elijah Insua
 * @version #0.6#
 * @package Blerby_TestRunner
 */
class Blerby_TestRunner_Connector
{
    
    /**
     * Configuration object
     * 
     * @var Blerby_TestRunner_Config
     */
    protected $config;
    
    /**
     * Setup Connector
     * 
     * @param Blerby_TestRunner_Config $config
     */
    public function __construct(Blerby_TestRunner_Config $config)
    {
        $this->config = $config;   
    }
    
    /**
     * Default connector
     * 
     * @param string $path
     */
    public function process($path, $baseTestPath = "")
    {
        return true;   
    }

    /**
     * Clear the caches
     * 
     * @return void
     */
    public function clearnCache()
    {
        
    }

    /**
     * Save Dependency cache
     * 
     * @return void
     */
    public function save()
    {
        
    }
}
