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
    Filename: OneToOne.php
*/

/**
 * @author     Elijah Insua
 * @package    Blerby_TestRunner    
 * @subpackage  Blerby_TestRunner_Connector
 * @version    #0.6#
 */

require_once BTR_LIB_PATH . "/Connector.php";

/**
 * Scan for files connected directly via a 1:1 mapping based on directory
 * structure
 * 
 * @author Elijah Insua
 * @version #0.6#
 * @package Blerby_TestRunner
 */
class Blerby_TestRunner_Connector_OneToOne extends Blerby_TestRunner_Connector
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
        $this->cachePath = $this->config->get("options/cache/path");
        $this->cacheFile  = $this->config->get("type");
        $this->cacheFile .= ".cache";
        $this->aCache = $this->load();      
	}
    
    /**
     * Store modtime information
     * 
     * @return void
     */
    public function save()
    {
        $file = $this->cachePath . "/" . $this->cacheFile;
        file_put_contents($file, serialize($this->aCache));
    }
    
    /**
     * attempt to load a cache
     * 
     */
    private function load()
    {
        $file = $this->cachePath . "/" . $this->cacheFile;

        if (is_file($file)) {
            $fcontents = file_get_contents($file);
            if (!empty($fcontents)) {
                return unserialize($fcontents);   
            }
        }
    }
    
    /**
     * Has path expired since last cache?
     * 
     * @param string $path
     * @return bool
     */
    private function hasExpired($path)
    {
        $path = Blerby_TestRunner_Util::cleanPath($path);
        return (!is_file($path) ||
                 !isset($this->aCache[$path]) ||
                 $this->aCache[$path] < filemtime($path)) ?
                 true:
                 false;
    }    
    
    /**
     * Default connector
     * 
     * @param path
     * @return
     */
    public function process($path, $baseTestPath = "")
    {
        $p = Blerby_TestRunner_Util::cleanPath($this->config->get("options/pathToCode") . "/" . $path);
        $e = $this->hasExpired($p);

        if (is_file($p) && $e) {
            $this->aCache[$p] = filemtime($p);
        }
        return $e;
    }
}