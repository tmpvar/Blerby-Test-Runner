/*
 * Blerby TestRunner
 *
 * Manages display/events/ajax for running tests
 *
 * Note: this is only a partial jQuery extension (mostly for looks)
 *
 * @author Elijah Insua <tmpvar@gmail.com>
 * @link http://www.blerby.com/project/testrunner/
 * @version #0.6#
 */

jQuery.btr = function(settings)
{
    // ** Currently running a test **
    this.isRunning = false;
    this.pendingRun = false;

    // ** Results **
    this.totalPasses = 0;
    this.totalFailure = 0;
    this.totalError = 0;
    this.totalTests = 0;
    this.totalSkipped = 0;

    // ** Test Results **
    this.oTests = new Object();

    // ** Default Settings **
    this.settings = jQuery.extend(
        {
            detectTimeout         : 2000,
            runnerTimeout         : 1000,
            displayTotalPassed    : "#totalPassed",
            displayTotalFailed    : "#totalFailed",
            displayTotalError     : "#totalError",
            displayTotalTests     : "#totalTests",
            displayTotalSkipped   : "#totalSkipped",
            displayPercentSuccess : "#percentSuccess",
            filesToRun            : 5
        }, settings
    );

    // ** Pending test storage **
    this.aPendingTests = new Array();

    // ** Add a test to the pending queue if not already included **
    this.queueTest = function(file)
    {
        // ** Search existing
        var fileExitsInQueue = false;
        for (i in this.aPendingTests)
        {
            if (this.aPendingTests[i] == file) {
                fileExitsInQueue = true;
            }
        }

        // ** File doesnt exist, so add it **
        if (!fileExitsInQueue && file != undefined) {
            this.aPendingTests.push(file);
        }
    }
    
    // ** Update all status texts **
    this.status = function(text) 
    {
        
        
        text = (jQuery(text).text()) ? jQuery(text).text() : text
                
        document.title = "BTR: " + text;
        jQuery("#currentFileDisplay").html(text);
    }

    // ** Reset BTR's front/backend caches **
    this.reset = function()
    {
        // ** Clear the serverside cache **
        // TODO: implement cache clearing
        var _self = this;
        this.status("Clearing caches...");
        jQuery.ajax({type:"GET",url:btrUrlPath + "sandbox.php",data:{action:'reset'},dataType:"json",
        complete:
            function(xhr, status)
            {
                if (status == 'success') {
                    _self.status("<b style='color:green'>Caches Cleared<b>");
                } else {
                    _self.status("<b style='color:red'>There was a problem clearing the caches</b>");
                }
            }
        });

        // ** Clear the pending tests **
        delete this.aPendingTests;
        this.aPendingTests = new Array();

        // ** Clear existing test results **
        delete this.oTests;
        this.oTests = new Object();

        // ** Reset Display **
        this.calculateStatistics();
    }
    
    // ** Start the execution **
    this.start = function()
    {
        // ** Prepare a variable for the nested closures below **
        var _self = this;

        // ** Clean the caches **
        this.reset();

        // ** Create detection event **
        jQuery(this).bind("doDetect",
            function (e, name, value)
            {
                if (!_self.isRunning && _self.aPendingTests.length < 1) {
                    _self.status("Detecting...");
                }

                $.getJSON(btrUrlPath + "sandbox.php", {action:'detect'},
                    function(data)
                    {
                        if (jQuery(data.aChanges).length > 0) {
                            for(k in data.aChanges)
                            {
                               _self.queueTest(data.aChanges[k]['file']);
                            };
                        }

                        // ** Manage file deletions **
                        if (jQuery(data.aDeletions).length > 0) {
                            for(k in data.aDeletions)
                            {
                                // ** Attempt to locate a pending test with the same filename **
                                for (i in _self.aPendingTests)
                                {
                                    // ** If found, delete from pending tests **
                                    if (_self.aPendingTests[i] == data.aDeletions[k]['file']) {
                                        delete _self.aPendingTests[i];
                                    }
                                }
                            };
                        }
                    }
                );
            }
        )

        // ** Create runner event **
        jQuery(this).bind("doRunner",
            function (e, name, value)
            {
                // ** Only run if we have tests in queue and not running **
                if (jQuery(_self.aPendingTests).length > 0 &&
                    !_self.isRunning)
                {
                    var a = 1;

                    // ** Setup params for ajax request below **
                    var params = new Object();
                    params.action = "run";
                    params.aFiles = "";

                    // ** Wait until we are done to run again **
                    _self.isRunning = true;


                    var fileCount = 0;
                    // ** Include X tests, not overrunning the boundries of aPendingTests **
                    while (a <= _self.settings.filesToRun && jQuery(_self.aPendingTests).length )
                    {
                        // ** Separate with semi-colons **
                        if (a > 1) {
                            params.aFiles = params.aFiles + ";";
                        }

                        // ** Add filename to string **
                        var tmpFile = _self.aPendingTests.shift();
                        params.aFiles = params.aFiles + tmpFile;
                        a++;
                        fileCount++;

                        // ** Notify the display of tests that are being run **
                        var tmpHash = this.getHashByFilename(tmpFile);
                        if (tmpHash != false) {
                            jQuery("#" + tmpHash + " .icon").attr("src","Images/loading.gif");
                        }

                    }

                    // ** Ensure that the calculated file string is not empty **
                    if (params.aFiles != "") {

                        // ** Update display -> this is actually kind of annoying? **
                        _self.status("Running " + fileCount + " of " + (jQuery(_self.aPendingTests).length + fileCount) + " queued files.");

                        // ** Run calculated tests **
                        jQuery.getJSON(btrUrlPath + "sandbox.php", params,
                            function(data)
                            {
                                // ** Convert results into html boxes and such **
                                _self.processRunnerResults(data);
                            }
                        );
                    }
                }
                
                // ** Set the runner to run at a regular interval **
                setTimeout(
                    function()
                        {
                            jQuery(_self).trigger("doRunner");
                        }, this.settings.runnerTimeout
                );

            }
        );

        // ** If automatic scanning is allowed **
        if (this.settings.detectTimeout > 0) {
            
            // ** Kick off a detection process **
            jQuery(this).trigger("doDetect");
            
            // ** Set the detector to run at a regular interval **
            setInterval(
                function()
                    {
                        jQuery(_self).trigger("doDetect");
                    }, this.settings.detectTimeout
            );

            // ** Set the runner to run at a regular interval **
            setTimeout(
                function()
                    {
                        jQuery(_self).trigger("doRunner");
                    }, this.settings.runnerTimeout
            );
        }
    };

    // ** Get a failing file's has by its filename **
    this.getHashByFilename = function(filename)
    {
        for (i in this.oTests)
        {
            if (this.oTests[i]['file'] == filename) {
                return this.oTests[i]['hash'];
            }
        }
        return false;
    }

    // ** Post processing of results **
    this.processRunnerResults = function(data)
    {
        // ** Add file results to our oTests **
        for (k in data)
        {
            // ** Ensure we have a valid result before we add it **
            if (data[k] != undefined && data[k]['hash']) {

                this.addFileResult(data[k]['hash'],data);
            }
        }

        this.calculateStatistics();
    }

    // ** Update the html statistics **
    this.calculateStatistics = function()
    {
        // ** Reset counts **
        this.totalPasses = 0;
        this.totalFailure = 0;
        this.totalError = 0;
        this.totalTests = 0;
        this.totalSkipped = 0;

        // ** Calculate results **
        for(k in this.oTests)
        {
            var currentTest = this.oTests[k];
            this.totalPasses  += currentTest.countPasses();
            this.totalFailure += Number(currentTest.totalFailure);
            this.totalError   += Number(currentTest.totalError);
            this.totalTests   += Number(currentTest.totalTests);
            this.totalSkipped += Number(currentTest.totalSkipped);
        }

        // ** Calculate percentage of success **
        var p = null;
        p = (this.totalPasses && this.totalTests) ?
            (this.totalPasses / this.totalTests) * 100 :
            0;

        // ** Force percentage to be atleast 0% **
        p = (p<0) ? 0 : p;

        // ** Display percentage **
        jQuery(this.settings.displayPercentSuccess).text(Math.round(p));

        // ** Subtract skipped from passes **
        this.totalPasses = Number(this.totalPasses) - Number(this.totalSkipped);

        // ** Ensure total passes never goes negative **
        if (this.totalPasses < 0) {
            this.totalPasses = 0;
        }

        // ** Update the summary amounts **
        jQuery(this.settings.displayTotalPassed).text(this.totalPasses);
        jQuery(this.settings.displayTotalFailed).text(this.totalFailure);
        jQuery(this.settings.displayTotalError).text(this.totalError);
        jQuery(this.settings.displayTotalTests).text(this.totalTests);
        jQuery(this.settings.displayTotalSkipped).text(this.totalSkipped);
        
        this.isRunning = false;
    }

    // ** Add a file result box if appropriate **
    this.addFileResult = function(hash, jsonObj)
    {
        // ** Create a new file entry **
        if (this.oTests[hash] == undefined) {
            this.oTests[hash] = new jQuery.btr.file(hash, this);
        }

        // ** Ensure that file entry exists **
        if (this.oTests[hash] != undefined) {

            // ** Setup the test file **
            var testFile = this.oTests[hash];

            var id = 0;

            testFile.processResults(hash,jsonObj[hash]);
            return;
            if (jQuery(jsonObj[hash]['results']).length > 0)
            {
                for (result in jsonObj[hash]['results'])
                {
                    if (result.action != undefined &&
                        result.action != "delete")
                    {
                        // ** Single files can have multiple results, dont overwrite the old **
                        id++;

                        // ** Process the incomming results **
                        testFile.processResults(id,jsonObj[hash]);
                    }
                }
           }
        }
    }
}

