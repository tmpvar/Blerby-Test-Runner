<?php
/**
  Copyright 2006-2010, Elijah Insua
  BSD Licensed (see: LICENCE.txt)
*/

/**
 * @author     Elijah Insua
 * @package    Blerby_TestRunner    
 * @subpackage  Blerby_TestRunner
 * @version    #0.6#
 */

/**
 * Service locator for blerby test runner
 * 
 * @author Elijah Insua
 * @version #0.6#
 * @package Blerby_TestRunner
 */
class Blerby_TestRunner_ServiceLocator
{
    
    /**
     * Dynamically locate and instantiate an object from a string
     * 
     * @param string $type
     * @param Blerby_TestRunner_Config $config
     * @return Object
     */
    static public function get($type, Blerby_TestRunner_Config $config)
    {
        $path = str_replace("_","/",$type);
        $path = str_replace("Blerby/TestRunner/","",$path);
        $path = BTR_LIB_PATH . "/" . $path . ".php";

        // ** Get Resource File **    
        if (is_file($path)) {
            require_once $path;
            
            // ** Get Instance **
            $className = $type;
            if (class_exists($className)) {
                return new $className($config);   
            }
        }
    }
}
