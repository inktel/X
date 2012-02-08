<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Did
 *
 * @author fgonzalez
 */
class Did extends \ActiveRecord\Model{
    //put your code here
    
    static $primary_key  = "did_id";
    static $table_name = "vicidial_inbound_dids";
}

?>
