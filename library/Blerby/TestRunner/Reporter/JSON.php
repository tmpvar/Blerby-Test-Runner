<?php
/**
  Copyright 2006-2010, Elijah Insua
  BSD Licensed (see: LICENCE.txt)
*/

/**
 * @license    ##LICENSE_URL##
 * @author     Elijah Insua
 * @package    Blerby_TestRunner    
 * @subpackage  Blerby_TestRunner_Reporter
 * @version    #0.6#
 */

require_once BTR_LIB_PATH . "/Reporter.php";

/**
 * Report test results via JSON
 * 
 * @author Elijah Insua
 * @version #0.6#
 * @package Blerby_TestRunner_Reporter
 */
class Blerby_TestRunner_Reporter_JSON extends Blerby_TestRunner_Reporter
{
	
    /**
     * Construct a json reporter
     * 
     * @param Blerby_TestRunner_Config
     */
    public function __construct(Blerby_TestRunner_Config $config)
	{
        parent::__construct($config);
	}

    /**
     * Convert the aResults object into a JSON message
     * 
     * @return string
     */
    public function toString()
    {
        
        $res = array();
        foreach ($this->aResults as $aResult)
        {
            foreach ($aResult as $result)
            {
                $res[] = $result->getAll();
            }    
        }

        return serialize($res);
    }    
}
