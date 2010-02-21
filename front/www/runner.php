<?php
error_reporting(E_ALL | E_STRICT | E_WARNING);
ini_set("memory_limit","64M");
$start = array_sum(explode(' ', microtime()));

error_reporting(E_ALL);
require_once "Blerby/TestRunner/Init.php";

// ** Start Initialization **
Blerby_TestRunner_Init::start();

// ** Include Required Files **
require_once BTR_PATH . "/Config.php";
require_once BTR_PATH . "/Scanner.php";
require_once BTR_PATH . "/ServiceLocator.php";

define('BTR_FRONT_PATH',realpath(dirname(__FILE__) . "/../"));

// ** Setup The Config **
Blerby_TestRunner_Init::set("config",new Blerby_TestRunner_Config(BTR_FRONT_PATH . "/config/config.xml"));

// ** Setup Blerby Test Runner and various
$res = Blerby_TestRunner_Init::setup();

if (!$res) {
    echo "INVALID: File Not Found";
    exit;
}

// *** Execute Test(s) ***

$info = array('count'        =>'0',
              'failureCount' =>'0',
              'errorCount'   =>'1',
              'skippedCount' =>'0');

try {
    $info = Blerby_TestRunner_Init::get('runner')->process();
} catch (Exception $e) {
    Blerby_TestRunner_Init::get('runner')->getReporter('JSON')->setException($e);
}

// TODO: this block of code should be refactored partially into the dependency
//       connector.  It would make life much easier and we wouldnt have a stray
//       config section in the config.xml file.

$dependancyCache = Blerby_TestRunner_Init::get('config')->get("dependencyCache/file",false);

if ($dependancyCache) {

    // ** Calculate dependencies from the last run **
    $aDependencies = Blerby_TestRunner_Init::get('runner')->calculateDependencies();

    // TODO: Make this handle more than 1 test at a time
    $aDeps = current($aDependencies);
    $aRetDeps = array();
    foreach ($aDeps as $k=>$file)
    {
        // ** Don't include the suite files **
        if (Blerby_TestRunner_Init::isBlacklistedPath('dependency',$file)) {
            continue;
        }

        // ** Add path to dependency cache **
        $file = Blerby_TestRunner_Util::cleanPath($file);
        $aRetDeps[] = array("file"=>trim($file),"filemtime"=>filemtime($file));
    }

    // ** Store the evaluated dependency cache **
    $cached = array();

    // ** Merge with existing file if exists **
    if (is_file($dependancyCache)) {
        $cached = unserialize(file_get_contents($dependancyCache));
    }

    // ** Overwrite the old entry for this file **
    $cached[key($aDependencies)] = $aRetDeps;
    file_put_contents($dependancyCache, serialize($cached));
}


$end = array_sum(explode(' ', microtime()));
$ret['debug']['executionTime'] = $end-$start;

$ret['info']         = $info;
$ret['results']      = Blerby_TestRunner_Init::get('runner')->getReporter('JSON')->toString();
//$ret['dependancies'] = $aDependencies;


echo "VALID:" . serialize($ret);
exit;