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
    
    Author:   Elijah Insua (Feb 10, 2008)
    Filename: Extension.php
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