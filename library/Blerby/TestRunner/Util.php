<?php
/**
  Copyright 2006-2010, Elijah Insua
  BSD Licensed (see: LICENCE.txt)
*/

/**
 * @license    ##LICENSE_URL##
 * @author     Elijah Insua
 * @package    
 * @subpackage 
 * @version    #0.6#
 */

/**
 * Utility class
 * @author Elijah Insua
 * @version #0.6#
 * @package Blerby
 */
class Blerby_TestRunner_Util
{
    /**
     * Path cleaner for communication between PHP and AJAX
     * 
     * @param string $path
     */
    static public function cleanPath($path)
    {
        //$path = realpath($path);
        $path = str_replace("\\","/",$path);
        
        
        do
        {
            $old = $path;
            $path = str_replace("//","/",$path);
        } while ($old !== $path);
        
        return $path;
    }
}
