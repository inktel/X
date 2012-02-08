<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
  /**
	* init jquery view helper, enable jquery, jqueryui, jquery ui css
	*/
	 
	protected function _initJquery() 
        {
	 
            $this->bootstrap('view');
            $view = $this->getResource('view'); //get the view object

            //add the jquery view helper path into your project
            $view->addHelperPath("ZendX/JQuery/View/Helper", "ZendX_JQuery_View_Helper");

            //jquery lib includes here (default loads from google CDN)
            $view->jQuery()->enable()//enable jquery ; ->setCdnSsl(true) if need to load from ssl location
                ->setVersion('1.5')//jQuery version, automatically 1.5 = 1.5.latest
                ->setUiVersion('1.8')//jQuery UI version, automatically 1.8 = 1.8.latest
                ->addStylesheet('https://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/ui-lightness/jquery-ui.css')//add the css
                ->uiEnable();//enable ui
	 
	}
        
        protected function _initExcelReader()
        {
            require_once "excel_reader2.php";
        }
        
        
        protected function _initExcelReaderExtended()
        {
            require_once "PHPExcel.php";
        }
        
        protected function _initActiveRecord() {
            include_once('ActiveRecord/ActiveRecord.php');
            //10.11.20.46 pilot
            ActiveRecord\Config::initialize(function($cfg){
                $cfg->set_model_directory(APPLICATION_PATH.'/models');
                $cfg->set_connections(array(
                    'development' => 'mysql://root:asterisk@vicisql01/asterisk',
                    'production' => 'mysql://root:asterisk@vicisql01/asterisk',
                ));
                $cfg->set_default_connection(APPLICATION_ENV);
            });
        }
        
        protected function _initZendSessionNamespaces(){
            $this->bootstrap('session');
            Zend_Session::start();
        }
}

