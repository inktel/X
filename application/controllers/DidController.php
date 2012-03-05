<?php

use X\Engine;

class DidController extends Zend_Controller_Action
{

    
    public function init()
    {
        /* Initialize action controller here */
        
    }

    public function indexAction()
    {

    }           

    public function GetDelimiter($file)
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

    private function IsDnis($column)
    {
        $colscount = count($column);
        for ($i = 1; $i < $colscount; $i++)
        {
            if  (!is_numeric($column[$i]) || strlen($column[$i]) > 10 || strlen($column[$i]) < 5) 
            {
                return false;
            }
        }
        return true;
    }

    private function HasDnis($row)
    {
        $colscount = count($row);
        for ($i = 1; $i < $colscount; $i++)
        {
            if  (is_numeric($row[$i]) && strlen($row[$i]) <= 10 && strlen($row[$i]) >= 5) 
            {
                return true;
            }
        }
        return false;
    }

    public function GetCandidateColumns($parsedFile)
    {
        $filters = array();
        $isDnisDefaltValueSet = false;
        $isDescDefaltValueSet = false;
        $numberCount = 0;
        $descriptionCount = 0;
        foreach ($parsedFile as $key => $value) 
        {
            
            if ($this->IsDnis($value))
            { 
                $filters["Number"][$numberCount] = array("ColumnName" => $key,"IsDefault" => 0);
                
                if (substr_count(strtolower($key), "dnis") > 0 && !$isDnisDefaltValueSet)
                {
                    $isDnisDefaltValueSet = true;
                    $filters["Number"][$numberCount]["IsDefault"] = 1;
                }
                $numberCount++;
            }
            else
            {
                $filters["Description"][$descriptionCount] = array("ColumnName" => $key,"IsDefault" => 0);
                if (substr_count(strtolower($key), "description") > 0 && !$isDescDefaltValueSet)
                {
                    $isDescDefaltValueSet = true;
                    $filters["Description"][$descriptionCount]["IsDefault"] = 1;
                }
                $descriptionCount++;
            }
        }
        if (!$isDnisDefaltValueSet)
        {
             $filters["Number"][0]["IsDefault"] = 1;
        }
        if (!$isDescDefaltValueSet)
        {
             $filters["Description"][0]["IsDefault"] = 1;
        }
        return $filters;
    }

    public function IsHeader($row)
    {
          $num = count($row);
          $blankCols = 0;
          for ($i=0; $i < $num; $i++)
          {
              if (is_numeric($row[$i]))
              {
                  return false;
              } 
              if ($row[$i] == '') $blankCols++;
          }
          
          return ($blankCols/$num) < 0.25;
    }

    public function LoadFile($file)
    {
        $csvFile = array();
        $colsHeaders = array();
        if (($handle = fopen($file, "r")) !== FALSE) 
        { 
            $delimeter = $this->GetDelimiter($file);
            $row = 0;
            $headerSelected = false;
            while (($cols = fgetcsv($handle, 1000, $delimeter)) !== FALSE)
            {
                $num = count($cols);

                if ( !$headerSelected && $this->IsHeader($cols))
                {
                    for ($c=0; $c < $num; $c++) 
                    {
                        $colsHeaders[$c] = $cols[$c];
                    }
                    $headerSelected = true;
                }
                else if ($this->HasDnis($cols))
                {
                    for ($c=0; $c < $num; $c++) 
                    {
                        $csvFile[$colsHeaders[$c]][$row] = trim($cols[$c]," "); 
                    }
                }
                $row ++;
            }
            fclose($handle);
        }
        return $csvFile;  
    }

    public function xls_to_csv_files($xlsfile, $precalculate = true, $use_states = array (  0 => 'visible',))
    {
        $objReader = new PHPExcel_Reader_Excel2007;
        $objReader->setReadDataOnly(true);
        $objPHPExcel = $objReader->load($xlsfile);
        // save csv files to same path as the xls.
        $dir = dirname($xlsfile);
        $base = basename($xlsfile,".xls");
        $fileName = array();
        foreach ($objReader->getWorksheetIterator() as $i=>$worksheet) {
            if(in_array($wstate,$use_states)){
                $wtitle = $worksheet->getTitle();
                $objReader->setLoadSheetsOnly($wtitle);

                $objWriter = new PHPExcel_Writer_CSV($objPHPExcel);
                $objWriter->setPreCalculateFormulas($precalculate);
                $writer->save($dir.'/'.$base.'-'.$wtitle.'.csv');
                $fileName[]= $dir.'/'.$base.'-'.$wtitle.'.csv';
            }
        }
        return $fileName;
        
    }

