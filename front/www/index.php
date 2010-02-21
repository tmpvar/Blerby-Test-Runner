<?php $btrUrlPath = $_SERVER['REQUEST_URI']; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
    <head>
        <script type="text/javasrcipt">
            var BTR = null;
        </script>
        <script type="text/javascript" src="<?php echo $btrUrlPath; ?>Script/jquery.js"></script>
        <script type="text/javascript" src="<?php echo $btrUrlPath; ?>Script/jquery.timer.js"></script>
        <script type="text/javascript" src="<?php echo $btrUrlPath; ?>Script/jquery.treeview.js"></script>
        <script type="text/javascript" src="<?php echo $btrUrlPath; ?>Script/jquery.btr.js"></script>
        <script type="text/javascript" src="<?php echo $btrUrlPath; ?>Script/jquery.highlightFade.js"></script>

        <style type="text/css" media="screen">
            @import url("<?php echo $btrUrlPath; ?>Style/main.css");

            /* These are here for the relative path to the image dir */
            .filetree span.folder { background: url(Images/treeview/folder.gif) left center no-repeat; }
            .filetree li.expandable span.folder { background: url(Images/treeview/folder-closed.gif) left center no-repeat; }
            .filetree span.file { background: url(Images/treeview/file.gif) left center no-repeat; }
            li a.reset {
                padding-left: 22px;
                background:url(Images/arrow_refresh.gif) left center no-repeat;
            }

        </style>

        <script type="text/javascript">

            var btrUrlPath = '<?php echo $btrUrlPath; ?>';
            var aCleanPaths = new Array();
            <?php
                error_reporting(E_ALL);

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

                    // ** If there is a glob path specified,  **
                    // ** clean from before the first known glob char **
                    $path = (string)$scannerConf->get("options/path");
                    foreach (array("{","*","?") as $char)
                    {
                        $pos = strpos($path,$char);

                        if ($pos !== false) {
                            $path = substr($path,0,$pos-1);
                            break;
                        }
                    }

                    // ** Add the path to the javascript aCleanPaths array **
                    $realPath = realpath($path);
                    if($realPath) {
                        echo "aCleanPaths.push('" . addcslashes(Blerby_TestRunner_Util::cleanPath($realPath),'\\') . "');\n";
                    }

                    // *** Instantiate Scanner ***
                    $scannerType = $scannerConf->get("type","File");
                    $scannerType = 'Blerby_TestRunner_Scanner_' . $scannerType;
                    $scanner = Blerby_TestRunner_ServiceLocator::get($scannerType,$scannerConf);
                    $scanner->cleanCache();

                    echo "\nvar scanTimeout = " . $scanner->getScanTimeout() . ";";
                }


            ?>
            var BTR = null
            $(document).ready(
                function()
                {
                    BTR = new jQuery.btr({detectTimeout:scanTimeout});
                    BTR.start();
                }
            );
        </script>
    </head>
    <body>
        <div class='popup'>
            <ul id="testMenu" class="filetree treeview"></ul>
        </div>
        <div id="controls">
                <a onclick="jQuery.btr.menu({runner: BTR})" href="javascript:void(0);">
                    <img src="<?php echo $btrUrlPath; ?>Images/blerbytestrunner.gif" alt="Blerby Test Runner" />
                </a>
                <div class="control">
                    <span id="percentSuccess">0</span>% Success
                </div>
                <div class="control">
                    <span style="color:#009900;" id="totalPassed">0</span> Passed
                </div>

                <div class="control">
                    <span style="color:#FF9A03;" id="totalFailed">0</span> Failed
                </div>
                <div class="control">
                    <span style="color:#CC0000" id="totalError">0</span> Error
                </div>
                <div class="control">
                    <span style="color:#000000" id="totalSkipped">0</span> Skipped
                </div>
                <div class="control">
                    <span style="color:#2B5FA9;" id="totalTests">0</span> Tests
                </div>
                <div id="currentFileDisplay">Idle..</div>
        </div>

        <div style="clear:both"></div>

        <div style="display:none" id="resultTemplate" class="box result">
            <div class="line">Line: ???</div><pre class="message">asdf</pre>
        </div>

        <div style="display:none" id="fileTemplate" class="box result">
            <div class="leftBox">
                <img class="icon" src="<?php echo $btrUrlPath; ?>Images/error.gif" />
            </div>
            <div class="rightBox">
                <div class="titleLine">
                    <span class="title"></span>
                </div>

            </div>
            <div class="resultContainer rightBox"></div>
            <div style="clear:both"></div>
        </div>
        <div id="fileContainer"></div>
    </body>
</html>