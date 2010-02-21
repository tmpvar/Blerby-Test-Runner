<?php
/**
  Copyright 2006-2010, Elijah Insua
  BSD Licensed (see: LICENCE.txt)
*/

/**
 * @author     Elijah Insua
 * @package    Blerby_TestRunner
 * @subpackage  Blerby_TestRunner_Config
 * @version    #0.6#
 */

require_once BTR_LIB_PATH . "/Status.php";

/**
 * Configuration handler
 * 
 * @author     Elijah Insua
 * @package    Blerby_TestRunner
 * @version    #0.6#
 */
class Blerby_TestRunner_Config
{
    /**
     * SimpleXMLElement Storage
     * 
     * @var SimpleXMLElement
     */
    private $config;
    
    /**
     * Base Configuration Path
     * 
     * @var string
     */
    private $baseConfigPath;
    
    /** 
     * Magic sleep function to avoid breaking during serialization
     * 
     * @return array
     */
    public function __sleep()
    {
        if (is_object($this->config) &&
            get_class($this->config) == 'SimpleXMLElement')
        {
            return array ('aConfig'=>$this->config->asXML(),
                           'baseConfigPath'=>$this->baseConfigPath);   
        }
        return array();
    }
    
    /**
     * Magic wakeup function to recreate configuration data
     */
    public function __wakeup()
    {
        if ($this->config) {
            $this->config = new SimpleXMLElement($this->config);   
        }   
    }

    /**
     * Constructor
     * 
     * @param mixed $path
     */    
    public function __construct($path)
    {

        // ** Is this a SimpleXMLElement or path 
        if (is_object($path) && get_class($path) == 'SimpleXMLElement') { 
            $this->config = $path;
        } else {
            if (is_file($path)) {
                try {
                    $cnf = file_get_contents($path);
                    $this->config = new SimpleXMLElement($cnf);
                } catch (Exception $e){
                    echo "Could not load config file: '$path' <br />";
                    echo "Reason: " . $e->getMessage();   
                }
            } else {
                echo "Could not load config file: '$path' file not found <br />";
            }
        } 
    }
    
    /**
     * Access config via path 
     * 
     * Example: get("config/testPath")
     * 
     * @param string $path
     * @param string $default
     * @return SimpleXMLElement
     */
    public function get($path, $default = null)
    {
        $aPath = explode("/", $this->baseConfigPath . $path);
        
        // ** Loop down the specified path **/        
        $currentPath = $this->config;
        foreach ($aPath as $i)
        {
            // ** Attempt to navigate into the child **
            if (isset($currentPath->$i)) {
                $currentPath = $currentPath->$i;
            
            // ** Navigation Failed **
            } else {
                return $default;
            }
        }
        
        if (is_string($currentPath)) {
            return trim($currentPath);
        }
        
        return $currentPath;  
    }
}
