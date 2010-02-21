<?php
/*
    Software License Agreement (BSD License)
    
    Blerby Components (c) 2008 Sproutware.
    All rights reserved.
    
    Redistribution and use of this software in source and binary forms, with or
    without modification, are permitted provided that the following conditions
    are met:
    
    * Redistributions of source code must retain the above
      copyright notice, this list of conditions and the
      following disclaimer.
    
    * Redistributions in binary form must reproduce the above
      copyright notice, this list of conditions and the
      following disclaimer in the documentation and/or other
      materials provided with the distribution.
    
    * Neither the name of Sproutware nor the names of its
      contributors may be used to endorse or promote products
      derived from this software without specific prior
      written permission of Sproutware
    
    THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
    AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
    IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
    ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE 
    LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR 
    CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF 
    SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS 
    INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN 
    CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) 
    ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
    POSSIBILITY OF SUCH DAMAGE.
    
    Author:   Elijah Insua (Feb 12, 2008)
    Filename: Result.php
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