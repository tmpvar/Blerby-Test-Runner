<?php
 /*******************************************\

	Blerby Components (c) 2008 Sproutware

                 ##LICENSE##

    Author:   Elijah Insua (Feb 9, 2008)
    Filename: detect.php
    Package:  package_declaration

 \*******************************************/

/**
 * @license    ##LICENSE_URL##
 * @author     Elijah Insua
 * @package
 * @subpackage
 * @version    #0.6#
 */

/**
 *
 * @author Elijah Insua
 * @version #0.6#
 * @package Blerby
 */
$start = microtime(false);

error_reporting(E_ALL | E_STRICT | E_WARNING);

// ** Process Config **
require_once "Blerby/TestRunner/Init.php";
Blerby_TestRunner_Init::start();

define('BTR_FRONT_PATH',realpath(dirname(__FILE__) . "/../"));

// ** Setup The Config **
Blerby_TestRunner_Init::set("config",new Blerby_TestRunner_Config(BTR_FRONT_PATH . "/config/config.xml"));
Blerby_TestRunner_Init::setupIncludePaths();

// ** Changes Store **
$aChanges = array();

// ** Deletion Store **
$aDeletions = array();

// ** Locate the global filters if available **
$globalFilters = Blerby_TestRunner_Init::get("config")->get("scanners/filters");

// ** Create Scanners **
foreach (Blerby_TestRunner_Init::get("config")->get("scanners")->children() as $s)
{

    // ** Only configure scanners in the scanners section **
    if ($s->getName() != 'scanner') {
        continue;
    }

    // *** Get scanner configuration ***
    $scannerConf = new Blerby_TestRunner_Config($s);

    // *** Default to File scanner ***
    $scannerType = $scannerConf->get("type","File");

    // *** Instantiate Scanner ***
    $scannerType = 'Blerby_TestRunner_Scanner_' . $scannerType;
    $scanner = Blerby_TestRunner_ServiceLocator::get($scannerType,$scannerConf);

    // ** Load global scanner filters if available **
    if ($globalFilters->children()) {
        $scanner->loadFilters($globalFilters);
    }

    // *** Scan for Changes ***
    $scanner->scan($scannerConf->get("options/path"));

    // *** Merge found changes ***
    $aChanges = array_merge($aChanges,$scanner->getChanges());

    // *** Merge found deletions ***
    $aDeletions = array_merge($aDeletions,$scanner->getDeletions());

}


// ** Prepare Changes **
$hashTable = array();

$ourPath = Blerby_TestRunner_Util::cleanPath(realpath(dirname(__FILE__) . "/../"));

foreach ($aChanges as $k=>$i)
{
    // ** Ensure we are not detecting files in btr's front **
    if ($i && $ourPath && strpos($i,$ourPath) === false) {
        $hashTable[md5($k)] = array("file"=>$i,
                                    "hash"=>md5($k));
    }
}
$send = array();
$send['aChanges'] = $hashTable;

// ** Prepare Deletions **
$hashTable = array();
foreach ($aDeletions as $k=>$i)
{
    $hashTable[md5($k)] = array("file"=>$i,
                                "hash"=>md5($k));
}

$send['aDeletions'] = $hashTable;
$end = microtime(false);
$send['debug']['executionTime'] = $end-$start;


return $send;