<?php
/**
  Copyright 2006-2010, Elijah Insua
  BSD Licensed (see: LICENCE.txt)
*/

/**
 * @license    ##LICENSE_URL##
 * @author     Elijah Insua
 * @package    Blerby_TestRunner
 * @subpackage  Blerby_TestRunner_Filter
 * @version    #0.6#
 */

require_once BTR_LIB_PATH . "/Filter.php";

/**
 * Extension based file filter
 * 
 * @author Elijah Insua
 * @version #0.6#
 * @package Blerby_TestRunner_Filter
 */
class Blerby_TestRunner_Filter_Extension extends Blerby_TestRunner_Filter
{
	
    /**
     * Constructor
     * 
     * @param Blerby_TestRunner_Config $config
     */
    public function __construct(Blerby_TestRunner_Config $config)
	{
        parent::__construct($config);
        
        $res = $this->config->get("options/extensions",false);
        $this->aCache = $this->configToArray($res);
	}
    
    /** 
     * Test if the incomming file is in the configured whitelist
     * 
     * @param string $path
     * @return bool
     */
    public function test($path)
    {
        
        // ** Ensure this is a file **
        if (is_file($path)) {

            // ** Compare via whitelist **
            foreach ($this->aCache as $ext)
            {
                // ** Found Match **
                
                $fileExt = substr($path,-(strlen($ext)));
                if ($fileExt === $ext) {
                    return true;   
                }   
            }
            // ** No Matching Extension **
            return false;
        }
        
        // ** Must be a directory **
        return true;
    }
}
