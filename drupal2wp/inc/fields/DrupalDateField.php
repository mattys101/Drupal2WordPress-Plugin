<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DrupalDateField
 *
 * @author Matt Selway
 */
class DrupalDateField extends DrupalBaseField{
    var $is_duration = FALSE;

    public function generateCurrentFieldMap(){
        $this->current_field_map=array(
            $this->field_info["field_name"] => $this->field_info["field_name"]
        );
        return $this->current_field_map;
    }
    
    public function processMeta($post_data,$field_data,$repeaterPos=FALSE){
        $this->is_duration = array_key_exists($this->field_info["field_name"] . "_value2", $field_data);
        return parent::processMeta($post_data, $field_data, $repeaterPos);
    }
    
    public function appendMeta($wp_field_name,$drupal_field_name){
        $name=$this->field_info["field_name"];

        if ($this->is_duration) {
            $values = $this->getFieldValue($drupal_field_name);
            $this->post_data["postmeta"][$this->getFieldName($name, $wp_field_name) . "_start"] = $values["start"];
            $this->post_data["postmeta"][$this->getFieldName($name, $wp_field_name) . "_end"] = $values["end"];
        }
        else {
            $this->post_data["postmeta"][$this->getFieldName($name, $wp_field_name)] = $this->getFieldValue($drupal_field_name);
        }
    }
    
    public function getFieldValue($col){
        $name = $this->field_info["field_name"];
        if($col == $name) {
            if($this->is_duration) {
                return array(
                    "start" => parent::getFieldValue($name."_value"),
                    "end" => parent::getFieldValue($name."_value2")
                );
            }
            else {
                return parent::getFieldValue($name."_value");
            }
        }
        return parent::getFieldValue($col);
    }

}
