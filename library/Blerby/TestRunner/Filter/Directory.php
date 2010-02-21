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

require_once BTR_LIB_PATH . "/Filter.php";

/**
 * Filter based on directory name 
 * 
 * @author Elijah Insua
 * @version #0.6#
 * @package Blerby
 */
class Blerby_TestRunner_Filter_Directory extends Blerby_TestRunner_Filter
{
	
    /**
     * Constructor
     * 
     * @param Blerby_TestRunner_Config $config
     */
    public function __construct(Blerby_TestRunner_Config $config)
	{
        parent::__construct($config);
        
        $res = $this->config->get("options/directories",false);
        $this->aCache = $this->configToArray($res);

	}
    
    /**
     * Test the incomming path against a configured blacklist
     *
     * @param string $path
     * @return bool
     */
    public function test($in)
    {
        // ** Ensure this is a file **
        //if (is_dir($in)) {

            // ** Compare via blacklist **
            foreach ($this->aCache as $path)
            {
                // ** Found Match **
                if ($path && stripos($in, $path) !== false) {
                    return false;   
                }   
            }
        //}
        
        // ** Must be a file or not on the blacklist **
        return true;
    }
}
