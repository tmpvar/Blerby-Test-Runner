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
    Filename: ModTime.php
*/

/**
 * @author     Elijah Insua
 * @package    Blerby_TestRunner
 * @subpackage  Blerby_TestRunner_Connector
 * @version    #0.6#
 */

require_once BTR_LIB_PATH . "/Scanner/File.php";


/**
 * ModTime File Scanner
 *
 * @author Elijah Insua
 * @version #0.6#
 * @package Blerby_TestRunner_Runner
 */
class Blerby_TestRunner_Scanner_File_ModTime extends Blerby_TestRunner_Scanner_File
{
    /**
     * Constructor
     *
     * @param Blerby_TestRunner_Config
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
    private function hasExpired($path)
    {
        return ($this->aOldCache[$path] < filemtime($path)) ?
                 true:
                 false;
    }

    /**
     * Clean the scanner's cache
     *
     * @return void
     */
    public function cleanCache()
    {
        $file = $this->cachePath . "/" . $this->cacheFile;
        if (is_file($file)) {
            unlink($file);
        }
    }

    public function save()
    {
        parent::save();
        $file = $this->cachePath . "/" . $this->cacheFile;
        file_put_contents($file, serialize($this->aCache));
    }

    /**
     * Compare path to cache
     *
     * @param string $path
     */
    private function isChanged($path)
    {
        $path = Blerby_TestRunner_Util::cleanPath($path);

        if (!isset($this->aOldCache[$path]) ||
            $this->hasExpired($path)     ||
            $this->requiresConnection($path))
        {
            return true;
        }
        return false;
    }

    /**
     * Callback from scan
     *
     * @param string file
     */
    public function addFile($file)
    {
        if ($this->isChanged($file)) {
            return parent::addFile($file);
        }
        return true;
    }

    /**
     * Callback from scan
     *
     * @param string $dir
     * @return true
     */
    public function addDir($dir)
    {
        return true;
    }

    /**
     * Scan for changes in a path
     *
     * @param string path
     * @param bool $recursive
     * @return bool true
     */
    public function scan($path, $recursive = true)
    {
        // ** Scan current path forcing recursive **
        return parent::scan($path,true);
    }
}