<?php
/**
  Copyright 2006-2010, Elijah Insua
  BSD Licensed (see: LICENCE.txt)
*/

/**
 * @author     Elijah Insua
 * @package    Blerby_TestRunner    
 * @subpackage  Blerby_TestRunner_Result
 * @version    #0.6#
 */

/**
 * 
 * @author Elijah Insua
 * @version #0.6#
 * @package Blerby_TestRunner_Runner
 */
class Blerby_TestRunner_Result
{
    
    /**
     * Value Array
     * 
     * @var array
     */
    private $aValues;
    
    /**
     * Constructor
     */
	public function __construct()
	{
        $aValues = array();
	}
    
    /**
     * Value Array Getter
     * 
     * @param string $key
     * @param mixed $default 
     * @return mixed or $default
     */
    public function get($key, $default = "")
    {
        return (is_string($key) &&
                  isset($this->aValues[$key])) ?
                  $this->aValues[$key] :
                  $default;
    }
    
    /**
     * Value Array Setter
     * 
     * @param string $key
     * @param mixed $value
     */
    public function set($key, $value)
    {
        $this->aValues[$key] = $value;   
    }
    
    /**
     * Get All Values
     * 
     * @return array
     */
    public function getAll()
    {
        return $this->aValues;   
    }
}
