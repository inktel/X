<?php

namespace X\Engine;
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of FileManager
 *
 * @author fgonzalez
 */
class FileManager {
    //put your code here
    
    
    static public function GetDelimiter($file)
    {
        $probableDelimiters = array (","," ","|",";");
        $stringFile = file_get_contents($file);
        $maxDelimiterCount = 0;
        $delimiterSelected = $probableDelimiters[0];

        foreach ($probableDelimiters as $probableDelimiter){
            $probableDelimiterCount = substr_count($stringFile, $probableDelimiter);
                if ($probableDelimiterCount > $maxDelimiterCount) {
                        $maxDelimiterCount = $probableDelimiterCount;
                                $delimiterSelected = $probableDelimiter;
                } 
        }
        return $delimiterSelected;
    }
    
    
}

?>
