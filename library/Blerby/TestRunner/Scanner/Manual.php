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
    
    Author:   Elijah Insua (Feb 9, 2008)
    Filename: File.php
*/

/**
 * @author     Elijah Insua
 * @package    Blerby_TestRunner
 * @subpackage  Blerby_TestRunner_Scanner
 * @version    #0.6#
 */

require_once BTR_LIB_PATH . "/Scanner.php";
require_once BTR_LIB_PATH . "/Config.php";

/**
 * Bunk Scanner, does nothing really.
 * 
 * @author Elijah Insua
 * @version #0.6#
 * @package Blerby_TestRunner
 */
class Blerby_TestRunner_Scanner_Manual extends Blerby_TestRunner_Scanner
{

    /**
     * Constructor
     * 
     * @param Blerby_TestRunner_Config $blerby
     */
    public function __construct(Blerby_TestRunner_Config $config)
	{
        parent::__construct($config);
    }
    
    public function getScanTimeout()
    {
        return 0;   
    }    
    
    /**
     * Store modtime information
     * 
     * @return void
     */
    public function save() {}
    
    /**
     * attempt to load a cache
     * 
     */
    protected function load() {}

    /**
     * Get Deleted Files
     * 
     * @return array
     */
    public function getDeletions() {}

    /**
     * Add a file change
     * 
     * @param string $file
     * @return bool
     */
    public function addFile($file) { }
    
    /**
     * Scan directory for files
     * 
     * @param string $path
     * @param bool $recursive
     */
    public function scan($path, $recursive = true) {}
}