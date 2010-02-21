Blerby Test Runner
==================
Introduction
------------
The blerby test runner is a fairly configurable system, and will become mostly configurable in the following releases up to 1.0. Due to its requirement on being properly configured, it is difficult to explain the differences between the processes while maintaining various contexts without generalizing some of the terms. The following sections are brief descriptions on the basic workflow that the test runner follows.
Configuration

<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="437" height="315" id="viddler_7b7052a5"><param name="movie" value="http://www.viddler.com/player/7b7052a5/" /><param name="allowScriptAccess" value="always" /><param name="allowFullScreen" value="true" /><embed src="http://www.viddler.com/player/7b7052a5/" width="437" height="315" type="application/x-shockwave-flash" allowScriptAccess="always" allowFullScreen="true" name="viddler_7b7052a5"></embed></object>

[ the sound is a bit low, but this should give you a basic gist as to what this thing does ]

-------------
Configuration is the heart of blerby test runner. It controls how tests are detected, when they are detected, what tests are allowed to be run, how to run the tests, how tests are connected to source code, and last but not least how file updates are tracked. Due to the highly flexible nature of the configuration it would take too much room on this page to properly describe every aspect and combination of allowed configuration. The following is a list of resources in which you will find helpful in getting your project under testing.
Web Front
--------
![btr menu][1]

The BTR menu is a simple way to manually run tests, clear all server sided caches, or jump back to the documentation page on this site. When navigating the "Available Tests" you may notice that each item in the tree is a link. This is because you can run the tests below that point by simply clicking on the item. All associated tests will be added to the end of the current test queue. After the tests are run the results will be merged with previous results and displayed.

Runner results are displayed in the following form:

  - % success (calculated from passes/total tests)
  - Total Tests (total tests encountered before fatal errors in files)
  - \# of passes - number of items not in the following categories
  - \# of errors - fatal errors / warnings / debug messages
  - \# of failures - assert failures
  - \# of skipped - number of encountered test cases marked skipped

**Note:** the term *encountered* is used due to the nature in which fatal errors stop execution. The runner can only detect tests that have been run.
Detection
---------
![btr detect process][2]

In order for Blerby test runner's javascript to know what tests to run, a detection process must take place.&nbsp; Depending on the configuration the detector will return results every execution or only when there are changes. Detection can also be completed manually using the BTR popup menu in the upper left hand of the runner page. The workflow is as follows:

 1. User/AJAX Heartbeat requests detection scan for a list of available/changed files.
 2. The detection front Initializes the BTR environment.
 3. Init is then used to create scanners via the Service Locator.
 4. Scanners are then executed sequentially, having their results merged.
 5. Results are returned to the user in standard JSON format.
 6. Javascript then processes the results and acts accordingly.
Running
-------
![btr run process][3]

The main workhorse of the blerby test runner is the running mechanism. In order to achieve some of the nicities mentioned in the descriptions of this project, it was realized early on that a sort of sandbox / php virtual machine was going to be required. The sandbox allows the test runner to properly run and report on files that contain fatal errors or do not exist at all! The following describes the workflow of the runner process.

   1. User/AJAX Runner timer runs file(s) using a get request to the sandbox.
   2. The sandbox intializes and reads in configuration.
   3. The sandbox spawns off php CLI instance per test.
   4. Each file is ran in its own container and the results sent back to sandbox.
   5. Unknown messages are translated into error/debug messages.
   6. Results are converted into JSON and sent across the wire to the user's browser.
   7. The user's browser updates the collective summary display.


  [1]: http://www.blerby.com/Images/image/btr-menu.gif
  [2]: http://www.blerby.com/Images/image/btr--detect-process.gif
  [3]: http://www.blerby.com/Images/image/btr--run-process.gif
