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

    Author:   Elijah Insua (Feb 11, 2008)
    Filename: PHPUnit.php
    Package:  package_declaration
*/

/**
 * @license    ##LICENSE_URL##
 * @author     Elijah Insua
 * @package    Blerby_TestRunner
 * @subpackage  Blerby_TestRunner_Runner
 * @version    #0.6#
 */

require_once BTR_LIB_PATH . "/Runner.php";

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/Framework/IncompleteTestError.php';
require_once 'PHPUnit/Framework/TestCase.php';
require_once "PHPUnit/Framework/TestResult.php";
require_once 'PHPUnit/Framework/TestSuite.php';
require_once 'PHPUnit/Runner/Version.php';
require_once 'PHPUnit/TextUI/TestRunner.php';
require_once 'PHPUnit/Util/Filter.php';

/**
 *
 * @author Elijah Insua
 * @version #0.6#
 * @package Blerby_TestRunner_Runner
 */
class Blerby_TestRunner_Runner_PHPUnit extends Blerby_TestRunner_Runner
{

    /**
     * Test suite to run
     *
     * @var PHPUnit_Framework_TestSuite
     */
    private $testSuite;

    /**
     * Array of Blerby_TestRunner_Reporter's
     *
     * @var array
     */
    protected $aReporters = array();

    /**
     * Constructor
     *
     * @param Blerby_TestRunner_Config
     */
    public function __construct(Blerby_TestRunner_Config $config)
	{
        parent::__construct($config);

        $this->testSuite = new PHPUnit_Framework_TestSuite();
        foreach ($this->config->get("reporters",array())->reporter as $reporter)
        {
            $type = "Blerby_TestRunner_Reporter_" . $reporter->type;
            $oRep = Blerby_TestRunner_ServiceLocator::get($type, new Blerby_TestRunner_Config($reporter));
            $this->aReporters[(string)$reporter->type] = $oRep;
        }

	}

    /**
     * Create and send a Blerby_TestRunner_Result to the attached reporters
     *
     * @param array $params
     */
    public function notifyReporters($params)
    {
        require_once BTR_LIB_PATH . "/Result.php";

        // ** Create a new test result **
        $r = new Blerby_TestRunner_Result();
        foreach ($params as $k=>$i)
        {
            $r->set($k, $i);
        }

        // ** Notify the reporters **
        foreach ($this->aReporters as $reporter)
        {
            $reporter->addResult($params['file'], $r);
        }
    }

    /**
     * Add a file to be tested
     *
     * @param string $path
     * @return bool
     */
    public function setFile($path)
    {
        $this->aFiles = $path;
        return true;
    }

    /**
     * Execute associated tests
     *
     * @return array
     */
    public function process()
    {
        $result = new PHPUnit_Framework_TestResult();

        // TODO: make this work for more than 1 file
        $this->aDependencies[$this->aFiles] = get_included_files();
        $this->testSuite->addTestFile($this->aFiles);
        /*foreach ($this->aFiles as $file)
        {

            $this->testSuite->addTestFile($file);
        }*/

        $ret = array();

        $this->testSuite->run($result,false);

        $ret['failureCount'] = $result->failureCount();
        $ret['errorCount']   = $result->errorCount();
        $ret['skippedCount'] = $result->skippedCount();
        $ret['count']        = $result->count();

        $failures = $result->failures();
        $deadFiles = array();

        if ($failures)
        {
            foreach ($failures as $fail)
            {

                $params = array();
                $params              = $fail->thrownException()->getLocation();
                $params['file']      = Blerby_TestRunner_Util::cleanPath($this->aFiles);
                $params['status']    = Blerby_TestRunner_Status::FAIL;
                $params['message']   = $this->_getMessage($fail);
                $params['count']     = 1;

                $this->notifyReporters($params);
                //unset($this->aFiles[$params['file']]);
            }

        }

        $errors = $result->errors();
        $deadFiles = array();

        if ($errors)
        {
            foreach ($errors as $error)
            {
                $params = array();
                $params['line']      = $error->thrownException()->getLine();
                $params['file']      = Blerby_TestRunner_Util::cleanPath($this->aFiles);
                $params['status']    = Blerby_TestRunner_Status::FAIL;
                $params['message']   = $this->_getMessage($error);
                $params['count']     = 1;

                $this->notifyReporters($params);
                //unset($this->aFiles[$params['file']]);
            }

        }

        return $ret;
    }

    private function _calculateLocation($file, $ex)
    {


    }

    /**
     * Abstraction to uniformly get a message from a thrown Exception
     *
     * @param Exception $e
     * @return string
     */
    private function _getMessage($e)
    {
        if ($e->isFailure()) {
            // ** Test if thrown exception is populated **
            if (($ex = $e->thrownException())) {
                if (method_exists($ex,'getDescription')) {
                    $msg = $e->thrownException()->getDescription();
                }
            }
            return htmlentities($msg);
        }
    }
}