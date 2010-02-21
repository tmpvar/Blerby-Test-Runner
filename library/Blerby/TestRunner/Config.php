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
    
    Author:   Elijah Insua (Feb 9, 2008)
    Filename: Config.php
*/

/**
 * @author     Elijah Insua
 * @package    Blerby_TestRunner
 * @subpackage  Blerby_TestRunner_Config
 * @version    #0.6#
 */

require_once BTR_LIB_PATH . "/Status.php";

/**
 * Configuration handler
 * 
 * @author     Elijah Insua
 * @package    Blerby_TestRunner
 * @version    #0.6#
 */
class Blerby_TestRunner_Config
{
    /**
     * SimpleXMLElement Storage
     * 
     * @var SimpleXMLElement
     */
    private $config;
    
    /**
     * Base Configuration Path
     * 
     * @var string
     */
    private $baseConfigPath;
    
    /** 
     * Magic sleep function to avoid breaking during serialization
     * 
     * @return array
     */
    public function __sleep()
    {
        if (is_object($this->config) &&
            get_class($this->config) == 'SimpleXMLElement')
        {
            return array ('aConfig'=>$this->config->asXML(),
                           'baseConfigPath'=>$this->baseConfigPath);   
        }
        return array();
    }
    
    /**
     * Magic wakeup function to recreate configuration data
     */
    public function __wakeup()
    {
        if ($this->config) {
            $this->config = new SimpleXMLElement($this->config);   
        }   
    }

    /**
     * Constructor
     * 
     * @param mixed $path
     */    
    public function __construct($path)
    {

        // ** Is this a SimpleXMLElement or path 
        if (is_object($path) && get_class($path) == 'SimpleXMLElement') { 
            $this->config = $path;
        } else {
            if (is_file($path)) {
                try {
                    $cnf = file_get_contents($path);
                    $this->config = new SimpleXMLElement($cnf);
                } catch (Exception $e){
                    echo "Could not load config file: '$path' <br />";
                    echo "Reason: " . $e->getMessage();   
                }
            } else {
                echo "Could not load config file: '$path' file not found <br />";
            }
        } 
    }
    
    /**
     * Access config via path 
     * 
     * Example: get("config/testPath")
     * 
     * @param string $path
     * @param string $default
     * @return SimpleXMLElement
     */
    public function get($path, $default = null)
    {
        $aPath = explode("/", $this->baseConfigPath . $path);
        
        // ** Loop down the specified path **/        
        $currentPath = $this->config;
        foreach ($aPath as $i)
        {
            // ** Attempt to navigate into the child **
            if (isset($currentPath->$i)) {
                $currentPath = $currentPath->$i;
            
            // ** Navigation Failed **
            } else {
                return $default;
            }
        }
        
        if (is_string($currentPath)) {
            return trim($currentPath);
        }
        
        return $currentPath;  
    }
}