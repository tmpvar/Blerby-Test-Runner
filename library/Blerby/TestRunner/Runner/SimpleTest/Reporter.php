<?php
/**
  Copyright 2006-2010, Elijah Insua
  BSD Licensed (see: LICENCE.txt)
*/

//TODO: Determine if this is in the scope of LGPL

require_once "simpletest/unit_tester.php";
require_once "simpletest/reporter.php";
require_once "simpletest/scorer.php";

/**
 *
 * @package btr
 */
class Blerby_TestRunner_Runner_SimpleTest_Reporter extends SimpleReporter
{

    /**
     * Private storage of failures
     *
     * @var array
     */
    private $failures = array();

    /**
     * Private storage of errors
     *
     * @var array
     */
    private $errors = array();

    /**
     * Number of skipped tests
     *
     * @var int
     *
     */
    private $skipped  = 0;

    /**
     * Number of test cases encountered
     *
     * @var int
     */
    public $_size = 0;

    /**
     * Current file
     *
     * @var string
     */
    private $currentFile = 0;

    /**
     *    Starts the display with no results in.
     *
     *    @access public
     */
    function __construct() {
        $this->SimpleReporter();
    }


    /**
     *    Prints the message for skipping tests.
     *    @param string $message    Text of skip condition.
     *    @access public
     */
    public function paintSkip($message) {
        $this->skipped++;
    }

    /**
     *    Paints the start of a group test.
     *    @param string $test_name     Name of test or other label.
     *    @param integer $size         Number of test cases starting.
     *    @access public
     */
    function paintGroupStart($test_name, $size) {

        $this->currentFile = $test_name;
    }

    /**
     * Get the number of skipped tests
     *
     * @return int
     */
    public function getSkippedCount()
    {
        return (int)$this->skipped;
    }

    /**
     * Misuse simpletest to track some stats
     *
     *  + Track how many test cases we have encountered
     *  + Track the current file we are in
     *  + Add a test to the test stack
     *
     * @return void
     */
    function paintCaseStart($test_name) {
        $this->_size++;
        $this->_test_stack[] = $test_name;
        $this->currentTest = $test_name;

    }

    /**
     *    Paints the test failure with a breadcrumbs
     *    trail of the nesting test suites below the
     *    top level test.
     *    @param string $message    Failure message displayed in
     *                              the context of the other tests.
     *    @access public
     */
    function paintFail($message) {

        if (!isset($this->failures[$this->getCurrentFile()])) {
            $this->failures[$this->getCurrentFile()] = array();
        }

        $this->failures[$this->getCurrentFile()][] = $message;
    }

    /**
     * Get the current file in which tests are being run
     *
     * @return string
     */
    function getCurrentFile()
    {
        return $this->currentFile;
    }

    /**
     *    Paints a PHP exception.
     *    @param Exception $exception        Exception to display.
     *    @access public
     */
    function paintException($exception) {
        $this->errors[] = $exception;
    }

    /**
     *    Paints a PHP error.
     *    @param string $message        Message is ignored.
     *    @access public
     */
    function paintError($message) {

        if (!isset($this->errors[$this->getCurrentFile()])) {
            $this->errors[$this->getCurrentFile()] = array();
        }

        $this->errors[$this->getCurrentFile()][] = $message;
    }

    /**
     * Get the number of failed tests
     *
     * @return int
     */
    function getFailCount()
    {
        return count($this->failures);
    }

    /**
     * Get the number of exceptions
     *
     * @return int
     */
    function getExceptionCount()
    {
        return count($this->errors);
    }

    /**
     * Get the total number of test cases ran
     *
     * @return int
     */
    function getTestCaseCount()
    {
        return $this->_size;
    }

    /**
     * Override! dont do anything, like paint footers or whatever.
     */
    function paintCaseEnd($test_name) { }

    /**
     * Return the failure array
     *
     * @return array
     */
    public function failures()
    {
        return $this->failures;
    }

    /**
     * Return the error array
     *
     * @return array
     */
    public function errors()
    {
        return $this->errors;
    }


}
