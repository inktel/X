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
    
    function __construct(array $attributes = array(), $guard_attributes = true, $instantiating_via_find = false, $new_record = true) {
        
        parent::__construct($attributes, $guard_attributes, $instantiating_via_find, $new_record);
        
            $this->did_active = "Y";
            $this->did_route = "EXTEN";
            $this->extension = "9998811112";
            $this->exten_context = "default";
            $this->user_unavailable_action = "VOICEMAIL";
            $this->user_route_settings_ingroup = "AGENTDIRECT";
            $this->call_handle_method = "CID";
            $this->agent_search_method = "LB";
            $this->list_id = 999;
            $this->phone_code = "1";
            $this->record_call = "N";
            $this->filter_inbound_number = "DISABLE";
            $this->filter_action = "EXTEN";
            $this->filter_extension = "9998811112";
            $this->filter_exten_context = "default";
            $this->filter_user_unavailable_action = "VOICEMAIL";
            $this->filter_user_route_settings_ingroup = "AGENTDIRECT";
            $this->filter_call_handle_method = "CID";
            $this->filter_agent_search_method = "LB";
            $this->filter_list_id = 999;
            $this->filter_phone_code = "1";
            $this->autoSaved = false;
    }
    public $autoSaved;
}

?>
