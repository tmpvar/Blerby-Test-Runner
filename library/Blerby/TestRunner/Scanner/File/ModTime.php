<?php
/**
  Copyright 2006-2010, Elijah Insua
  BSD Licensed (see: LICENCE.txt)
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
