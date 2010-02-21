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
    Filename: Scanner.php
*/

/**
 * @author     Elijah Insua
 * @package    Blerby_TestRunner
 * @subpackage  Blerby_TestRunner_Scanner
 * @version    #0.6#
 */

/**
 * Scan for tests to run
 *
 * @author Elijah Insua
 * @version #0.6#
 * @package Blerby_TestRunner
 */

class Blerby_TestRunner_Scanner
{
    /**
     * Configuration object
     *
     * @var Blerby_TestRunner_Config
     */
    protected $config;

    /**
     * Cache Path
     *
     * @var string
     */
    protected $cachePath;

    /**
     * Cache File
     *
     * @var string
     */
    protected $cacheFile;

    /**
     * Previous cache storage
     *
     * @var array
     */
    protected $aOldCache = array();

    /**
     * File / Directory Filters
     *
     * @var array
     */
    private $aFilters = array();

    /**
     * File / Directory Filters
     *
     * @var array
     */
    protected $aConnectors = array();

    /**
     * Array of changes
     *
     * @var array
     */
    protected $aChanges = array();

    /**
     * Constructor
     *
     * @param Blerby_TestRunner_Config $config
     */
    public function __construct(Blerby_TestRunner_Config $config)
    {
        $this->config = $config;

        $this->cachePath = $this->config->get("options/cache/path");
        $this->cacheFile  = $this->config->get("options/name","scanner");
        $this->cacheFile .= ".cache";
        $this->aCache = $this->load();

        // ** Save the old cache **
        $this->aOldCache = (is_array($this->aCache)) ?
                            $this->aCache            :
                            array();

        // ** Load Filters **
        $this->loadFilters();

        // ** Load Connectors **
        $this->loadConnectors();
    }

    /**
     * attempt to load a cache
     *
     */
    protected function load()
    {
        return array();
    }

    public function getScanTimeout()
    {
        return 3000;
    }

    /**
     * Load configured filters
     *
     * @return bool
     */
    public function loadFilters($filters = null)
    {

        if (!($filters instanceof SimpleXMLElement)) {
            $filters = $this->config->get("filters");
        }

        if ($filters instanceof SimpleXMLElement) {
            foreach ($filters->filter as $k=>$filter)
            {
                // ** Instantiate Filter **
                $type = "Blerby_TestRunner_Filter_" . $filter->type;
                $this->aFilters[] = Blerby_TestRunner_ServiceLocator::get($type,new Blerby_TestRunner_Config($filter));
            }

        }
        return true;
    }

    /**
     * Load configured connectors
     *
     * @return true
     */
    private function loadConnectors()
    {
        $conns = $this->config->get("connectors");
        if ($conns) {
            foreach ($conns as $conn)
            {
                // ** Instantiate Filter **
                $type = "Blerby_TestRunner_Connector_" . $conn->connector->type;
                $this->aConnectors[] = Blerby_TestRunner_ServiceLocator::get($type, new Blerby_TestRunner_Config($conn->connector));
            }
        }
        return true;
    }

    /**
     * Get Changes
     *
     * @return array
     */
    public function getChanges()
    {
        return (array)$this->aChanges;
    }

    /**
     * Test via filters
     *
     * @param string $path
     * @return bool
     */
    protected function passesFilters($path)
    {
        foreach ($this->aFilters as $filter)
        {
            if (!$filter->test($path)) {
                return false;
            }
        }

        // ** All Filters Passed **
        return true;
    }

    /**
     * Test via Connector
     *
     * @param string $path
     * @return bool
     */
    protected function requiresConnection($path)
    {
        $path = Blerby_TestRunner_Util::cleanPath($path);
        //$path = str_replace($this->config->get("options/path"), "",$path);

        foreach ($this->aConnectors as $connector)
        {
            if (is_object($connector) &&
                $connector->process($path, $this->config->get("options/path")))
            {
                return true;
            }
        }

        // ** Don't load file **
        return false;
    }

    /**
     * Clean the scanner's cache
     *
     * @return void
     */
    public function cleanCache()
    {
        foreach ($this->aConnectors as $connector)
        {
            if (is_object($connector))
            {
                $connector->cleanCache();
            }
        }
    }

    /**
     * Scan the provided path
     *
     * @param string $path
     * @return bool
     */
    public function scan($path)
    {
        $ret = false;
        if ($this->passesFilters($path) && $this->requiresConnection($path)) {
            $ret = true;
        }

        return $ret;
    }
}