// ** Blerby Test Runner File Object **
jQuery.btr.file = function(hash, runner)
{
    this.hash     = hash;
    this.runner   = runner;
    this.file     = "";
    this.oResults = new Object();

    this.hasRun   = true;

    this.totalTests  = 0;
    this.totalPasses = 0;
    this.totalSkipped = 0;
    this.totalFailure = 0;
    this.totalError = 0;
    this.totalInvalid = 0;

    this.box = null;

    // ** Kill off fixed result boxen **
    this.cleanResults = function(o, jsonObj)
    {
        // loop through and kill off the results
        // add results to the box

        var a = 0;
        var resultContainer = jQuery(o).children();
        for (a=0; a<resultContainer.length; a++)
        {
            var found = false;
            for(k in jsonObj.results)
            {
                if (jsonObj.hash + "_" + jsonObj.results[k]['line'] == resultContainer[a]['id']) {
                    found = true;
                }
            }

            // delete expired results
            if (!found) {
                this.deleteResult(resultContainer[a]);
                delete this.oResults[resultContainer[a]['id']]
            }
        }
    }

    // ** Remove a file result box **
    this.deleteResult = function(jqueryObj)
    {
        jQuery(jqueryObj).highlightFade({color:'#ffffc8',speed:200})
        jQuery(jqueryObj).fadeOut(200,
            function ()
            {
                jQuery(this).remove();
            }
        );
        delete this.oResults[jqueryObj.id];
    }

    this.processResults = function(hash, jsonObj)
    {
        // ** Track incomming results from the server **
        this.totalTests   = Number(jsonObj.info.count);
        this.totalFailure = Number(jsonObj.info.failureCount);
        this.totalError   = Number(jsonObj.info.errorCount);
        this.totalSkipped = Number(jsonObj.info.skippedCount);

        // ** Track what file this is **
        this.file         = jsonObj.info.file;

        // ** Track whether or not we found failures **
        var found = false

        // ** We have results, which means there was a problem **
        if (jQuery(jsonObj.results).length > 0) {

            for(k in jsonObj.results)
            {
                if (jQuery(jsonObj.results[k]).length > 0 &&
                    jsonObj.results[k]['status'] != 'pass')
                {
                    // ** Add a result **
                    this.oResults[this.hash + "_" + jsonObj.results[k]['line']] = new jQuery.btr.file.result(this.hash + "_" + jsonObj.results[k]['line'], jsonObj.results[k]);

                    // ** add to end of re-test queue **
                    this.runner.queueTest(jsonObj.results[k]['file']);

                    // ** Found a failure in this result set **
                    found = true;
                }

                var resultContainer = jQuery("#" + this.hash).find(".resultContainer");
                this.cleanResults(resultContainer,jsonObj);

            }
        }

        // ** We found some sort of problem earlier **
        if (found == true && jQuery(this.oResults).length > 0) {
            jQuery("#" + this.hash + " .icon").attr("src","Images/error.gif");
            // add file box if non-existant
            var boxResult = jQuery("#" + this.hash);
            if (!boxResult.length) {
                boxResult = jQuery("#fileTemplate").clone();
                jQuery(boxResult).attr("id",this.hash);

                jQuery("#fileContainer").append(boxResult);
                jQuery(boxResult).find(".title").html(jsonObj['info']['file']);
                jQuery(boxResult).fadeIn(200,
                    function()
                    {
                        jQuery(this).highlightFade({color:'#ffffc8',speed:200})
                    }
                );
            }


            // add results to the box
            resultContainer = jQuery(boxResult).find(".resultContainer");
            for(k in this.oResults)
            {
                // add box
                if (this.oResults[k]['status'] != 'pass') {
                    this.oResults[k].addBox(resultContainer);
                }
            }

        // ** No problems, procede with removing the error box **
        } else {
            this.removeBox();
        }
    };

    // ** Remove results **
    this.remove = function()
    {
        delete this.oResults;

        // ** Remain consistent for future failures **
        this.oResults = new Object();
    };

    // ** Remove file box **
    this.removeBox = function()
    {
        delete this.runner.aPendingTests[this.hash];
        this.remove();

        // ** Remove html box if it exists **
        if (jQuery("#" + this.hash + " .icon").length > 0) {
            jQuery("#" + this.hash + " .icon").attr("src","Images/pass.gif");
            jQuery("#" + this.hash).highlightFade({color:'#ffffc8', complete:
                function()
                {
                    // ** dodges issue where runner timout is less than 700 **
                    jQuery("#" + this.hash + " .icon").removeClass("icon");

                    // ** Fadeout and remove **
                    jQuery(this).fadeOut(700,
                        function()
                        {
                            jQuery(this).remove();
                        }
                    );
                }
            });
        }
    }

   // ** Count the number of passes in this file **
    this.countPasses = function()
    {
        // ** Get the total from the total tests **
        var ret = this.totalTests - (this.totalFailure + this.totalError);
        return Number(ret);
    };
}

