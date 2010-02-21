<?php
 /*******************************************\

	Blerby Components (c) 2008 Sproutware

                 ##LICENSE##

    Author:   Elijah Insua (Feb 18, 2008)
    Filename: sandbox.php
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
require_once "Blerby/TestRunner/Init.php";
set_time_limit(0);

Blerby_TestRunner_Init::doIncludes();
if (!isset($_GET['action'])) {
   die("INVALID: No Action Specified");
}



/**
 * Safe include + result tracking
 * This allows BTR to run tests / detect changes without having to worry about
 * fatal errors, unhandled exceptions, etc.
 *
 * @param string $file
 * @param array  $params
 * @return
 */
function execute($file,$params)
{
    ob_start();

        $aParams = array();

        $path = "";
        foreach ($params as $k=>$i)
        {
            if ($k == "path") {
                $path = $i = Blerby_TestRunner_Util::cleanPath($i);
            }
            $aParams[] = $k . "=" . $i;
        }

        $strParams = rawurlencode(implode("&",$aParams));

        passthru("php ./$file $strParams");


        // get result
        $res = ob_get_contents();
        ob_end_clean();

        $ret = array("info"=>$params);
        if (strlen($res)>=6) {
            // ** Exp
            if (substr($res,0,6) == "VALID:") {
                $res = substr($res,6);
                $ret = unserialize($res);
                $ret['status']  = Blerby_TestRunner_Status::PASS;
            } else if (substr($res,0,8) == "INVALID:") {
                $ret["file"]    = $path;
                $ret['action']  = "delete";
                $ret['results'] = array();
            } else {

                $aRes["file"]    = $path;
                $aRes['message'] = $res;
                $aRes['status']  = Blerby_TestRunner_Status::ERROR;
                $aRes['count']   = 1;

                preg_match("/on line ([0-9]+)/",$res,$matches);

                $aRes['line']    = (isset($matches[1])) ? $matches[1] : '';

                $ret['info']['failureCount'] = 0;
                $ret['info']['errorCount']   = 1;
                $ret['info']['skippedCount'] = 0;
                $ret['info']['count']        = 0;
                $ret['results'] = array(md5($path)=>$aRes);
            }
        }
        $ret['hash'] = md5($path);
        $ret['info']['file'] = $path;

        return $ret;

}

$aResults = array();

// ** Decide where to send the request **
switch ($_GET['action'])
{
    // ** Execute a test **
    case 'run':
        $aFiles = array();
        if (isset($_GET['aFiles'])) {
            $aFiles = explode(";",$_GET['aFiles']);
            unset($_GET['aFiles']);
        }

        $res = array();
        foreach ($aFiles as $file)
        {
            $_GET['path'] = $file;

            // ** Run the current file **
            $res[md5($file)] = execute('runner.php',$_GET);

            // ** Collect results from the execution **
            if (isset($res[md5($file)]['results'])) {

                // ** If we didn't get an array, try to unserialize **
                if (!is_array($res[md5($file)]['results'])) {
                    $res[md5($file)]['results'] = unserialize($res[md5($file)]['results']);
                }

            } else {

                // ** Manually set the results to an empty array **
                $res[md5($file)]['results'] = array();
            }
            Sleep(0);
            // ** Set the filename hash for uniqueness **
            $res[md5($file)]['info']['hash'] = md5($file);
        }

        $aResults = $res;
    break;

    // ** Run detector **
    case 'detect':
        /*$res = execute('detect.php',$_GET);
        echo json_encode($res);*/
        require_once "detect.php";
        $aResults = $send;
    break;

    case 'available':

        // ** Get an instance of the file scanner **
        define('BTR_FRONT_PATH',realpath(dirname(__FILE__) . "/../"));

        // ** Setup The Config **
        Blerby_TestRunner_Init::set("config",new Blerby_TestRunner_Config(BTR_FRONT_PATH . "/config/config.xml"));
        Blerby_TestRunner_Init::setupIncludePaths();

        // ** Changes Store **
        $aChanges = array();

        // ** Deletion Store **
        $aDeletions = array();

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
            $scannerType = $scannerConf->get("type","File");

            // *** Instantiate Scanner (force file to scan everything) ***
            $scannerType = 'Blerby_TestRunner_Scanner_File';
            $scanner = Blerby_TestRunner_ServiceLocator::get($scannerType,$scannerConf);

            // ** Load global scanner filters if available **
            if ($globalFilters->children()) {
                $scanner->loadFilters($globalFilters);
            }

            // *** Scan for Changes ***
            $scanner->scan($scannerConf->get("options/path"));

            // *** Merge found changes ***
            $aChanges = array_merge($aChanges,$scanner->getChanges());
        }

        // ** Return in json format the array (hash=>key) **
        $ret = array();
        foreach ($aChanges as $file)
        {
            $ret[md5($file)] = Blerby_TestRunner_Util::cleanPath($file);
        }

        $aResults = $ret;
    break;


    case 'reset':
        define('BTR_FRONT_PATH',realpath(dirname(__FILE__) . "/../"));

        // ** Process Config **
        require_once "Blerby/TestRunner/Init.php";
        $_GET['path'] = '';
        Blerby_TestRunner_Init::start();

        Blerby_TestRunner_Init::set("config",new Blerby_TestRunner_Config(BTR_FRONT_PATH . "/config/config.xml"));
        Blerby_TestRunner_Init::setupIncludePaths();

        // ** Create Scanners **
        foreach (Blerby_TestRunner_Init::get("config")->get("scanners") as $s)
        {
            $scannerConf = new Blerby_TestRunner_Config($s->scanner);

            // *** Instantiate Scanner ***
            $scannerType = $scannerConf->get("type","File");
            $scannerType = 'Blerby_TestRunner_Scanner_' . $scannerType;
            $scanner = Blerby_TestRunner_ServiceLocator::get($scannerType,$scannerConf);
            $scanner->cleanCache();
        }

        $aResults = array("result"=>"ok");

        $file = (string)Blerby_TestRunner_Init::get("config")->get("dependencyCache/file");
        if (is_file($file)) {

            unlink($file);
        }

    break;

    // ** Invalid **
    default:
        die("INVALID: Invalid Action");
    break;
}

if (!isset($_GET['debug'])) {
    header('Content-type: application/json');
    echo json_encode($aResults);
} else {
    echo "<pre>";
    print_r($aResults);
}