    public function uploadAction()
    {
        // action body
        if ($_FILES["inFile"]["error"] > 0)
        {
            echo "Error: " . $_FILES["inFile"]["error"] . "<br />";
        }
        else
        {
            $colsFilters = array();
            $file = $_FILES["inFile"]["tmp_name"];
            
            $fileParts = explode(".", $file);
            $lengthCount = count($fileParts);
            if ($lengthCount > 0 && ($fileParts[$lengthCount-1] == "xls" || $fileParts[$lengthCount-1] == "xlsx"))
            {
                $csvFile = array();
                $csvFile = $this->xls_to_csv_files($file);
                $this->view->TestValue = $csvFile[0];
                $rFile =  $this->LoadFile($csvFile[0]);
               
            }
            else
            {
                $rFile = $this->LoadFile($file);
               
            }
            
            $colsFilters = $this->GetCandidateColumns($rFile);
            $this->view->colsFilter =  $colsFilters;

            $_SESSION['FileManager']['File'] = $rFile;
            $this->view->fileContent =  $rFile;
        }
        
       
    }
    
    

    public function manageAction()
    {
        // get values from the session
        $numberCol = $_POST['number'];
        $descriptionCol = $_POST['description'];
        $fileContent = array();
        if (isset( $_SESSION['FileManager']['File'])){
                $fileContent =  $_SESSION['FileManager']['File'];
        }
       
        $didObjs = array();
        
        //build did entities
        $countRows = count($fileContent[$numberCol]);
        for ($i = 1; $i < $countRows; $i++)
        {
            $did = new Did(array('did_pattern' => $fileContent[$numberCol][$i], 
                                 'did_description'=> $fileContent[$descriptionCol][$i]));
            $didObjs[] = $did;     
        }
        $_SESSION['FileManager']['Dids'] = $didObjs;
    }
    
    /*
     * @assert("EXTEN","spot") == 1
     * @assert("AGENT") == 0
     * @assert() == 82
     * @assert("EXTEN") == 1
     */
    public function getdidsAction()
    {
        $dids =  $_SESSION['FileManager']['Dids'];

        $result = array();
       
        $request = $this->getRequest();
        $route = $request->getParam('route');
        $filter = $request->getParam('filter');
        
        if (!isset($route)) $route = 'EXTEN';
        if (!isset($filter)) $filter = '';
        
        $tempResult = array_filter($dids, function ($item) use ($filter,$route) {
            if (($item->did_route == $route) &&
                (($filter == "") || stripos($item->did_pattern, $filter) !== false || stripos($item->did_description, $filter) !== false)) {
                return true;
            }
            return false;
        });
        
        foreach ($tempResult as $key => $value)
        {
              $result[] = array('key' => $key,
                                'autoSaved' => $value->autoSaved,
                                'did_pattern' => $value->did_pattern, 
                                'did_description' => $value->did_description, 
                                'did_active' => $value->did_active);
            
        }
        
       $this->_helper->json($result);
       
    }
    
    public function adddidAction()
    {
        $request = $this->getRequest();
        $number = $request->getParam('number');
        $description = $request->getParam('description');
        if (!isset($description)) $description = '';
        $dids =  $_SESSION['FileManager']['Dids'];
        
        // todo Check if the number already exist if so add it if it is not send failure.
        // todo add number to the DB
    
        // add number to memory
        $dids[] = new Did(array('did_pattern' => $number, 
                                 'did_description'=> $description));
        $_SESSION['FileManager']['Dids'] = $dids;
        
        $result =  array("result" => "success");
        $this->_helper->json($result);
    }
    
    public function deletedidAction()
    {
        $request = $this->getRequest();
        $ids = $request->getParam('ids');
        $idList = explode(',',$ids);
        $dids =  $_SESSION['FileManager']['Dids'];
        foreach ($idList as $value)
        {
            unset($dids[$value]);
        }
        $result = array();
        $result =  array("result" => "success");
        $_SESSION['FileManager']['Dids'] = $dids;
        
        $this->_helper->json($result);
    }
    
    public function autosaveAction()
    {
        $request = $this->getRequest();
        $id = $request->getParam('id');
        $fieldName = $request->getParam('fieldname');
        $value = $request->getParam('value');
       
        $dids =  $_SESSION['FileManager']['Dids'];
        
        $dids[$id]->$fieldName = $value;
        $dids[$id]->autoSaved = true;
        
        $_SESSION['FileManager']['Dids'] = $dids;
        
        $this->_helper->json($dids[$id]->autoSaved);
    }

}





