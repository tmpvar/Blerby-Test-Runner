<?php
/**
  Copyright 2006-2010, Elijah Insua
  BSD Licensed (see: LICENCE.txt)
*/

/**
 * @author     Elijah Insua
 * @package    Blerby_TestRunner    
 * @subpackage  Blerby_TestRunner_Filter
 * @version    #0.6#
 */

/**
 * Filter Blerby_TestRunner_Scanner results
 * 
 * @author Elijah Insua
 * @version #0.6#
 * @package Blerby_TestRunner
 */
class Blerby_TestRunner_Filter
{
    
    /**
     * Loaded Config Instance
     * 
     * @var Blerby_TestRunner_Config
     */
    protected $config;
    
    /**
     * Caching for black/white list
     * 
     * @var array
     */
    protected $aCache = array();
    
    /**
     * Constructor 
     * 
     * @var Blerby_TestRunner_Config
     */
    public function __construct(Blerby_TestRunner_Config $config)
    {
       $this->config = $config;
    }
    
    /**
     * Turn a configuration level into an array, accepts comma delimited as well
     * 
     * @param mixed $item
     * @return array
     */
    public function configToArray($item)
    {
        // ** Has children nodes **
        if (is_object($item) && count($item->children()) > 0) {
            
            if (count($item->children()) > 1) {
               
                $ret = array();
                foreach ($item->children() as $child)
                {
                   $ret[] = (string)$child;
                }
                return $ret;
                
            } else {
                return array((string)$item->children());   
            }
        // ** Treat like a text node **   
        } else if (trim((string)$item)) {
            $ret = explode(",",trim((string)$item));
            return (is_array($ret)) ? $ret : array($ret);
        }
        
        return array();
    }
    
    /**
     * Test incomming path
     * 
     * @param string $path
     * @return bool
     */
    public function test($path)
    {
        return true;   
    }
}
