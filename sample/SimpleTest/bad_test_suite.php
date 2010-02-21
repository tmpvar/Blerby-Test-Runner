<?php
require_once('simpletest/autorun.php');

class BadTestCases extends TestSuite {
    function BadTestCases() {
        $this->TestSuite('Two bad test cases');
        $this->addFile(dirname(__FILE__) . '/support/empty_test_file.php');
    }
}
?>