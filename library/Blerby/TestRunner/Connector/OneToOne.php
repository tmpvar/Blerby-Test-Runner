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
