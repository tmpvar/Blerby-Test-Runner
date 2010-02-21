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
 * File scanner w/o caching
 *
 * @author Elijah Insua
 * @version #0.6#
 * @package Blerby_TestRunner
 */
class Blerby_TestRunner_Scanner_File extends Blerby_TestRunner_Scanner
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

    /**
     * Store modtime information
     *
     * @return void
     */
    public function save()
    {

        // ** Save off the connector caches **
        foreach ($this->aConnectors as $oConn)
        {
            $oConn->save();
        }
    }

    /**
     * attempt to load a cache
     *
     */
    protected function load()
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
     * Get Deleted Files
     *
     * @return array
     */
    public function getDeletions()
    {
        $ret = array();

        // ** Compare the cache with the filesystem **
        foreach ($this->aOldCache as $file=>$modTime)
        {
            $file = Blerby_TestRunner_Util::cleanPath($file);

            if (!is_file($file)) {
                $ret[$file] = $file;
                unset($this->aCache[$file]);
            }
        }
        $this->save();
        return $ret;
    }

    /**
     * Recursively scan a path
     *
     * @param string $path
     */
    private function _scanPath($path, $recurse = false)
    {

        if (strpos($path, '..') !== false) {
            $path = realpath(BTR_FRONT_PATH . DIRECTORY_SEPARATOR . $path);
        }

        $path = (string)$path;
        $globbed = glob($path,GLOB_BRACE);

        $aPath = ($globbed) ? $globbed : array($path);
        foreach ($aPath as $scanPath)
        {
            $dh = opendir($scanPath);
            if ($dh) {
                while (false !== ($file = readdir($dh))) {

                    // ** Skip .svn and others **
                    if (substr($file,0,1) == '.') { continue; }

                    $c = $scanPath . '/' . $file;

                    // ** Ensure filters are satisfied with the file **
                    if (!$this->passesFilters($c)) {
                        continue;
                    }

                    // ** Is the file another directory? **
                    if (is_dir($c)) {
                        if ($recurse) {
                            $tmpNode = $this->_scanPath($c, $recurse);
                        }

                    // ** It's a file **
                    } else {
                        $this->addFile($c);
                    }
                }
                closedir($dh);
            }
        }
    }

    /**
     * Add a file change
     *
     * @param string $file
     * @return bool
     */
    public function addFile($file)
    {
        $file = Blerby_TestRunner_Util::cleanPath($file);
        // ** Store file in cache for later 'deletion' calcualtions **
        $this->aCache[$file] = filemtime($file);
        $this->aChanges[$file] = $file;
        return true;
    }

    /**
     * Scan directory for files
     *
     * @param string $path
     * @param bool $recursive
     */
    public function scan($path, $recursive = true)
    {

        // ** Scan recursively **
        $this->_scanPath($path, $recursive);

        // ** Save new cache **
        $this->save();

        return true;
    }
}