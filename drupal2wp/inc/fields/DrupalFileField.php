<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DrupalFileField
 *
 * @author boris
 */
class DrupalFileField extends DrupalBaseField{
    public static $media_post_data=array();
    public function generateCurrentFieldMap(){
        $this->current_field_map=array(
            $this->field_info["field_name"]=>$this->field_info["field_name"]
        );
        return $this->current_field_map;        
    }
    
    public function appendMeta($wp_field_name,$drupal_field_name){
        $name=$this->field_info["field_name"];
        $post_id=$this->post_data["post_id"];
        if(!isset(self::$media_post_data[$post_id])){
            self::$media_post_data[$post_id]=array();
        }
        $file_id=$this->getFieldValue($name."_fid");
        //Not add to current post meta. Add to defered meta
        self::$media_post_data[$post_id][$this->getFieldValue($name."_fid")]=array("file_id"=>$file_id,"post_field"=>$wp_field_name,"post_id"=>$post_id, "alt"=>$this->getFieldValue($name."_alt"),"title"=>$this->getFieldValue($name."_title"));
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
