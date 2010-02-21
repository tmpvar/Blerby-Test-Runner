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
    Filename: Dependancy.php
*/

/**
 * @author     Elijah Insua
 * @package    Blerby_TestRunner
 * @subpackage  Blerby_TestRunner_Connector
 * @version    #0.6#
 */

require_once BTR_LIB_PATH . "/Connector.php";

/**
 * Dependency resolving file scanner
 * 
 * @author Elijah Insua
 * @version #0.6#
 * @package Blerby
 */
class Blerby_TestRunner_Connector_Dependency extends Blerby_TestRunner_Connector
{
	
    /**
     * Cache Path
     * 
     * @var string
     */
    private $cachePath;
    
    /**
     * Cache File
     * 
     * @var string
     */
    private $cacheFile;
    
    /**
     * Cache Array
     * 
     * @var array
     */
    private $aCache;
    
    /**
     * Constructor
     * 
     * @param Blerby_TestRunner_Config $config
     */    
    public function __construct(Blerby_TestRunner_Config $config)
	{
        parent::__construct($config);
	}
    
    /**
     * Has path expired since last cache?
     * 
     * @param string $path
     * @return bool
     */
    private function hasExpired($path, $modtime)
    {
        $path = Blerby_TestRunner_Util::cleanPath($path);
        
        return (!is_file($path) ||
                 $modtime < filemtime($path)) ?
                 true:
                 false;
    }    
    
    /**
     * Clear the caches
     * 
     * @return void
     */
    public function cleanCache()
    {
        $dependancyCache = Blerby_TestRunner_Init::get('config')->get("dependencyCache/file",false);
        
        $ret = false;
        
        // ** Check for configured path to dependency cache **
        if ($dependancyCache) {
        
            $cached = array();
            
            // ** Load cache saved by the runner **
            if (is_file($dependancyCache)) {
                unlink($dependancyCache);
            }
        }
    }    
    
    /**
     * Locate Expired Dependencies.
     * Dependancies  are collected via the test runner
     * 
     * @param string $path
     * @return bool
     */
    public function process($path, $baseTestPath = "")
    {
        $p = str_replace($baseTestPath, "",$path);
        $p = $this->config->get("options/pathToCode") . $p;
        $res = '';
        $dependancyCache = Blerby_TestRunner_Init::get('config')->get("dependencyCache/file",false);
        
        $ret = false;
        
        // ** Check for configured path to dependency cache **
        if ($dependancyCache) {
        
            $cached = array();
            
            // ** Load cache saved by the runner **
            if (is_file($dependancyCache)) {
                $cached = unserialize(file_get_contents($dependancyCache));
            }

            // ** Does this file have cached dependencies? **            
            if (isset($cached[$path])) {

                // ** Attempt to find an expired dependency **
                foreach ($cached[$path] as $key=>$aFile)
                {
                    
                    // ** Ensure this is a valid entry **
                    if (!isset($aFile['file']) || !isset($aFile['filemtime'])) {
                        continue;
                    }
                    
                    // ** Trim the file to avoid blank spaces **
                    $aFile['file'] = trim($aFile['file']);

                    // ** Test whether the file has expired **
                    $e = $this->hasExpired($aFile['file'],$aFile['filemtime']);
                    if ($e) {
                        return true;
                    }
                }
            }
        }
        return $ret;
    }
}