<?php
 /*******************************************\ 

	Blerby Components (c) 2008 Sproutware

                 ##LICENSE##

    Author:   Elijah Insua (Feb 11, 2008)
    Filename: Init.php
    Package:  package_declaration
    
 \*******************************************/

/**
 * @license    ##LICENSE_URL##
 * @author     Elijah Insua
 * @package    
 * @subpackage 
 * @version    #0.6#
 */



/**
 * 
 * @author Elijah Insua
 * @version #0.6#
 * @package Blerby
 */
class Blerby_TestRunner_Init
{
    /**
     * Resource Holder
     * 
     * @var array 
     */
    private $aRes;
    
    public static $instance = false; 
    
    protected $aBlacklistPath = array();
    
    /**
     * Include required files
     * 
     * @return void
     */
    public static function doIncludes()
    {
        // ** Setup paths **
        $path = realpath(dirname(__FILE__));
        define("BTR_PATH",$path);
        define("BTR_LIB_PATH",$path);
        
        // ** Blacklist BTR directories from dependency caching **
        $exclude = realpath(BTR_PATH . "../../../../");
        Blerby_TestRunner_Init::addBlacklistPath('dependency',$exclude);

        // ** Start execution timer **        
        self::set("startTime", microtime(false));        
        
        // ** Include Required Files **
        require_once BTR_PATH . "/Config.php";
        require_once BTR_PATH . "/Scanner.php";
        require_once BTR_PATH . "/ServiceLocator.php";
        require_once BTR_PATH . "/Util.php";        
    }
    
    public static function start()
    {
        if (!self::$instance) {
            self::$instance = new Blerby_TestRunner_Init();
            self::doIncludes();

            
            if (isset($_SERVER['argv'][1])) {
                $params = rawurldecode($_SERVER['argv'][1]);
                $aParams = array();
                parse_str($params,$_GET);
                
            } else if (!isset($_GET['path'])) {
                die("INVALID: No File Specified");   
            }
            
        }
    }
    
    public static function setupIncludePaths()
    {
        // ** Setup Include Paths **
        $oPaths = self::get('config')->get("includePaths",false);
        if ($oPaths) {
            foreach ($oPaths->path as $path)
            {
                $currentPaths = ini_get('include_path');
                ini_set('include_path',$currentPaths . PATH_SEPARATOR . $path);
            }
        }
        
    }
    
    public static function setup()
    {
        
        self::setupIncludePaths();
        // ** Setup the test runner **
        $runnerType = 'Blerby_TestRunner_Runner_' . self::get('config')->get("runner/type");
        $runner = Blerby_TestRunner_ServiceLocator::get($runnerType,
                                        new Blerby_TestRunner_Config(self::get('config')->get("runner")));
        self::set('runner', $runner);
        
        
        // ** Add File(s) to test runner **
        $path = (isset($_GET['path'])) ?
                 $_GET['path']         :
                 false;

        // ** Force file only runs **        
        if (is_file($path)) {
            self::get('runner')->setFile($path);
            return true;   
        }
        return false;
    }
    
    /**
     * Add a path to blacklisting
     * 
     * @param string $type
     * @param string $path
     * @return void
     */
    public static function addBlacklistPath($type, $path)
    {
        
        // ** Ensure both type and path are strings **
        if (!is_string($type) || !is_string($path)) {
            return;
        }
        
        
        // ** Make sure we setup aBlacklistPath[$type] first **
        if (!isset(self::$instance->aBlacklistPath[$type])) {
            
            // ** Setup empty blacklist array for this type **
            self::$instance->aBlacklistPath[$type] = array();
        }
        
        // ** Push the new $path onto the blacklist **
        self::$instance->aBlacklistPath[$type][] = $path;   
    }
    
    /**
     * Check if a path is blacklisted
     * 
     * @param string $path
     * @return bool
     */
    public static function isBlacklistedPath($type, $path)
    {


        // ** Default to false when blacklisting type doesnt exist **
        if ( !is_string($path)  ||
             !is_string($type)  || 
             !isset(self::$instance->aBlacklistPath[$type]))
        {
            return false;
        }

        // ** Check all the blacklisted paths for this type **        
        foreach (self::$instance->aBlacklistPath[$type] as $v)
        {
            // ** Error avoidance **
            if (!is_string($v)) {
                continue;   
            }
            
            $pos = stripos($path, $v); 
            
            // ** Do a string match to determine blacklisting **
            if ($pos === false) {
                continue;
            } else {
                return true;   
            }
        }
        
        // ** Default to not blacklisted **
        return false;
    }
    
    public static function set($key, $val)
    {
        self::$instance->aRes[$key] = $val;
    }
    
    public static function get($path,$default = false)
    {
        $path = explode("/",$path);
        
        $loc = self::$instance->aRes; 
        foreach ($path as $s)
        {
            if (is_object($loc) && isset($loc->$s)) {
                $loc = $loc->$s;   
            } else if (is_array($loc) && isset($loc[$s])) {
                $loc = $loc[$s];
            } else {
                return $default;   
            }
        }
        return $loc;
    }
    
    public static function setupPaths()
    {
           
    }
}