// ** File result helper **
jQuery.btr.file.result = function(hash,jsonObj)
{
    this.status       = jsonObj.status;
    this.line         = jsonObj.line;
    this.message      = jsonObj.message;
    this.count        = jsonObj.count;
    this.file         = jsonObj.file;
    this.hash         = hash;
    this.jsonObj      = jsonObj;

    // ** Add a result box **
    this.addBox = function(o)
    {
        // ** Display Failure **
        var boxResult = jQuery(o).find("#" + this.hash);
        if (!boxResult.length) {
            boxResult = jQuery("#resultTemplate").clone();
            jQuery(boxResult).attr("id",this.hash);

            jQuery(o).append(boxResult)
            jQuery(boxResult).fadeIn(200,
                function()
                {
                    jQuery(this).highlightFade({color:'#ffffc8',speed:200})
                }
            );
        }
        var actualFile = "\nFile: " + this.jsonObj.file + "<br />";
        var myLine = (this.line != undefined) ? "Line: " + this.line : '';
        jQuery(boxResult).find(".line").html(actualFile + myLine);

        jQuery(boxResult).find(".message").html(this.message);
    }

    // ** Remove a result box **
    this.removeBox = function()
    {
        var boxResult = jQuery(o).find("#" + this.hash);
        $(boxResult).remove();
    }
}

