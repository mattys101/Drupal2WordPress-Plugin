<?php
/**
 * Import coords field to ACF Google Map field
 *
 * @author codeko
 */
class ACFGoogleMapField extends DrupalBaseField{
    
    public function generateCurrentFieldMap(){
        $this->current_field_map=array(
            $this->field_info["field_name"]=>$this->field_info["field_name"]
        );
        return $this->current_field_map;
    }
    
    public function getFieldValue($col){
        $name=$this->field_info["field_name"];
        if($col==$name){
            return serialize(array(
                "lat"=>parent::getFieldValue($name."_lat"),
                "lng"=>parent::getFieldValue($name."_lng")
            ));
        }
        return parent::getFieldValue($col);
    }
}
