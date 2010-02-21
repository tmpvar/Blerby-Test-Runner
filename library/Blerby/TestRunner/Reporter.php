<?php
/**
  Copyright 2006-2010, Elijah Insua
  BSD Licensed (see: LICENCE.txt)
*/

/**
 * @author     Elijah Insua
 * @package    Blerby_TestRunner
 * @subpackage  Blerby_TestRunner_Reporter
 * @version    #0.6#
 */

require_once "Blerby/TestRunner/Result.php";

/**
 * Report test results in an observer fashion
 * 
 * @author Elijah Insua
 * @version #0.6#
 * @package Blerby_TestRunner
 */
class Blerby_TestRunner_Reporter
{
	
    /**
     * Reporter configuration
     * 
     * @var Blerby_TestRunner_Config
     */
    protected $config;
    
    /**
     * Result storage
     * 
     * @var array
     */
    protected $aResults;
    
    /**
     * Build a test reporter
     * 
     * @param Blerby_TestRunner_Config $config
     */
    public function __construct(Blerby_TestRunner_Config $config)
	{
        $this->config   = $config;
        $this->aResults = array();
	}

    /**
     * Add a test result
     * 
     * @param string $file
     * @param Blerby_TestRunner_Result $result
     */
    public function addResult($file, Blerby_TestRunner_Result $result)
    {
        $this->aResults[$file][] = $result;
    }
    
    /**
     * Convert the test result into a string
     * 
     * @return string
     */    
    public function toString()
    {
        // ** Default behavior :: return the number of results
        return count($this->aResults);
    }

    /**
     * Set an exception, this obviously means a test has failed.
     * 
     * @param Exception $e
     */
    public function setException(Exception $e)
    {
        
        $result = new Blerby_TestRunner_Result();
        
        $trace = $e->getTrace();
        
        if (isset($trace[0]['args'][0])) {
            $file = $trace[0]['args'][0];
            
        } else {
            $file = Blerby_TestRunner_Util::cleanPath($e->getFile());   
        }
        
        $result->set('status',Blerby_TestRunner_Status::ERROR);
        $result->set('file',Blerby_TestRunner_Util::cleanPath($file));
        $result->set("message",$e->getMessage());
        
        preg_match("/on line ([0-9]+)/",$result->get("message"),$matches);
        
        if (isset($matches[1])) {
            
            $result->set('line',$matches[1]);
        } else {
             $result->set('line',"???");
        }
        
        $this->addResult($file,$result);
    }
}