// ** BTR Test menu builder/helper **
jQuery.btr.menu = function(settings)
{
    var params = {action:'available'}

    this.oStructure = new Object();

    // ** Default Settings **
    this.settings = jQuery.extend(
        {
            id : "testMenu",
            runnerName: 'BTR',
            runner: null
        }, settings
    );

    var _self = this;

    jQuery.getJSON(btrUrlPath + "sandbox.php", params,
        function(data)
        {


            var menu = _self.createMenu(_self.settings.id, data);

            jQuery('*').bind('click',_self.handleClick);

            jQuery("#" + _self.settings.id).html(menu);


            jQuery("#" + _self.settings.id).treeview({
                collapsed: true,
                animated: "fast",
                prerendered: false,
                persist: "location",
                unique:false
            });

            jQuery("div.popup").show('fast');
            jQuery("#" + _self.settings.id).find("a").remove('click').click(
                function (event)
                {
                    jQuery.btr.queueTests(this,_self.settings.runner);
                    event.stopPropagation()
                    window.location = jQuery(this).attr('href');
                    return false;
                }
            );


        }
    );

    // ** Handle click events, for hiding the popup **
    this.handleClick = function(e,v,a)
    {
        var tmpNode = this;
        var found = false;

        // ** Calculate whether the click happened inside of the popup **
        if (tmpNode.tagName != "HTML" && tmpNode.tagName != "BODY") {
            while ((tmpNode = jQuery(tmpNode).parent())) {

                if (jQuery(tmpNode).hasClass('popup')) {
                    found = true;
                    break;
                } else if (tmpNode[0]['tagName'] == "BODY") {
                    break;
                }
            }
        }

        if (found == false) {
            _self.destroyMenu(_self);

        // ** Do not propigate click if we know its inside of the popup **
        } else {
            e.stopPropagation();
            return false;
        }

    }

    // ** Remove the menu, unbind popup hiding events **
    this.destroyMenu = function(source)
    {
        jQuery(".popup").hide().find("ul").html("");
        jQuery('*').unbind('click',source.handleClick);
    }

    // ** Convert a json object into a better structure **
    this.jsonToStructure = function(o, path, full, partial)
    {
        if (!path) { return o; }

        if (o[path] == undefined) {
            o[path] = new Object();

            o[path]['type'] =  'dir';
            if (path.indexOf('.') > -1) {
                o[path]['type'] = 'file';
                o[path]['full'] = full;
            }

            o[path]['partial'] = partial;
            o[path]['name'] = path;
        }

        return o[path];
    }

    // ** Convert an object structure into html recursively **
    this.structureToHtml = function(o)
    {
        var ret = "";

        // ** List folders first **
        for (elem in o)
        {
            // ** Skip over unsed params **
            if (elem == 'type' || elem == 'name' || elem == 'partial') { continue; }

            // ** Create link for queuing tests **

            // ** If directory go recurse **
            if (o[elem]['type'] == 'dir') {
                ret += "<li><span class='folder'><a id='path:" + o[elem]['partial'] + "' href='#path:" + o[elem]['partial'] + "'>" + elem + "</a></span>";

                ret += "<ul class='expandable'>";
                ret += this.structureToHtml(o[elem]);
                ret += "</ul></li>";
            }
        }

        // ** Add Files **
        for (elem in o)
        {
            // ** Skip over unsed params **
            if (elem == 'type' || elem == 'name' || elem == 'partial') { continue; }

            // ** File **
            if (o[elem]['type'] != 'dir') {
                ret += "<li><span class='file'><a id='path:" + o[elem]['partial'] + "' href='#path:" + o[elem]['partial'] + "' rel='" + o[elem]['full'] + "'>" + elem + "</a></span></li>";
            }
        }
        return ret;
    }

    // ** Create the btr menu from a json object **
    this.createMenu = function(id, jsonObj)
    {
        // ** Create base link + ability to run all tests **
        var menu = "<li><span class='folder'><a id='path:/'href='#path:/' onmousedown='jQuery.btr.queueTests(this,";
        menu += this.settings.runnerName + ")'>Available Tests</a></span><ul>";

        for (i in jsonObj)
        {
            // ** Shorten the dir to a readable format (long paths especially) **
            var currentFile = jsonObj[i];
            for (clean in aCleanPaths)
            {
                currentFile = currentFile.replace(aCleanPaths[clean],'');
            }

            // ** Remove prepended slash if exists **
            currentFile = currentFile.replace(/^\//,"");

            // ** Split the directory **
            var aCurrent = currentFile.split("/");

            // ** Create a nested object structure **
            var oCurrent = this.oStructure;
            var rebuilt = "";
            var first = true;
            for (path in aCurrent)
            {
                if (first) {
                    first = false;
                } else {
                    rebuilt += "/";
                }

                rebuilt += aCurrent[path];

                oCurrent = this.jsonToStructure(oCurrent,aCurrent[path], jsonObj[i], rebuilt);
            }
        }

        // ** Convert nested objects into nested ul/li list **
        menu += this.structureToHtml(this.oStructure);


        menu += "</ul></li><li><hr /><a class='reset' href='javascript:void(0);' onmousedown='" + this.settings.runnerName + ".reset()'> Reset Caches</a></li></ul>";
        menu += "<li><hr /><a href='http://www.blerby.com/project/testrunner/' target='_blank'>BTR Homepage</a></li></ul>";
        return menu;
    }
}

// ** Queue tests into BTR using the dom as a guide**
jQuery.btr.queueTests = function(e, ourBTR)
{

    var aAnchor = jQuery(e).parent().parent().find("a");

    aAnchor.each(
        function (k,tmpAnchor)
        {
            ourBTR.pendingRun = (k+1>=aAnchor.length) ? true : false;

            // ** Locate the rel attr **
            var rel = jQuery(tmpAnchor).attr('rel');
            
            // ** Ensure that we only add files to the test queue **
            if (rel != "" && rel.indexOf('.') > -1) {
                ourBTR.queueTest(rel);
            }
        }
    );
}
