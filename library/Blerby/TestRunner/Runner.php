<?php
/**
  Copyright 2006-2010, Elijah Insua
  BSD Licensed (see: LICENCE.txt)
*/

/**
 * @author     Elijah Insua
 * @package    Blerby_TestRunner    
 * @subpackage  Blerby_TestRunner_Runner
 * @version    #0.6#
 */

/**
 * Test runner
 * 
 * @author Elijah Insua
 * @version #0.6#
 * @package Blerby_TestRunner
 */
class Blerby_TestRunner_Runner
{
    /**
     * Test Runner Configuration
     * 
     * @var Blerby_TestRunner_Config
     */
    protected $config;
    
    /**
     * Test Runner Files
     * 
     * @var array
     */
    protected $aFiles;
    
    /**
     * Test Runner Directories
     * 
     * @var array
     */
    protected $aDirs;
    
    /**
     * Test Result Reporters
     * 
     * @var Blerby_TestRunner_Reporter
     */
    protected $aReporters;
    
    /**
     * Track test dependancies
     * 
     * @var array
     */
    protected $aDependencies;
    
    /**
     * Construct a Blerby_TestRunner_Runner
     * 
     * @param Blerby_TestRunner_Config $config
     */
    public function __construct(Blerby_TestRunner_Config $config)
    {
        $this->config = $config;
        $this->aFiles = array();
        $this->aDirs  = array();

        // ** Include all of the runner required paths **        
        $path = $this->config->get("options/suite/path",false);
        
        // ** Add the testing suite to the the dependency blacklist **
        Blerby_TestRunner_Init::addBlacklistPath('dependency',(string)$path);
        
        // ** Setup the include paths **
        if ($path) {
            $includePath = ini_get("include_path");
            ini_set('include_path', $includePath . PATH_SEPARATOR . $path . "/");
        }
        
        // ** Require Suite files **
        $includes = $this->config->get("options/suite/includes");

        if ($includes)
        {
            foreach ($includes->file as $include)
            {
                require_once (string)$include;   
            }
        }

        // ** Setup the reporters **
        foreach ($this->config->get("reporters")->reporter as $r)
        {
            $reporterConfig = new Blerby_TestRunner_Config($r);
            $reporterType = "Blerby_TestRunner_Reporter_" . $r->type;
            $reporter = Blerby_TestRunner_ServiceLocator::get($reporterType,$reporterConfig);
            $this->addReporter($r->type, $reporter);
        }


        
        
    }
    
    /**
     * Add a file to be tested
     * 
     * @param string $path
     * @return bool
     */
    public function setFile($path)
    {
        $this->aFiles = $path;
        return true;
    }
    
    /**
     * Add a directory to be tested
     *
     * @param string $path
     * @return bool
     */
    public function addDir($path)
    {
        $this->aDir[$path] = $path;
        return true;
    }
    
    /**
     * Get Dynamic dependancies
     * Note: this is the only way to resolve dynamic includes
     * 
     * @return array
     */
    public function getDependencies()
    {
        return $this->calculateDependencies();
    }

    /**
     * Return the  difference between previous dependancies and current
     * 
     * @return array
     */    
    public function calculateDependencies()
    {
        // TODO: implement this for multiple files
        return array($this->aFiles=>get_included_files());         
    }
    
    /**
     * Execute selected files and dirs
     * 
     * @return bool
     */
    public function process()
    {
        return true;
    }
    
    /**
     * Add a test reporter
     * 
     * @param Blerby_TestRunner_Reporter
     * @return bool
     */
    public function addReporter($name, Blerby_TestRunner_Reporter $reporter)
    {
        $this->aReporters[(string)$name] = $reporter;   
    }
    
    /**
     * Get a specific report by reporter short name
     * 
     * @param string $name
     * @return Blerby_TestRunner_Reporter
     */
    public function getReporter($name)
     {
        if (isset($this->aReporters[$name])) {
            return $this->aReporters[$name];
        } else {
            return new Blerby_TestRunner_Reporter();   
        }  
     }
}
