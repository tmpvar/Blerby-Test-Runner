<?php
/**
  Copyright 2006-2010, Elijah Insua
  BSD Licensed (see: LICENCE.txt)
*/

/**
 * @author     Elijah Insua
 * @package    Blerby_TestRunner
 * @subpackage  Blerby_TestRunner_Status
 * @version    #0.6#
 */

/**
 * Test Result Status  definitions
 * 
 * @author Elijah Insua
 * @version #0.6#
 * @package Blerby_TestRunner
 */
class Blerby_TestRunner_Status
{
    /**
     * Passing tests
     * 
     * @var string
     */
    const PASS    = "pass";

    /**
     * Test Failure
     * 
     * @var string
     */
    const FAIL    = "fail";

    /**
     * Errored
     * 
     * @var string
     */
    const ERROR   = "error";

    /**
     * Invalid
     * 
     * @var string
     */
    const INVALID = "invalid"; 
}
